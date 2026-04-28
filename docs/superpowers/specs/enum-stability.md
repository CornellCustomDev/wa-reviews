# Plan: Durable Enum Migration for IssueStatus and ProjectStatus

## Context

PR #148 (`verification-reviews` branch) expands the project workflow from 3 statuses to 6, and adds new issue statuses. The change removes `ProjectStatus::Completed = 'Completed'` and replaces it with four new cases. Any existing row with `projects.status = 'Completed'` will throw a fatal error after deployment because Laravel's enum cast calls `ProjectStatus::from()` on the stored string, which has no matching case.

The deeper problem is that enum backing values are the display strings themselves (e.g., `'Not Being Fixed'`). Whenever a label is renamed, DB values become stale. This plan solves durability **first** on a new branch, then `verification-reviews` merges that branch and its changes land cleanly on top.

**Note on IssueStatus in PR #148:** `WontFix = 'Not Being Fixed'` was renamed to `NotBeingFixed = 'Not Being Fixed'` — the backing value is identical, so no data migration is needed for issues in the current PR.

---

## Phase 1: Enum Stability Refactor (this branch — `enum-stability`)

Deploy this first. Decouples stored DB values from display strings so future label changes never require a data migration.

### Design: stable snake_case backing values + `label()` method

**ProjectStatus new backing values** (keeping existing `Completed` case for now):
| Case | Old value | New value |
|------|-----------|-----------|
| NotStarted | `'Not Started'` | `'not_started'` |
| InProgress | `'In Progress'` | `'in_progress'` |
| Completed | `'Completed'` | `'completed'` |

**IssueStatus new backing values:**
| Case | Old value | New value |
|------|-----------|-----------|
| Reviewed | `'Reviewed'` | `'reviewed'` |
| Fixed | `'Fixed'` | `'fixed'` |
| WontFix / NotBeingFixed | `'Not Being Fixed'` | `'not_being_fixed'` |
| FalsePositive | `'False Positive'` | `'false_positive'` |
| Verified | `'Verified Fixed'` | `'verified_fixed'` |

### Implementation Steps

#### 1. Add `label()` to both enums

```php
public function label(): string
{
    return match ($this) {
        self::NotStarted => 'Not Started',
        self::InProgress => 'In Progress',
        self::Completed  => 'Completed',
    };
}
```

#### 2. Update `toSelectArray()` in both enums (or `NamedEnum`)

Change the `option` field to use `$case->label()` so dropdowns still show human-readable text while storing snake_case values:

```php
['value' => $case->value, 'option' => $case->label()]
```

#### 3. Audit `description()` methods

These already return independent display strings — confirm they don't reference `->value`.

#### 4. Create a data migration

```
lando php artisan make:migration migrate_status_columns_to_snake_case --no-interaction
```

Map all existing display-string values → snake_case in both tables. Include a `down()` that reverses:

```php
// projects
$map = ['Not Started' => 'not_started', 'In Progress' => 'in_progress', 'Completed' => 'completed'];
// issues
$map = ['Reviewed' => 'reviewed', 'Fixed' => 'fixed', 'Not Being Fixed' => 'not_being_fixed',
        'False Positive' => 'false_positive', 'Verified Fixed' => 'verified_fixed'];
```

#### 5. Audit Blade views using `->value()`

Nine view files call `->value()`. For each occurrence:
- If used for **display text** → change to `->label()`
- If used for **CSS class names or data attributes** → `->value` is valid (snake_case works as CSS identifiers)

Key files:
- `resources/views/livewire/projects/report.blade.php`
- `resources/views/livewire/issues/assessment.blade.php`
- `resources/views/livewire/issues/show-issue.blade.php`
- `resources/views/exports/project-report.blade.php`
- `resources/views/livewire/issues/item-observation.blade.php`
- `resources/views/livewire/issues/items-recommended.blade.php`
- `resources/views/livewire/issues/instructions.blade.php`
- `resources/views/livewire/items/create-item.blade.php`
- `resources/views/livewire/scopes/view-scopes.blade.php`

#### 6. Update `ActivityLog.php` match statement

`app/Livewire/Projects/ActivityLog.php` uses `ProjectStatus::X->value` for string matching. After this refactor those values become snake_case — update the match accordingly.

#### 7. Update tests

- `tests/Unit/Enums/IssueStatusTest.php` — update assertions expecting display strings to expect snake_case values
- `tests/Unit/Enums/ProjectStatusTest.php` — same
- Add assertions that `->label()` returns the expected display string

### Deployment

This branch deploys atomically (migration + code together). After deployment all stored status values are snake_case and `label()` provides display text. No breaking changes to `verification-reviews` since `ProjectStatus::Completed` still exists in this phase.

---

## Phase 2: verification-reviews Merges Phase 1, Then Completes

After Phase 1 is merged and deployed, `verification-reviews` rebases/merges it and adds:

### 1. Expand `ProjectStatus` with new cases (already in PR #148)

New cases use snake_case backing values:
```php
case ReviewComplete    = 'review_complete';
case CustomerResponse  = 'customer_response';
case VerificationReview = 'verification_review';
case Closed            = 'closed';
```

Remove `Completed` case and add `label()` entries for all new cases.

### 2. Expand `IssueStatus` with new cases (already in PR #148)

```php
case NotFixed  = 'not_fixed';
case NewIssue  = 'new_issue';
```

### 3. Create a data migration for `Completed` → `Closed`

Since Phase 1 already converted `'Completed'` → `'completed'`, this migration converts:
```php
DB::table('projects')->where('status', 'completed')->update(['status' => 'closed']);
```

### 4. Fix existing bug in ActivityLog.php

The PR currently references `ProjectStatus::Completed->value` which no longer exists after removing that case. Update the `statusColor()` match to cover the four new cases with appropriate colors.

### 5. Update tests for new cases

---

## Critical Files

- `app/Enums/ProjectStatus.php`
- `app/Enums/IssueStatus.php`
- `app/Enums/NamedEnum.php`
- `app/Livewire/Projects/ActivityLog.php` (has Completed bug in Phase 2)
- `app/Models/Project.php` — enum cast
- `app/Models/Issue.php` — enum cast
- `database/migrations/` — new migrations for both phases
- `tests/Unit/Enums/IssueStatusTest.php`
- `tests/Unit/Enums/ProjectStatusTest.php`

---

## Verification

**Phase 1:**
1. Run migration: `lando php artisan migrate`
2. Confirm values converted: `lando php artisan tinker` → `Project::query()->distinct()->pluck('status')`
3. Load a project in the browser — status dropdowns show human-readable labels, not snake_case
4. Run tests: `lando php artisan test --compact`

**Phase 2:**
1. Run migration — confirm no projects have `status = 'completed'` after
2. Verify full project status workflow in browser through all 6 states
3. Run full test suite: `lando php artisan test --compact`
