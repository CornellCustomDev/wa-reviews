# AI Model Evaluation Harness

## Context

We are swapping the models behind the app's AI features: the Chat profile moves to `openai.gpt-5.4-mini` and the Task profile to `gemini-3.5-flash-lite` (`config/cornell_ai.php`). Today there is no way to answer "did that make the product better or worse?" — only anecdote.

A year of real usage is sitting in `chat_histories` (706 `GuidelineRecommender` runs, 597 `StructuredOutput` runs, each with the full transcript, token usage, and finish reason), and reviewers have been *labelling* AI output all along via `issues.ai_status` (238 accepted / 99 modified / 74 rejected / 98 untriaged across 329 recommender runs, joined precisely through `issues.chat_history_ulid`).

The goal is a repeatable harness that (a) turns that history into a stable evaluation dataset, (b) replays it against any candidate model, (c) grades the results against human labels rather than against the old model's opinion, and (d) says with statistical confidence whether a model swap is a real improvement, a real regression, or noise.

### The key methodological point

**Do not compare a new run against the numbers recorded in `chat_histories`.** Those runs happened under older prompts, an older Prism version, and an older guidelines corpus, so any diff conflates model change with codebase drift. The history is how we *build the dataset and mine labels*. Numeric comparison is always **run vs. run**: execute the outgoing model (`openai.gpt-4.1-mini` / `gpt-4o-mini`) and the candidate through the same harness, same day, same DB, and compare those two runs pairwise.

### Scope (confirmed)

- Agents: `GuidelineRecommender` and `StructuredOutput` (the two Prism agents). `ModelChatAgent` is deferred.
- Results stored in DB tables.
- Labels mined from `issues.ai_status`, plus a small hand-curated gold subset.

### What exploration settled

- **No production code changes needed to swap models.** Both agents read the model from `config('cornell_ai.profiles')` *inside their constructors*, so the runner just calls `config()->set(...)` before constructing.
- **No cassette/mocking layer needed in v1.** Every tool (`FetchGuidelinesTool`, `FetchScopePageContentTool` → `Scope::getPageContent()` reads the stored `pages` row, `ScratchPadTool`) is a pure DB read. Given a frozen DB, tools are deterministic, so both models see identical inputs on the same day.
- **The streaming path is reachable from non-Livewire code.** `tests/Integration/Prism/AiToolsTest.php` already does `use PrismAction;` and calls `collectStream(...)` directly — trait-private is fine. The runner reuses that same trait so it exercises the real production code path (`asStream()` + `collectStream()`), not `asText()`.
- **`tests/Integration` is deliberately excluded from `phpunit.xml`** — it's the existing home for live-API tests, and `AiToolsTest::getProviders()` already proves the Cornell gateway routes `'cornell', 'gemini-3.5-flash-lite'`.
- **Baseline reliability to beat:** recommender 652 Stop / 45 Error (6.4%) / 7 Length; StructuredOutput 595 Stop / 2 Length.
- **Cost is not a constraint:** recommender averages ~100k prompt tokens per run (large stored pages), StructuredOutput ~1.4k. 60 cases × 2 repeats × 2 models ≈ 24M input tokens — single-digit dollars.

## Current shape (2026-10-13)

The design below was built as five Artisan commands and then collapsed once it
had answered its first real questions. What exists now:

- **One command, `ai:eval`** (`app/Console/Commands/Evals/EvalModel.php`),
  which extracts a dataset if the agent has none, replays the production model
  as the baseline if the dataset has none, replays the candidate, and writes the
  report. `ai:eval-report` remains for re-reading two past runs, with
  `--regrade` to re-score them first. The separate extract, run and grade
  commands are gone; their logic lives in `CaseExtractor::extractDataset()` and
  `EvalRunner::replay()`, and both graders run inline as each result is stored.
- **The report tabulates only the metrics in `MetricCatalog`** (reliability,
  fidelity, agreement, cost), each with a paired bootstrap interval, repeat sd,
  margin and verdict. McNemar and effect size were dropped: at twenty cases
  the interval already answers "is this noise", and every real finding so far
  was legible from the means and the worst-case table.
- **The LLM judge is parked**, together with the plain-language wizard, in the
  git stash `parked: eval wizard … + judge grader`. 100 judgements were
  recorded in August and none changed a verdict. The `eval_judgements` table
  still exists in the restored local database; its migration is parked with
  the code.
