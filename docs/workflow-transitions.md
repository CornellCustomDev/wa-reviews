# Project Workflow Transitions

This document defines the project lifecycle and the available status transitions.

## Status Progression

```
Not Started → In Progress → Review Complete → Customer Response → Verification Review → Closed
```

## Transition Map

| Current Status | Forward Button | → Next Status | Back Button | → Previous Status |
|---|---|---|---|---|
| Not Started | "Start Review" | In Progress | — | — |
| In Progress | "Complete Review" | Review Complete | "Stop Review" | Not Started |
| Review Complete | "Send to Customer" | Customer Response | "Re-open Review" | In Progress |
| Customer Response | "Start Verification" | Verification Review | "Pause Verification" | Review Complete |
| Verification Review | "Complete Verification" | Closed | "Re-open Verification" | Customer Response |
| Closed | — | — | "Re-open" | Verification Review |

## Status Buckets

| Bucket | Statuses | Meaning |
|---|---|---|
| `activeCases()` | Not Started, In Progress | Review underway; report not yet visible to customer |
| `reviewedCases()` | Review Complete, Customer Response, Verification Review | Report accessible; comments open |
| `completedCases()` | Closed | Cycle complete; read-only |

## Notes

- Transition buttons are gated by the `update-status` permission (Team Admin and Reviewer level)
- The verifier assignment section in the Workflow panel is hidden until Review Complete
- Report viewers (customers) gain access to the project once it enters a `reviewedCases()` status
- `completed_at` timestamp is recorded when status reaches Review Complete
