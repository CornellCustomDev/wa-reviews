# Building components in flux-pro

This file is loaded automatically when working in this repo. Read it before writing any new component or non-trivial change to an existing one.

Ultimately, you can pretty much make any component by copying bits and pieces from other components. Try not to invent too much new stuff, and instead build on the precedence established by other components. This includes their JS source, markup, Blade, docs, etc.

## Philosophy

These principles guide every decision. When in doubt, lean on these:

- **Internal consistency > external best practices.** If `flux:input` does something, match it. Don't make one component "better" if it creates inconsistency. Either change the system or leave it alone.
- **Punt semi-aggressively.** If a feature adds meaningful complexity without proportional value to most users, cut it. Ship the simpler version. Features can come in a future version.
- **If a feature changes the component's fundamental shape, it's a separate component.** Inline mode (no trigger, no popover) is a different component, not an `inline` prop.
- **The most useful variant is the default.** Don't make users opt-in to the better experience.
- **Push back on feature creep.** Say "I'd keep it simple and ship" and let me confirm. Don't just build whatever I casually mention.
- **Accept pragmatic imperfections.** Precision loss in color round-trips? Accept and move on. Invalid input? Silent revert, no shake animations. Don't over-engineer edge cases.
- **Research external precedent (Base UI, React Aria, Radix, ShadCN, Mantine, Tailwind, etc.), then decide.** Use their decisions as reference points, not templates. But always check flux's own patterns first.
- **Outside-in development.** Start from the docs page and user experience. Browse with Playwright. Then work inward to code review and architecture. Not the reverse.
- **Dead code is a liability.** Actively remove unused methods, lookup tables, parsers. If the browser can do it natively, delete the polyfill.

# Process for new components

1. **Research** external implementations (Base UI, React Aria, Radix, ShadCN, Mantine, Tailwind, etc.) for design reference.
2. **Compare architecture** to the closest sibling components. Make simplified flowcharts of existing components (slider, date-picker) and the new one. Identify divergences -- they're smells.
3. **Write tests first** that match sibling component test patterns. Get them green against the current implementation.
4. **Refactor** internals with the test safety net in place.
5. **Add new features** only after the architecture is clean.
6. **Audit outside-in** -- browse the docs page with Playwright, stress test interactions, inspect markup, then audit JS.
7. **Verify everything with Playwright** before claiming done.

## Architecture

- Use existing JS mixins: Controllable, Disableable, Submittable, Focusable, etc. But don't force a mixin that doesn't fit -- a bad abstraction is worse than self-contained logic.
- Copy JS patterns from similar components.
- Compose components. Example: Color picker uses a `ui-slider` element for its hue slider rather than implementing its own from scratch.
- **Sub-elements call methods on the state root.**
- **Use `detangle()`** to prevent sync loops between Controllable and internal state subscriptions.

## Naming conventions

- **Short, natural prop names.** `dropper` not `eye-dropper`. People will know what it means.
- **`-able` suffix for boolean opt-in behaviors.** `clearable`, `copyable`, `searchable`, `expandable`.
- **`type` prop for trigger variants.** `type="input"` vs `type="button"`, not boolean props like `:input="true"`.
- **Consistency across the library wins.**

## Styling conventions

- **Cursors**: `cursor-pointer` only belongs on interactive elements that perform some kind of navigation. Button elements and other clickable widgets should keep default cursor.
- **Focus rings**: prefer native browser outlines on natively-focusable elements (`<button>`, `<input>`). Add NO classes for focus on those. When a custom element with `tabindex="0"` (or a wrapper around an internal `<input type="range">`) needs visible focus, use the slider thumb pattern: `has-focus-visible:outline-2 has-focus-visible:outline-[-webkit-focus-ring-color]`. Never use `focus:outline-none focus:ring-2 focus:ring-(--color-accent)`.
- **Dialog/popover**: copy this verbatim from date-picker: `max-sm:max-h-full! rounded-xl shadow-xl sm:shadow-2xs max-sm:fixed! max-sm:inset-0! sm:backdrop:bg-transparent bg-white dark:bg-zinc-700 sm:border border-zinc-200 dark:border-white/10 p-3`
- **Trigger button** (default/sm/xs): copy from `date-picker/button.blade.php`. Trigger height progression: `h-10 / h-8 / h-6`. Text size: `text-base sm:text-sm / text-sm / text-xs`. Border radius: `rounded-lg / rounded-md / rounded-md`.
- **Input**: copy classes from `flux/stubs/resources/views/flux/input/index.blade.php` outline variant. Don't restyle: use `bg-white dark:bg-white/10`, `border-zinc-200 border-b-zinc-300/80 dark:border-white/10`, `shadow-xs`, `rounded-lg`. Reverting to native focus = no class.
- **Numeric display**: `tabular-nums` when value will change rapidly and cause tiny jarring width-shifts.
- **Disabled on custom elements**: CSS `:disabled` only works on native form elements. Custom elements need `[&[disabled]]:shadow-none!` attribute selectors.
- **Invalid styling**: Layer `data-invalid:` classes on top of base styles. Never ternary-swap the entire class block. Match `flux:input` pattern.
- **Whole trigger = one input**: Clicking whitespace in a trigger container should focus the text input. `cursor: text` across the container, except over action buttons.
- **Pixel-level parity**: Measure with Playwright, don't guess. If the exact Tailwind value doesn't exist, use magic numbers like `pe-[9px]`. But strive for round Tailwind values. Bespoke hyper-specific pixel values are often a smell in need of an upstream improvement.
- **Text sizes must match** across trigger variants and with sibling components.
- **Lock scroll when popover is open.** Copy date-picker pattern.