- **A candidate run changes one chat profile.** StructuredOutput replaces the
  Task model, the recommender replaces the Chat model, and `--profile` overrides
  that. Changing both at once made a recommender failure unattributable.

Sections from here on describe the original design and the data findings that
shaped it. Command names in them are historical.

## Design

Four separable pieces, each its own Artisan command, so a failed judge run doesn't cost a re-run of the models.

### 1. Dataset extraction — `ai:eval-extract`

Mines `chat_histories` into `eval_cases`. Per case: source ULID, agent, context ref, the input prompt, the recorded baseline output/usage/finish reason (for reference and drift checks only), stratification tags, and mined labels.

Stratified sampling, not random — strata: agent, context type (`Scope` vs `Issue`), outcome shape (guidelines returned vs. none), tool-use pattern, stored-page size bucket, baseline finish reason (deliberately include the `Error`/`Length` cases), and label availability.

Label mining for `GuidelineRecommender`, per source run:
- **Positives / true positives:** issues with that `chat_history_ulid` and `ai_status` in `accepted`/`modified`.
- **False positives:** same join, `ai_status = rejected`.
- **Misses (for recall):** issues on the same `scope_id` with `ai_status IS NULL` (human-authored) created after the run.
- `generated` (untriaged) rows are recorded as *unlabelled* and excluded from precision/recall denominators — not silently counted as correct.

For `StructuredOutput` the case is fully self-contained (the `<data>` prompt plus schema), and the grading target is faithful transformation, so no external label is needed.

A `--gold` flag marks a curated subset; gold labels live in `eval_cases.labels` under a separate key so mined and human labels never overwrite each other. Curation itself is a manual pass over ~30–50 cases (recommend a tiny Artisan review loop, or direct SQL — not worth a UI in v1).

### 2. Runner — `ai:eval-run`

```
ai:eval-run --agent=GuidelineRecommender --chat-model=... --task-model=... --repeats=2 --limit=60 [--label="candidate-A"]
```

Per case:
1. Override the profile models via `config()->set('cornell_ai.profiles.{profile}.model', ...)`.
2. **Authenticate a user.** `PrismHistory::storeChatHistory()` writes `'user_id' => auth()->id()` into a non-nullable FK column — a console run with no auth will fail the insert. Log in the source row's original user for fidelity (tools and policies then behave as they did in production).
3. Open a DB transaction, run the agent, **roll back**. This neutralises every side effect: the `chat_histories` row the agent writes, the issues `GuidelineRecommenderCallback` creates, and any `StoreIssuesTool`/`UpdateIssueTool` mutation. Results are buffered in memory and persisted to `eval_results` *after* the rollback.
4. Capture: final text, parsed output, per-step tool calls, usage, finish reason, wall-clock latency (absent from history — new signal), and any thrown exception as a first-class result rather than a crash.
5. `--repeats` ≥ 2 because temperature is 0.2, not 0. Without within-model variance you cannot tell a real between-model gap from sampling noise.

`GuidelineRecommender` is constructed exactly as `ScopeAnalyzer::getAgent()` does (`GuidelineRecommenderAgent::for($scope)->withPrompt(...)->withResponseHandler(...)`), but with a capture-only handler in place of `storeGeneratedIssues`. Note that a recommender run internally triggers a nested `StructuredOutputAgent` call via `PrismSchema::getStructuredResponse()` — so the Task-profile model affects recommender results too, and the runner must record both models on the run.

### 3. Graders — `ai:eval-grade` (three tiers, cheap first)

