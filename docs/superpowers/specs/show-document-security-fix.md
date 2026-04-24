# Re-architect `ShowDocument` to remove public edit surface

## Context

Nightwatch issue #27 surfaced an attack where a bot (`python-requests/2.32.4`)
POSTs to `/livewire/update` trying to mutate the `slug` property of
`App\Livewire\Documents\ShowDocument`. The previous `show-doc-leak` work added
`#[Locked]` on `slug`, which blocks the mutation — but the user's concern is
architectural: the bot only knows which component/properties to target because
we render `<livewire:documents.show-document>` directly inside the public
`welcome` (and `updates`) views, emitting a Livewire snapshot (class name +
property names + checksum) into anonymous HTML. That snapshot is the handle
that makes the probe possible.

The root cause is that `ShowDocument` is doing double duty: read-only rendering
for anonymous visitors AND authenticated-admin editing. Fixing that separation
removes the attack surface at the root.

Editors for these documents are site admins only (verified via
`DocumentPolicy::update` → `Permissions::ManageSiteConfig`).

## Approach

Split editing off into a new nested Livewire component. `ShowDocument` stays as
the public view (so `#[On('version-updated')]` refresh keeps working) but
becomes fully read-only — no `form`, no `save()`, no mutable public props, no
public action with side effects. The new `EditDocument` component is only
emitted into the DOM when the viewer can update the document, so anonymous
page loads ship no edit-related Livewire payload at all.

### Files to modify

