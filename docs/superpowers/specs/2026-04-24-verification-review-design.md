# Verification Review — Design Spec

**Date:** 2026-04-24
**Branch:** verification-reviews
**Issue:** #136

---

## Overview

This feature extends the project workflow to support a full accessibility review lifecycle: from initial review through customer response and verification. It adds a verifier assignment, expands project and issue statuses, introduces phase-gated comment and status-update permissions, and surfaces a verifier alongside the reviewer in the project list.

The guiding principle is **light and fast** — good communication and clarity about what's outstanding, not a rigid accounting system.

---

## 1. Project Status Expansion

### Enum Values

The `ProjectStatus` enum expands from 3 to 6 values, in linear progression:

| Case | Value | Description |
|---|---|---|
| `NotStarted` | "Not Started" | Project created, review not begun |
| `InProgress` | "In Progress" | Reviewer actively working |
| `ReviewComplete` | "Review Complete" | Report finalized, ready to send to customer |
| `CustomerResponse` | "Customer Response" | Report sent; customer applying fixes |
| `VerificationReview` | "Verification Review" | Verifier checking customer's fixes |
| `Closed` | "Closed" | Cycle complete |

### Status Buckets

Three named bucket methods replace the current `activeCases()` / `completedCases()` split:

| Method | Statuses | Purpose |
|---|---|---|
| `activeCases()` | NotStarted, InProgress | Report not yet visible to customer |
| `reviewedCases()` | ReviewComplete, CustomerResponse, VerificationReview | Report accessible to customer; comments open |
| `completedCases()` | Closed | Cycle complete; read-only |

### Milestone Timestamp

`completed_at` is wired to `ReviewComplete` (previously `Completed`). No new timestamp fields or history table — the existing `activities` log captures all status transitions with actor and timestamp.

### `nextStatus()` / `previousStatus()`

Updated to reflect the new linear chain. Both methods return the adjacent status; the UI controls which transitions are surfaced to the user.

---

## 2. Verifier Assignment

### Data Model

`project_assignments` gains a `role` enum column: `reviewer` | `verifier` (default: `reviewer`).

`projects.assignment_id` FK is dropped. Both reviewer and verifier are queried from `project_assignments` filtered by `project_id`, `role`, and `deleted_at IS NULL`.

### Project Model Changes

| Current | New |
|---|---|
| `assignment()` | becomes role-aware, filters `role = reviewer` |
| `reviewer()` | unchanged interface, updated internals |
| `assignToUser()` | unchanged interface; internally creates assignment with `role = reviewer` |
| `unassign()` | internally filters by `role = reviewer` |
| `isReviewer(User)` | unchanged |
| `withReviewer` scope | joins via `project_assignments.project_id + role + deleted_at` |
| *(new)* `verifierAssignment()` | HasOne via `role = verifier` |
| *(new)* `verifier()` | HasOneThrough to User via verifier assignment |
| *(new)* `assignVerifier(User)` | mirrors `assignToUser` |
| *(new)* `unassignVerifier()` | mirrors `unassign` |
| *(new)* `isVerifier(User)` | mirrors `isReviewer` |
| *(new)* `withVerifier` scope | same join pattern as `withReviewer`, role = verifier |

### Workflow UI

The Workflow panel gains a verifier section mirroring the existing reviewer section (assign, remove, "assign to me"). The verifier section is **hidden until project status reaches `ReviewComplete`**.

Verifier assignment uses the same `update-reviewer` permission as the reviewer.

---

## 3. Issue Status Expansion

### Enum Values

| Case | Value | Set By | Phase |
|---|---|---|---|
| `Reviewed` | "Reviewed" | default | — |
| `Fixed` | "Fixed" | Customer | CustomerResponse |
| `NotBeingFixed` | "Not Being Fixed" | Customer | CustomerResponse |
| `FalsePositive` | "False Positive" | Customer or Reviewer/Verifier | varies |
| `Verified` | "Verified Fixed" | Reviewer / Verifier | VerificationReview |
| `NotFixed` | "Not Fixed" | Reviewer / Verifier | VerificationReview |
| `NewIssue` | "New Issue" | *(status added; no workflow implemented yet)* | — |

