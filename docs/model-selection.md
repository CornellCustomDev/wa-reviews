# Model selection

Why each chat profile in `config/cornell_ai.php` uses the model it does, as of
November 2026. The comparisons were made with `ai:eval`
(see `docs/model-evaluation-harness.md`).

## Current choices

| Profile | Model | Used by | Replaced |
|---|---|---|---|
| `chat` | `openai.gpt-5.4-mini` | The guideline recommender, the chat agents and the issue and scope analyzers | `openai.gpt-4.1-mini` |
| `default` | `openai.gpt-5.4-mini` | Nothing in the app reads it; kept in step with `chat` | `openai.gpt-4.1-mini` |
| `task` | `gemini-3.5-flash-lite` | `StructuredOutputAgent` and chat-title generation | `openai.gpt-4o-mini` |
| `reasoning` | `gpt-5.6-terra` | Nothing in the app reads it; not evaluated | `openai.o4-mini` |

Each is overridable with `AI_MODEL_CHAT`, `AI_MODEL_DEFAULT`, `AI_MODEL_TASK`
and `AI_MODEL_REASONING`.

## What we were choosing for

The chatbot needs fast models with reasonable results, not slow models with
excellent results. Cost per call is a few cents and was the lowest priority.
The previous models are also being retired: OpenAI shuts down `gpt-4.1-mini`
and `gpt-4o-mini` on January 14, 2027.

## The `chat` profile

Compared on the guideline recommender: 20 past cases a reviewer had triaged
(dataset `2026-10-16-reviewed`), each asked twice, on November 20, 2026. No
model had a failed call.

| Model | Mean latency | F1 (strict) | Recall (strict) | Guidelines suggested per call | Input cost per call |
|---|---|---|---|---|---|
| `openai.gpt-4.1-mini` (previous) | 19.7 s | 0.75 | 0.80 | 1.9 | $0.051 |
| **`openai.gpt-5.4-mini`** | **11.1 s** | 0.73 | 0.76 | 2.25 | $0.072 |
| `gemini-3.5-flash-lite` | 12.9 s | 0.74 | 0.74 | 1.45 | $0.056 |
| `gpt-6-luna`, low reasoning effort | 20.2 s | 0.60 | 0.60 | 1.1 | $0.014 |

F1 and recall are measured against the guidelines reviewers accepted or
rejected. With 20 cases the 95% confidence interval on an F1 difference is
about ±0.2, so the first three models are tied on that measure. The latency
differences are well outside noise.

**Why `gpt-5.4-mini` over `gemini-3.5-flash-lite`.** The two tie on the
guidelines reviewers had ruled on, but `gpt-5.4-mini` suggests more guidelines
that no reviewer had ever seen, which the eval cannot score. Twenty of those
suggestions, from seven cases, were judged by hand:

| | `gpt-5.4-mini` | `gemini-3.5-flash-lite` |
|---|---|---|
| Suggestions | 19 | 10 |
| Valid | 12 | 8 |
| Wrong | 6 | 1 |
| Unsure | 1 | 1 |

`gpt-5.4-mini` found four valid guidelines that Gemini missed, and made five
wrong suggestions that Gemini did not. Gemini found nothing valid that
`gpt-5.4-mini` missed. A reviewer can reject a wrong suggestion in seconds,
while a guideline that is never suggested is likely to be missed, so the more
thorough model was chosen. The sample was small and leaned towards cases where
`gpt-5.4-mini` suggested more. If the extra wrong suggestions prove tiresome,
`gemini-3.5-flash-lite` is the fallback.

**Why not `gpt-6-luna`.** It is OpenAI's named replacement for `gpt-4.1-mini`
and by far the cheapest, but on this task it was no faster than `gpt-4.1-mini`
and suggested about 40% fewer guidelines, which showed up as lower recall.
Turning reasoning off (`--effort=none`) did not help: it made more tool-call
rounds instead, and that run also stalled mid-answer.

**Not evaluated.** `gpt-6.1-sol` hit a rate limit on the Cornell gateway that
did not clear, so no comparison was possible. `gpt-6.1-terra` was not on the
gateway. `gemini-3.8-flash` was tried in October 2026 on an earlier dataset,
where it was slow at its default reasoning effort and lost calls to timeouts;
it was not re-run.

## The `task` profile

`StructuredOutputAgent` reshapes the recommender's answer into a fixed schema,
so it is graded on fidelity: a valid schema and text carried through unchanged.
On 20 cases asked twice in October 2026, `gemini-3.5-flash-lite` matched
`gpt-4o-mini` on every fidelity measure, had no invalid answers where
`gpt-4o-mini` had two, and took 2.1 s per call against 9.2 s.
`openai.gpt-5.4-nano` and `gpt-6-luna` matched `gpt-4o-mini` on fidelity but
were slower than Gemini (7.1 s and 5.8 s).

Every recommender run above used `gemini-3.5-flash-lite` for this step, and
none produced an invalid schema. Chat-title generation also reads this profile
and was not evaluated.

## When to revisit

- `gpt-5.4-mini` is not deprecated, but OpenAI has started retiring the GPT-5
  family (`gpt-5.4-nano` shuts down April 1, 2027). Check
  [OpenAI's deprecations page](https://developers.openai.com/api/docs/deprecations)
  before relying on it past early 2027.
- When `gpt-6.1-terra` or a `gpt-6.1-sol` quota becomes available on the
  gateway, run it against the current `chat` model.
- The `reasoning` profile names `gpt-5.6-terra` only because it is the
  replacement OpenAI lists for `o4-mini`, which shut down on October 23, 2026.
  The Cornell gateway serves it, but nothing reads the profile and the model
  was not evaluated, so evaluate it before anything uses it.
- The dataset is small. A larger set of reviewed cases would let F1 separate
  models it currently cannot.

To test a candidate:

```bash
lando php artisan ai:eval --agent=GuidelineRecommender --dataset-version=2026-10-16-reviewed --model=<model>
```