**Structural** (free, deterministic): schema validity; `finish_reason === Stop`; guideline IDs exist (reuse `GuidelinesAnalyzerService::isValidGuidelineId()`); required fields present and enums mappable (`mapResponseToItemArray()` will throw on bad `assessment`/`impact` — that's a hard failure worth counting); step count vs. `withMaxSteps(10)`; empty/refusal detection; tokens, cost, latency.

**Agreement:**
- `GuidelineRecommender` — precision / recall / F1 of the returned guideline-ID set against mined+gold labels. Report Jaccard vs. the historical set separately, labelled as *drift*, not quality.
- `StructuredOutput` — normalised field-level exact match against the input `<data>`. This is a near-deterministic transformation; any semantic drift is a hard fail, and it is the metric most at risk from the Gemini swap (see Risks).

**LLM judge** — blinded pairwise A/B on free text only (`feedback`, and per-guideline `reasoning`/`recommendation`). Rubric: accessibility correctness, guideline-citation accuracy, actionability. Position-swapped to cancel position bias; judge model must be neither candidate (use a Reasoning-profile model). Stored in `eval_judgements`.

### 4. Qualification & report — `ai:eval-report --run=X --baseline=Y`

Every metric is paired on the same case, so:
- **Rate metrics** (schema-valid, `Stop`, judge win, error rate) → **McNemar** on discordant pairs.
- **Continuous metrics** (F1, tokens, latency) → **paired bootstrap CI** on the mean difference, plus effect size. Average over repeats within a case before pairing, and report within-model variance alongside.
- **Verdict against pre-registered non-inferiority margins**, stored on the run so they can't be moved after seeing results. Suggested starting margins: no regression in schema validity or error rate; guideline-recall CI lower bound within 2pp of baseline; cost and p95 latency not worse.

Output: a markdown report — aggregate table, per-metric CIs, pass/fail verdict, and the worst N per-case diffs so a regression can be eyeballed.

## Schema

Four tables (six migrations), following existing conventions (FKs to `agents`, ULID reference to `chat_histories`):

- **`eval_cases`** — `agent_id`, `source_chat_history_ulid`, `context_type`, `context_id`, `user_id`, `input` (json), `baseline` (json: text, parsed, usage, finish_reason), `labels` (json: `{mined: …, gold: …}`), `tags` (json strata), `is_gold`, `dataset_version`.
- **`eval_runs`** — `label`, `repeats`, `models` (json profile snapshot), `margins` (json, pre-registered), `git_sha`, `dataset_version`, `is_baseline`, `status`, `started_at`, `finished_at`, `notes`.
- **`eval_results`** — `eval_run_id`, `eval_case_id`, `repeat_index`, `output` (json), `usage` (json), `finish_reason`, `latency_ms`, `tool_calls` (json), `error`, `scores` (json metric ⇒ value).
- **`eval_judgements`** — `eval_case_id`, `result_a_id`, `result_b_id`, `winner`, `rubric` (json), `judge_model`, `position_swapped`, `error`.

## Files

**New** (all under existing directories; no new base folders):

- `app/Console/Commands/Evals/EvalModel.php` (`ai:eval`), `EvalReport.php`
- `app/Services/Evals/CaseExtractor.php` — transcript parsing + label mining
- `app/Services/Evals/EvalRunner.php` — `use PrismAction;` (per `AiToolsTest` precedent), transaction isolation, model override, per-agent construction, inline grading
- `app/Services/Evals/Graders/{StructuralGrader,AgreementGrader}.php`
- `app/Services/Evals/Statistics.php` — paired bootstrap
- `app/Services/Evals/ReportBuilder.php`, `MetricCatalog.php`
- `app/Models/{EvalCase,EvalRun,EvalResult}.php` + migrations + factories
- `tests/Feature/Evals/*` — unit/feature coverage with `Prism::fake()` (pattern from `tests/Integration/Prism/StructuredOutputAgentTest.php`), asserting: extraction produces correct strata and labels, the runner leaves no DB residue, graders score known fixtures correctly, statistics match hand-computed values on a fixed dataset.

**Reused, not reimplemented:** `GuidelinesAnalyzerService::{getScopeContext,getRecommendedGuidelinesSchema,isValidGuidelineId,mapResponseToItemArray}`, `PrismSchema::convertToPrismSchema()`, `PrismAction::collectStream()`, `GuidelineRecommenderCallback`.

**Modified:** none in production paths. If `withTools()` turns out not to replace (rather than merge) the agent's internally-constructed tools, that only matters for a future cassette mode — not v1.

## Phasing

1. Migrations + models + `ai:eval-extract` with label mining. Inspect the dataset by hand before spending on runs.
2. `ai:eval-run` + structural grading. Validate on 5 cases with the *current* model — confirm zero DB residue and sane latency/usage capture.
3. Baseline run of the outgoing models, then candidate runs. Agreement grading.
4. `ai:eval-report` with McNemar + bootstrap and pre-registered margins.
5. LLM judge last — it only refines a picture the cheap tiers have already drawn.

## Risks

- **`StructuredOutputAgent` sets `withProviderOptions(['schema' => ['strict' => true]])`.** `CornellApi` extends Prism's OpenAI provider, so that flag is OpenAI-shaped; strict structured-output semantics are unlikely to be honoured for a Gemini-routed model even though the gateway accepts the model ID. Expect schema-validity and field-match to be the metrics that move, and expect a provider-option branch may be needed. Phase 2 on `StructuredOutput` alone is the cheap early read on this.
- **Label noise:** `modified` conflates "AI was right, reviewer polished the wording" with "AI was half wrong." The gold subset exists to calibrate this; report `accepted`-only and `accepted+modified` variants side by side rather than picking one.
- **Deleted issues are invisible.** A rejected recommendation that was deleted outright leaves no row, biasing precision upward. The `activities` audit table can recover deletions — worth adding in a later pass, and worth noting as a known bias in the v1 report.
- **Dataset must be frozen against DB drift.** Guidelines and stored pages change over time. Record `dataset_version` and re-extract deliberately, never implicitly; only compare runs sharing a `dataset_version`.

## Verification

- `lando php artisan test --compact tests/Feature/Evals` — extraction, isolation, grading, statistics.
- **Isolation proof (the one that matters):** snapshot `SELECT COUNT(*) FROM chat_histories; SELECT COUNT(*) FROM issues;` via `lando mysql` before and after `ai:eval-run --limit=3`; counts must be identical.
- `lando php artisan ai:eval-extract --agent=StructuredOutput --limit=20` then inspect `eval_cases` with `lando mysql` — confirm strata spread and that labels are populated where expected.
- End-to-end on the cheap agent first: extract → run baseline (`openai.gpt-4o-mini`) → run candidate (`gemini-3.5-flash-lite`) → `ai:eval-report`, and confirm the verdict, CIs, and per-case diffs render.
- `lando exec appserver -- /app/vendor/bin/pint --dirty --format agent` before finalising.

## Phase 1 outcome (2026-07-31)

Extraction is built and tested (`tests/Feature/Evals`). Four things the
data turned up that the plan did not anticipate:

### The recommender arm is blocked locally by missing page content

`pages.page_content` holds a *path*; the HTML lives on the filesystem under
`storage/app/page_content/{scope_id}/{date}.html`. Local storage holds 182 files
against 1254 `pages` rows, so of 706 recommender transcripts only **2** have
retrievable page content. `FetchScopePageContentTool` is the recommender's entire
view of the page, so a case without it would grade both models on an empty
string — `CaseExtractor` skips those rather than scoring noise
(`699 × stored page content missing`, `5 × scope deleted`).

Resolving this needs a decision: sync production `storage/app/page_content`
locally, run the harness where production storage is reachable, or re-fetch the
pages now (which drifts the content away from what the mined `ai_status` labels
were given for, weakening the truth signal). Until then the recommender arm
cannot be evaluated, and with it every mined label — labels attach to recommender
cases only.

### StructuredOutput cases are derived from recommender transcripts

Only 2 of 597 `StructuredOutput` transcripts still record their prompt; older
Prism versions stored the answer alone (`642 × no input text recorded`). But
production chains `GuidelineRecommenderAgent::handleResponse()` →
`PrismSchema::getStructuredResponse()` → `StructuredOutputAgent`, so a
recommender's final text *is* a StructuredOutput input. `sourceAgents()` derives
cases that way, `baseline.derived_from` records the origin, and the input text
doubles as the reference — the transformation must carry it through intact.
Derived and directly-recorded cases from the same transcript coexist (the unique
index is `(dataset_version, agent_id, source_chat_history_ulid)`), and
`deduplicate()` drops byte-identical inputs so no case is double-weighted in a
paired comparison.

### The recommender packs multiple ids into one `number` field

26 of 706 runs emit values like `"33 and 34"`, `"45, 56"`, `"1, 2, 3, 4"`.
Production drops these outright — `isValidGuidelineId()` rejects a non-numeric
value — so the guidelines were silently lost. `RecommendationParser` now
recovers the digit runs into `guideline_ids` and reports the raw value in
`malformed_numbers`, so the loss is measurable instead of invisible.

### Dataset v1

32 cases: 30 `StructuredOutput` across 7 strata (including 6 `Length`-truncated
inputs and 9 genuine refusal/no-issue narratives, both worth keeping — the
transformation must return empty guidelines rather than hallucinate) and the 2
replayable recommender cases. 0 gold, 0 labelled.

Phase 2 proceeds on `StructuredOutput` alone, which needs no page content and is
where the plan already located the biggest risk: `withProviderOptions(['schema'
=> ['strict' => true]])` is OpenAI-shaped and unlikely to be honoured by a
Gemini-routed model.