`WontFix` is renamed to `NotBeingFixed` (same string value `"Not Being Fixed"` — no data migration needed).

### Issue Status Permission Matrix

See also: [`/docs/issue-status-permissions.md`](/docs/issue-status-permissions.md)

| Project Status | Customer (report viewer) | Reviewer | Verifier | Team Admin |
|---|---|---|---|---|
| NotStarted / InProgress | — | existing behavior | — | existing behavior |
| ReviewComplete | — | Reviewed, False Positive | — | same as reviewer |
| CustomerResponse | Fixed, Not Being Fixed, False Positive, Reviewed | — | — | same as customer |
| VerificationReview | — | Verified Fixed, Not Fixed, False Positive, Not Being Fixed | same as reviewer | all roles |
| Closed | — | — | — | — |

Enforcement is in policy, not in the enum. The `UpdateStatus` issue component renders a filtered dropdown based on the viewer's role and the project's current status.

---

## 4. Comment Policy

### Rule

Comments are allowed only when the project is in a `reviewedCases()` status (ReviewComplete, CustomerResponse, VerificationReview). No one can comment during `activeCases()` or `Closed`.

### Who Can Comment (when in an allowed phase)

| Role | Can Comment |
|---|---|
| Reviewer (assigned) | ✓ |
| Verifier (assigned) | ✓ |
| Team Admin | ✓ |
| Report Viewer (customer) | ✓ |
| Site Admin | ✓ |
| Other team members | ✗ |

### Implementation

`CommentPolicy::create` is updated to:
1. Resolve the project from the commentable (Issue → project, Scope → project)
2. Check that project status is in `reviewedCases()`
3. Check that the user is one of: assigned reviewer, assigned verifier, team admin, report viewer, site admin

The existing 10-minute edit window and author-or-admin delete rules are unchanged.

---

## 5. Workflow Status Transitions

See also: [`/docs/workflow-transitions.md`](/docs/workflow-transitions.md)

| Current Status | Forward Button | → Status | Back Button | → Status |
|---|---|---|---|---|
| NotStarted | "Start Review" | InProgress | — | — |
| InProgress | "Complete Review" | ReviewComplete | "Stop Review" | NotStarted |
| ReviewComplete | "Send to Customer" | CustomerResponse | "Re-open Review" | InProgress |
| CustomerResponse | "Start Verification" | VerificationReview | "Pause Verification" | ReviewComplete |
| VerificationReview | "Complete Verification" | Closed | "Re-open Verification" | CustomerResponse |
| Closed | — | — | "Re-open" | VerificationReview |

Status transitions are gated by the existing `update-status` permission (Team Admin and Reviewer level).

---

## 6. Sort & Filter

- Status filter options expand to all 6 project statuses
- A **verifier** sort/filter option is added alongside reviewer, using the new `withVerifier` scope (same join pattern as `withReviewer`)
- The project list grouped view uses `activeCases()` / `reviewedCases()` / `completedCases()` for grouping headers

---

## 7. Durable Documentation

Two markdown files are written and maintained under `/docs`:

- `/docs/issue-status-permissions.md` — the full issue status permission matrix (Section 3)
- `/docs/workflow-transitions.md` — the full workflow transition map (Section 5)

These are kept in sync with the code and are the authoritative reference for business rules.

---

## Out of Scope (Noted for Future)

- Multiple verifiers per project (currently: one responsible party)
- "New Issue" workflow (status exists, no creation flow yet)
- Email notifications on status change
- Guided "Complete Report" button flow
- Customer response per-reviewer visibility controls
- Extending reviewer issue-status access during CustomerResponse
