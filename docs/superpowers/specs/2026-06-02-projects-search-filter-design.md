# Projects Search & Filter — Design Spec

**Date:** 2026-06-02  
**Status:** Approved

## Overview

Add a search toolbar to the `ViewProjects` page that filters the project list by name. The toolbar is designed to accommodate future filter chips (reviewer, status, team, date range, tags) without structural changes. The existing tab partitioning (My Projects, Active, Reviewed, Completed) is retained for now, with the expectation that tabs may be removed later once filtering matures.

## Scope

**Ships now:**
- Project name search, applied within the active tab
- Toolbar layout designed to accommodate future filter chips

**Explicitly deferred:**
- URL-reflected search state
- Filters by reviewer, status, team, date range, or tags
- Removing tabs

## Architecture

### Component: `ViewProjects`

Add a single `$search` string property (default empty string). No `#[Url]` — ephemeral, resets on page reload.

Each of the four `#[Computed]` query methods (`activeProjects`, `myProjects`, `reviewedProjects`, `completedProjects`) gets a conditional `where` clause applied before pagination:

```php
->when($this->search, fn ($q) => $q->where('projects.name', 'like', "%{$this->search}%"))
```

Because `$search` is a public property, changing it invalidates the computed cache automatically — tab counts update reactively.

**No reset on tab switch.** The search term persists when the user switches tabs, so they can compare results across tabs without re-typing.

### UI: Toolbar Row

Inserted between `<h1>Projects</h1>` and `<flux:tab.group>` in `view-projects.blade.php`.

- A `<flux:input>` on the left (~1/3 page width), with:
  - `wire:model.live.debounce.300ms="search"`
  - `placeholder="Search projects…"`
  - A search icon
  - `clearable` attribute (renders a built-in × button when input is non-empty)
- Remaining row width is empty space, reserved for future filter chips

The "Create New Project" button is unaffected — it remains floated right above the heading.

## Testing

Feature test: `ViewProjectsSearchTest`

1. **Happy path** — user has projects; setting `$search` to a matching term returns only matching projects in the computed property.
2. **No match** — setting `$search` to a non-matching term returns an empty result set.

Tests assert against the computed query results directly, not UI interaction.