- **`app/Livewire/Documents/ShowDocument.php`** — strip to read-only:
  - Remove `DocumentForm $form` property
  - Remove `save()` method
  - Remove `$this->dispatch('close-edit')` from `getDocument()` (that's the
    edit component's concern now)
  - Keep `#[Locked] public string $slug`, `public Document $document`,
    `mount()`, and `#[On('version-updated')] getDocument()`
  - Net result: the only remaining mutation vector is `#[Locked]` (blocked)
    and the only action is an idempotent DB re-read

- **`resources/views/livewire/documents/show-document.blade.php`** — remove
  `<x-forms.edit-model-wrapper>` and its `<x-slot:edit>`. Render title +
  content directly. Nest the edit component, gated on policy:
  ```blade
  <div>
      @if($document->title)
          <h1>{{ $document->title }}</h1>
      @endif
      <div>{!! $document->content !!}</div>

      @can('update', $document)
          <livewire:documents.edit-document :slug="$slug" />
      @endcan
  </div>
  ```

### Files to create

- **`app/Livewire/Documents/EditDocument.php`** — new component:
  - `#[Locked] public string $slug`
  - `public Document $document`
  - `public DocumentForm $form`
  - `mount()` — `$this->authorize('update', $this->document = Document::get($this->slug))`
    then `$this->form->setModel($this->document)` (defense in depth: the Blade
    `@can` guards initial render; `mount()` guards any forged update requests)
  - `save()` — same logic that's in `ShowDocument::save()` today, dispatches
    `version-updated` and `close-edit`
  - `#[On('version-updated')]` reload handler so its own form stays in sync if
    another source updates the doc

- **`resources/views/livewire/documents/edit-document.blade.php`** — houses the
  pencil/cancel buttons and the edit form (moved out of the shared
  `x-forms.edit-model-wrapper`). Structure:
  ```blade
  <div x-data="{ showEdit: false }" @close-edit.window="showEdit = false">
      <x-forms.button icon="pencil-square" class="float-right" x-show="!showEdit" x-on:click="showEdit = true" title="Edit" />
      <x-forms.button icon="x-mark" x-cloak class="float-right secondary" x-show="showEdit" x-on:click="showEdit = false" title="Cancel editing" />
      <div x-show="showEdit" x-cloak>
          <form wire:submit="save">
              <x-forms.input label="Title" wire:model="form.title" />
              <x-forms.textarea label="Content" wire:model="form.content" size="lg"/>
              <x-forms.button.submit-group>
                  <x-forms.button type="submit">Update Document</x-forms.button>
                  <x-forms.button x-on:click="showEdit = false" class="secondary">Cancel</x-forms.button>
              </x-forms.button.submit-group>
          </form>
      </div>
  </div>
  ```
  Note: no `x-forms.edit-model-wrapper` reuse, because the show/edit split now
  spans two components — the `<x-slot:view>` content lives in ShowDocument, the
  `<x-slot:edit>` content lives here.

### Tests

- **`tests/Feature/Livewire/ShowDocumentTest.php`** — keep
  `renders_successfully_for_guest` and `slug_is_locked_and_cannot_be_set_by_client`.
  Remove `unauthenticated_user_cannot_save` and `authenticated_admin_can_save`
  (save no longer exists on this component).

- **`tests/Feature/Livewire/EditDocumentTest.php`** — new file, mirroring the
  removed cases plus new authorization coverage:
  - `unauthenticated_user_cannot_mount` — expects forbidden on `Livewire::test(EditDocument::class, ['slug' => ...])`
  - `non_admin_authenticated_user_cannot_mount` — expects forbidden
  - `admin_can_mount_and_save` — happy path ending in a new `documents` row
  - `slug_is_locked_and_cannot_be_tampered`
  - `save_requires_authorization_on_subsequent_request` — mount as admin, then
    simulate session loss and confirm the persistent-middleware re-apply
    behavior (or at minimum the form's own `authorize` check) rejects `call('save')`

- **`tests/Feature/DocumentsRenderingTest.php`** — new thin HTTP-level test:
  - GET `/` as a guest, assert response contains `wire:snapshot` referencing
    `documents.show-document` but NOT `documents.edit-document` (proves no edit
    component ships to anonymous)
  - GET `/` as a site admin (route is public, so just `actingAs` an admin),
    assert both component snapshots are present

### Critical files to read before editing

- `app/Livewire/Documents/ShowDocument.php` — current structure we're splitting
- `app/Livewire/Forms/DocumentForm.php` — reused unchanged
- `resources/views/components/forms/edit-model-wrapper.blade.php` — reference
  only; the new edit component inlines the relevant bits
- `app/Policies/DocumentPolicy.php` — authoritative `update` check
- `tests/Feature/Livewire/ShowDocumentTest.php` — base for refactoring

### Residual attack surface after the change

- The public pages still emit a `ShowDocument` Livewire snapshot. Its exposed
  surface after the split:
  - `slug` (locked, mutation throws)
  - `document` (Eloquent model, hydrated from DB every request)
  - `getDocument()` public method — idempotent DB read, no writes, no
    authorization-sensitive side effects
  - Conclusion: probing this endpoint costs the attacker a DB read and
    produces no leverage.
- The `EditDocument` component is never included in anonymous HTML, so there
  is no snapshot, checksum, or payload for an attacker to craft a request
  against. A forged POST that invents a component would fail Livewire's
  snapshot-checksum verification.
- The speculative `boot()` auth guard added to `ShowDocument` in the current
  working tree should be removed as part of this work — the component is no
  longer sensitive, and the guard would break anonymous rendering.

## Verification

1. `lando php artisan test --compact tests/Feature/Livewire/ShowDocumentTest.php`
2. `lando php artisan test --compact tests/Feature/Livewire/EditDocumentTest.php`
3. `lando php artisan test --compact tests/Feature/DocumentsRenderingTest.php`
4. Manual: `curl -s https://wa-reviews.lndo.site/ | grep -o 'wire:snapshot[^"]*' | head` — confirm no `edit-document` reference appears; only `show-document` should.
5. Manual as admin: log in as a SiteAdmin, visit `/`, confirm the pencil button appears and editing still works end-to-end (save creates a new version, page refreshes content via `version-updated`).
6. Replay the original attack locally: POST to `/livewire/update` with a captured snapshot from an anonymous load of `/`, attempting to target `edit-document` — confirm the server rejects because the component was never rendered for that session.
