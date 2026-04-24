# Issue Status Permissions

This document defines which roles can set which issue statuses, gated by the project's current workflow status.

| Project Status | Customer (report viewer) | Reviewer | Verifier | Team Admin |
|---|---|---|---|---|
| Not Started | — | existing behavior | — | existing behavior |
| In Progress | — | existing behavior | — | existing behavior |
| Review Complete | — | Reviewed, False Positive | — | same as reviewer |
| Customer Response | Fixed, Not Being Fixed, False Positive, Reviewed | — | — | same as customer |
| Verification Review | — | Verified Fixed, Not Fixed, False Positive, Not Being Fixed | same as reviewer | all roles |
| Closed | — | — | — | — |

## Notes

- **Customer** = report viewer added to the project via the Report Viewers panel
- **Verifier** has no issue status access until the project reaches Verification Review
- **Reviewed** in Customer Response acts as an "undo" — customer can reset an issue back to unresolved
- **New Issue** status exists but has no enforced workflow yet
- Site Admins follow Team Admin rules
