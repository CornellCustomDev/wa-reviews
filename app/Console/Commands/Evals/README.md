# Model Evaluation Harness

## What this answers

The app asks an AI model to suggest accessibility guidelines for a page, and a
second model call reshapes that answer into a fixed structure. When we want to
switch either call to a newer or cheaper model, the only honest question is:
**does the new one give answers at least as good as the old one?** This is the
way to measure that instead of guessing from a handful of examples.

## Just want to test a model?

```bash
lando php artisan ai:eval --model=gemini-3.5-flash-lite
```

That is the whole workflow. The command:

1. Builds a question set from past AI runs if this feature has none yet.
2. Replays the current production model as the **baseline** if the question
   set has none yet.
3. Replays the candidate model.
4. Writes a comparison to `storage/eval-report-<model>-<date>-<run>.md` and
   prints it. The exit code is non-zero on a failing verdict.

Later candidates reuse the same question set and baseline, so each one is a
single command. Add `--dry-run` first to see what would run without calling a
model.

The useful options:

| Option | What it does |
|---|---|
| `--agent=` | `StructuredOutput` (default, cheap) or `GuidelineRecommender` (large pages, slow). |
| `--model=` | The candidate. Prompted for when omitted. |
| `--profile=` | Which chat profile the model replaces, `task` or `chat`. Defaults to the one the agent reads, so a run changes one thing. |
| `--baseline` | Replay the model as the new baseline instead. Do this when the production model or its prompt changes. Without `--model` it uses the configured production model. |
| `--repeats=2` | How many times each question is asked. Two or more separates a real difference from the model simply answering differently. |
| `--dataset-version=` | Which frozen question set to use. A new name extracts a new set. |
| `--margins=` | JSON of how much loss you will tolerate per metric. Defaults to the baseline's margins, then to the agent's standard ones. |

`ai:eval-report --run=<id> --baseline=<id>` re-reads any two past runs, and
`--regrade` re-scores them first after a grader change. Grading never calls a
model.

## How it can work at all

Two facts about our data:

**We have a year of real questions.** Every AI run is saved in
`chat_histories` with the prompt and the answer, so we can re-ask a model the
questions it was really asked.

**We already know some of the right answers.** When the AI suggested a
guideline, reviewers marked it **accepted**, **modified**, or **rejected**
(`issues.ai_status`). Those marks are human judgement about whether the AI was
right, recorded as a side effect of normal work.

Vocabulary you'll see:

- A **case** is one test question: one past run, its input, and any reviewer
  marks that go with it.
- A **run** is one pass of every case through one model. You need two runs to
  learn anything.
- A **dataset version** names a frozen set of cases. Guidelines and stored
  pages change over time, so two runs are only comparable when they share one.

## An important rule

**Don't compare a new run to the numbers saved in `chat_histories`.** Those
old runs happened under different prompts, a different AI library version, and
a different set of guidelines. The old history is only used to build the
questions and collect the reviewer marks. Comparison is always run against run,
on the same day, against the same database.

## What the two agents are graded on

The two agents do different jobs, so they are held to different references.
`app/Services/Evals/Graders/AgreementGrader.php` is where that happens.

**StructuredOutput** exists to reshape the recommender's text into the schema
without altering it, so the case's own input is the ground truth:

- `schema_valid`: the answer came back in the shape production needs.
- `field_match_rate`: share of fields carried through unchanged. Anything
  below 1.0 means the model rewrote content it was told to copy.
- `feedback_match`: the summary text was carried through verbatim.

**GuidelineRecommender** is graded against the reviewer marks:

- **Precision**: of the guidelines it suggested, what share did reviewers
  accept? **Recall**: of the guidelines reviewers endorsed, what share did it
  find? **F1** combines the two.
- Each comes in two flavours. `_strict` counts only `accepted` as a hit;
  `_lenient` also counts `modified`, because "modified" covers both "right,
  reworded" and "half wrong". A model that wins under both has a real result.
- Guidelines no reviewer ever ruled on are left out of the score. A new model
  is neither punished nor credited for surfacing something new.
- `finished_stop` and `completed` catch a model that errors or runs out of
  room on large pages.

Both agents also report latency and token counts.

## Reading the report

The report opens with a one-word verdict, then one row per metric: baseline
mean, candidate mean, the difference, and a 95% confidence interval for it.

- **If the interval includes zero, nothing has been measured.** The honest
  answer is "not enough cases", not "slightly better".
- `repeat sd` is how much the same model varied when asked the same question
  twice. A difference smaller than that is noise.
- The **margin** column is the loss you agreed to tolerate before the run.
  `fail` means the interval crossed it. Margins are recorded on the run before
  it executes so the goalposts cannot move afterwards.
- The last table lists the cases where the candidate lost most ground, so a
  regression can be read rather than inferred from an average.

Every other score a grader records stays on the `eval_results` rows for anyone
digging into a case; the report tabulates only the metrics listed in
`app/Services/Evals/MetricCatalog.php`.

## Two things to be aware of

**The recommender arm is starved on most machines.** The page HTML it analyses
lives on the filesystem under `storage/app/page_content`, and a local copy
usually holds a small fraction of it. Cases without page content are skipped at
extraction, so a recommender dataset built locally is small and most reviewer
marks go unused. Syncing that directory from production is what unlocks it.

**Reviewer marks are approximate.** They were made during real work, not as
test labels, and a bad suggestion deleted outright leaves no record. The
`eval_cases.labels` column has a `gold` key for hand-curated labels that
override the mined ones; nothing writes it yet.

Full design history and the data findings that shaped it:
`docs/model-evaluation-harness.md`.
