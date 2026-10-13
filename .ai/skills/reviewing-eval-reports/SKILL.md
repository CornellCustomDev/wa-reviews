---
name: reviewing-eval-reports
description: Guide for interpreting `ai:eval-report` model-comparison reports (storage/eval-report-*.md) and the underlying EvalCase/EvalRun/EvalResult data. Activate when asked to explain, judge, or investigate an eval report, a specific eval case's score, or whether a metric change is a real regression.
---

# Reviewing an eval report

## Report header first

```
- Candidate: run 1670 — candidate-gemini-3.5-flash-lite
- Baseline: run 1668 — baseline-openai.gpt-4o-mini
```

"Case X falls short in run N" is meaningless until you know whether N is the
baseline or the candidate. A regression is always candidate-vs-baseline on
the *same case*, never a score standing alone. Confirm both run ids/models
before pulling case data:

```
lando artisan tinker --execute 'echo json_encode(App\Models\EvalRun::find($id)?->toArray());'
```

## Find the actual regressing case before explaining anything

An aggregate metric diff (`schema_valid: 0.6 → 0.55`) does not identify which
case caused it, and the report's "lost most ground" table (ranked by
`field_match_rate`) may not contain it. Diff every shared case on the specific
metric in question:

```php
$baseline = App\Models\EvalResult::where('eval_run_id', $baselineRunId)
    ->get(['eval_case_id', 'repeat_index', 'scores'])
    ->keyBy(fn ($r) => $r->eval_case_id.':'.$r->repeat_index);

foreach (App\Models\EvalResult::where('eval_run_id', $candidateRunId)->get() as $c) {
    $b = $baseline[$c->eval_case_id.':'.$c->repeat_index] ?? null;
    if ($b && ($b->scores['schema_valid'] ?? null) !== ($c->scores['schema_valid'] ?? null)) {
        // this case is the regression — not whichever one looked worst elsewhere
    }
}
```

A case failing identically in both runs (same `schema_issues` string) is a
pre-existing dataset/prompt problem the candidate inherited — not a
regression. Only baseline-pass/candidate-fail (or vice versa) counts.

## Know the grader's reference before judging a mismatch

Read `app/Services/Evals/Graders/AgreementGrader.php` — the two agents are
graded against different things:

- `GuidelineRecommender` → human labels from `issues.ai_status`
  (`precision_strict`/`recall_strict`/`f1_strict`).
- `StructuredOutput` → **the case's own input text**
  (`case->input['data']`, parsed by `RecommendationParser`). Its job is a
  faithful reshape, per `StructuredOutputAgent::instructions()`: "stay as
  true to the original data as possible." So `field_match_rate` /
  `field_mismatches` measure transcription fidelity, not accessibility
  quality — a mismatch on `<id>.reasoning`/`<id>.observation`/etc. usually
  means the model paraphrased text it should have carried through unchanged.
  Confirm by diffing `case->input['data']` against
  `result->output['structured']` directly.

`schema_valid` failing on `"guidelines is not a list"` can mean the model
correctly found nothing to report but encoded it as `null` instead of `[]`
(legitimate empty answer, wrong shape) — check `case->input['data']` for a
refusal/clarifying-question before assuming bad reasoning. Also check what
the *baseline* did with the same case: fabricating a plausible-looking
guideline from nothing can pass `schema_valid` while being a worse failure
than `null`. Passing a structural check is not evidence of a good answer;
failing one is not evidence of a bad one.

## Reference

- `app/Services/Evals/MetricCatalog.php` — which scores the report tabulates
  and which way each points (higher/lower-better). Don't re-derive polarity
  from the report table. Every other score a grader recorded (counts, lists,
  `baseline_jaccard` drift, `schema_issues`) is still on the `eval_results`
  row, just not in the table.
- `margin` on a metrics-table row is the pre-registered non-inferiority
  threshold (`eval_runs.margins`); `fail` means the CI crossed that margin,
  not necessarily a large practical difference.
- `repeat sd` is the same model's variance across repeats of the same case —
  a diff smaller than that is noise.
- `ai:eval-report --run=X --baseline=Y --regrade` re-scores both runs with
  the current graders before building the report; no model is called.

## Working method

1. Get baseline/candidate run ids and models from the report header.
2. For a named case, fetch `EvalCase` (reference/input) and `EvalResult` rows
   from both runs.
3. Identify the agent (`GuidelineRecommender` vs `StructuredOutput`) and grade
   against the right reference per above.
4. For an aggregate regression, diff every shared case on that metric before
   picking one to explain.
5. State plainly whether it's a real regression, a shared pre-existing issue,
   or a metric/schema check that doesn't capture what actually went wrong.