## JavaScript conventions

- **Use `setAttribute`/`removeAttribute` from utils.js**, not native DOM methods. The utility functions have MutationObserver implications.
- **Move interaction logic from Alpine to JS** when it needs disabled-state awareness or component coordination. Alpine should be RARELY used in Flux, only if reasonably necessary.
- **Fire both `input` and `change` events** on keyboard changes to match native `<input type="range">` spec behavior. `change` fires on mouse release (pointer commit), not on popover close.
- **MutationObserver should watch `value` attribute** in addition to config attributes.
- **Lean on the browser.** A `<canvas>` context can parse any CSS color -- don't ship lookup tables or parsers for things the browser handles natively.
- **2D area keyboard navigation**: Arrow keys move in the grid, Home/End = horizontal edges, PageUp/PageDown = vertical edges.

## Blade / PHP conventions

- **Aria-labels must use `__()`** for localization. Set them from Blade templates where `__()` runs. JS uses the Blade-set value as primary and a hardcoded English string only as a safety-net fallback. Client-side dnamic values may be able to use browser's built-in localization utilities for numbers and dates and such.
- **Use real `<flux:button>` for action buttons** (clear, copy, dropper) inside input triggers. Only use `<flux:button as="div">` when nested inside a `<button>` element (nested-button HTML constraint).
- **ARIA follows the closest pattern**: input trigger = `role="combobox"` on the `<input>` element; button trigger = native `<button>` with `aria-haspopup="dialog"`. Don't invent new patterns -- check ARIA APG and React Aria.

## Docs conventions

- **Labels only when contextually meaningful.** Use `label="hex"` for a format demo. Don't use `label="Clearable"` (echoes section title) or `label="Brand"` (random filler). When in doubt, no label.
- **Previews are dressed up, code blocks are minimal.** Previews show `value="#0ea5e9"` for visual context. Code blocks show `wire:model="color"` -- clean copy-paste targets. They don't need to match.
- **Remove all scaffolding/testing sections before shipping.** This is a launch blocker.
- **Reference section structure**: Use `<x-docs.variant level="3">` for sub-components. Match date-picker/select pattern. Container components listed before their children.
- **Reference prop order**: wire:model, value, name, format, type, placeholder, size -> behavioral props (swatches, dropper, clearable, copyable) -> label, description, disabled, invalid. Never alphabetical.
- **Document limitations honestly.** If a prop doesn't work with a variant (e.g. `copyable` with `type="button"`), say so explicitly.

## Verification

This is the single most important section. The #1 source of friction is claiming work is done without visually verifying.

- **Always verify with Playwright before claiming anything is done.** Take a screenshot. Measure pixels. Don't eyeball and don't guess.
- **After every CSS change**, take a Playwright screenshot to confirm the change is visible and correct.
- **After every docs change**, open the page in Playwright and verify rendering (especially inside `@verbatim` blocks where Blade components don't process).
- **After every behavior change**, interact with the component in Playwright and confirm the behavior works end-to-end.
- **Never guess at pixel values.** If you need to match spacing, use Playwright to measure the actual gap, then pick the right value.

## Testing

- Write tests that match sibling component test patterns (look at slider, date-picker tests).
- Get tests green before refactoring. Build the safety net first.
- Tests observe DOM state (thumb positions, input values, aria attributes) -- don't poke internals like `el.state.s`.
- Always run tests headless unless explicitly asked otherwise.
- Never commit dist files unless explicitly asked.
