# Oversized page content

## The problem

Some scopes store enormous pages. Measured across the 40 `GuidelineRecommender`
cases in evaluation dataset v2, using the page content currently on disk:

| stored content | cases |
|---|---|
| 6,736,266 chars (~1.68M tokens) | 691, 710 |
| 2,440,605 chars | 674 |
| 689,247 chars | 685, 704 |
| 507,335 chars | 673 |

Six of forty cases estimate above a 128k-token context window and three above
400k tokens — roughly **15% of the recommender arm**. This markup runs about
**2.8 characters per token**, not the usual 4.

Until recently these failed opaquely. The gateway is LiteLLM-fronted, so its
mid-stream failure arrives as a bare `{"error": {...}}` SSE line rather than
OpenAI's `{"type": "error", ...}`, and Prism's OpenAI handler read `$data['type']`
unguarded — so the user saw `Undefined array key "type"` instead of the real
message. `App\Ai\Prism\Providers\CornellStream` now reshapes that line so Prism
raises the actual error:

```
litellm.APIError: Your input exceeds the context window of this model.
Please adjust your input and try again.
```

That is honest, but it still costs the user a long wait for a failure that was
knowable before the request was sent.

## What ships now: a pre-flight limit

`config('cornell_ai.page_content_char_limit')` (`AI_PAGE_CONTENT_CHAR_LIMIT`,
default **250,000**) is a deliberately conservative ceiling. At ~2.8 chars per
token that is roughly 90k tokens, leaving room inside a 128k window for the
system prompt, tool results, and the response. The empirical bounds from the
eval runs bracket it: case 675 passed at 168k chars, case 673 failed at 507k.

The limit is measured in **characters** (in practice bytes, read with
`Storage::size()` so a multi-megabyte page is never loaded just to measure it).
No tokenizer is involved, and no model call is made to find out.

- `Scope::pageContentExceedsAnalysisLimit()` — the predicate.
- `Scope::pageContentAnalysisRefusal()` — the user-facing message, or `null`.
  It logs a `warning` on refusal; that count is the evidence that would justify
  the work below.
Guarded entry points, each checking the predicate and showing the refusal
through the existing `$feedback` / `$showFeedback` surface:

- `Scopes\ScopeAnalyzer::recommendGuidelines()`
- `Issues\IssueAnalyzer::recommendGuidelines()`
- `Issues\IssueFormAnalyzer::recommendGuidelines()`
- `Ai\ScopeChat::sendChatMessage()` (inlines the whole page into its prompt)

Deliberately **not** guarded:

- The tool path (`FetchScopePageContentTool` and friends). A check there would
  fire mid-stream, which is exactly what we set out to avoid; `CornellStream`
  already makes that path fail truthfully.
- `Ai\ScopeHelp::reviewElements()`, which also inlines page content but has no
  feedback surface to report a refusal through. Known remaining exposure.

There is no database flag for oversize. It is derivable from the stored content,
and a stored flag would go stale the moment a page is re-retrieved.

## Deferred: content minimizing

The point of the guard is to make the failure legible and countable. If the
warning count says real reviewers hit this often, these are the options, cheapest
first:

1. **Strip boilerplate.** Remove `<script>`, `<style>`, `<svg>` bodies, comments,
   inline `data-*` noise, and tracking markup. On the pages measured above, the
   6.7MB cases are dominated by generated markup, not content — this alone may
   bring most cases under the limit. It is also the only option that does not
   change what the model is asked to reason about, so it should be tried and
   re-measured before anything else is built.
2. **Deduplicate repeated markup.** Collapse long runs of structurally identical
   siblings (table rows, card grids, calendar cells) to a representative sample
   plus a count. Accessibility findings are per-pattern, not per-instance, so
   this preserves the signal for most guidelines.
3. **Region-wise map-reduce.** Split the DOM at landmark boundaries
   (`header`/`nav`/`main`/`footer`, or top-level sections of `main`), run the
   recommender per region, then merge and deduplicate the guideline sets. This
   is the general answer, and the most expensive: it multiplies calls, needs a
   merge policy for conflicting assessments, and changes what a "run" means for
   the evaluation harness.
4. **Summarized DOM outline.** Send a structural digest (landmarks, heading
   tree, form controls, images with/without alt, link text) instead of markup.
   Cheap and bounded, but it discards exactly the detail some criteria need, so
   it is better as a fallback for pages too large even for (3) than as a default.

Also worth building if this becomes routine: an on-demand `ai:oversized-pages`
report listing scopes whose stored content exceeds the limit, so the size problem
can be seen across the corpus rather than one refusal at a time.

## Note for the evaluation harness

The limit changes which cases are runnable, so it is part of the run
configuration. A dataset extracted before the guard contains cases the guard now
refuses; when comparing runs, compare only runs sharing both a
`dataset_version` and the same `page_content_char_limit`.
