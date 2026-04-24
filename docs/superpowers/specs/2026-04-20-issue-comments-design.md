# Issue & Scope Comments

**Date:** 2026-04-20

## Overview

Allow any user with project access (reviewers, team admins, report viewers) to leave comments on individual Issues and Scopes/Pages. Comments are displayed in chronological order — flat, no threading. They give report viewers a direct channel to ask questions or flag concerns without requiring edit access to the review itself.

## Data Model

New `comments` table (polymorphic):

| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `user_id` | bigint FK | Author |
| `commentable_type` | varchar | e.g. `App\Models\Issue` |
| `commentable_id` | bigint | |
| `body` | text | Plain text |
| `created_at` | timestamp | |
| `updated_at` | timestamp | Used to enforce 10-min edit window |

**Model relationships:**
- `Comment` → `morphTo commentable`, `belongsTo user`
- `Issue`, `Scope`, ~~`Page`~~ → each get `morphMany comments`

## Authorization — `CommentPolicy`

| Action | Who |
|---|---|
| `create` | Any user who can `view` the project |
| `update` | Author only, and only if `now()->diffInMinutes($comment->created_at) <= 10` |
| `delete` | Author, or a site administrator (`$user->isAdministrator()`) |

## Components

### Shared `livewire:comments` component

`app/Livewire/Comments/Comments.php`

- Accepts a polymorphic `$commentable` (Issue or Scope)
- Renders comments in chronological order (flat list)
- `addComment(string $body)` — creates comment, checks `create` policy
- `updateComment(int $id, string $body)` — checks `update` policy
- `deleteComment(int $id)` — checks `delete` policy
- Exposes `canEdit(Comment $comment)` and `canDelete(Comment $comment)` booleans to the view for showing/hiding controls
- Edit form shown inline; replaces comment body on click if within 10-minute window

### `IssueSidebar` changes

- Rename heading "AI Assistance" → "Issue Actions"
- Add `showComments` bool property with `#[Url(as: 'comments', history: true)]`
- Add `clickComments()` method (toggles `showComments`, closes other panels)
- Add Comments button to view using `chat-bubble-oval-left` Heroicon with a `flux:badge` showing `$issue->comments_count`
- Embed `<livewire:comments :commentable="$issue" />` in a panel toggled by `showComments`
- Eager-load `comments_count` on the issue where needed

### `ScopeSidebar` changes

- Add `showComments` bool property with `#[Url(as: 'comments', history: true)]`
- Add `clickComments()` method (toggles `showComments`, closes other panels)
- Add Comments button with `chat-bubble-oval-left` icon and `flux:badge` count
- Embed `<livewire:comments :commentable="$scope" />` in toggled panel

## Badge Indicators

### Project issues table (`resources/views/livewire/projects/issues.blade.php`)

- Add `withCount('comments')` to the eager-load in `Projects\Issues::issues()`
- In the Remediation column (shown only for completed projects), append a `flux:badge` with `$issue->comments_count` when count > 0, using the `chat-bubble-oval-left` icon

### Project scopes table (`resources/views/livewire/scopes/view-scopes.blade.php`)

- Add `withCount('comments')` to the scopes eager-load
- Wrap the Notes cell content in a flex row: notes text on the left, `flux:badge` pinned right when `$scope->comments_count > 0`

## Naming Conflict Resolution

The `scopes` table has an existing `comments` column (a JSON array used by the Google Sheets export and the AI analysis context service). This conflicts with the new polymorphic `comments` relationship. Resolve by renaming the column to `checklist_comments` via migration and updating the two affected files:

- `app/Models/Scope.php` — rename `comments` → `checklist_comments` in `$fillable` and `$casts`
- `app/Exports/ProjectReportGoogle.php` — update `$scope->comments` → `$scope->checklist_comments`
- `app/Services/GuidelinesAnalyzer/GuidelinesAnalyzerService.php` — update `'comments' => $scope->comments` → `'comments' => $scope->checklist_comments`

## Files to Create

- `database/migrations/*_create_comments_table.php` (generated via `artisan make:migration`)
- `app/Models/Comment.php`
- `app/Policies/CommentPolicy.php`
- `app/Livewire/Comments/Comments.php`
- `resources/views/livewire/comments/comments.blade.php`
- `database/factories/CommentFactory.php`
- `tests/Feature/CommentTest.php`

## Files to Modify

- `app/Models/Issue.php` — add `morphMany comments`
- `app/Models/Scope.php` — add `morphMany comments`
- ~~`app/Models/Page.php` — add `morphMany comments`~~
- `app/Livewire/Issues/IssueSidebar.php` — add `showComments`, `clickComments()`, rename heading
- `resources/views/livewire/issues/issue-sidebar.blade.php` — Comments button + panel
- `app/Livewire/Scopes/ScopeSidebar.php` — add `showComments`, `clickComments()`
- `resources/views/livewire/scopes/scope-sidebar.blade.php` — Comments button + panel
- `app/Livewire/Projects/Issues.php` — add `withCount('comments')` to eager load
- `resources/views/livewire/projects/issues.blade.php` — badge in Remediation column
- `resources/views/livewire/scopes/view-scopes.blade.php` — flex Notes cell with badge

## Testing

- Happy path: any project member can create a comment on an issue and on a scope
- Happy path: author can edit their own comment within 10 minutes
- Failure: author cannot edit after 10 minutes
- Happy path: author can delete their own comment
- Happy path: site admin can delete any comment
- Failure: non-author, non-admin cannot delete another user's comment
- Failure: report viewer cannot comment on a project they are not a viewer for
- Badge count increments when comment added, decrements when deleted
