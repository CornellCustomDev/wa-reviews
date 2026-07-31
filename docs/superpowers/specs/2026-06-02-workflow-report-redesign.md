# Workflow Panel & Report Finalization Redesign

## Summary

The Workflow panel becomes a status-aware action guide rather than a passive status display. Forward-progress actions are promoted to direct CTAs in the panel; backwards/override actions remain behind the gear icon. The Report Data tab is removed — its editing surface moves to the draft report page, and Report Viewers moves into the Workflow panel.

## Workflow Panel

The panel is restructured to show a status-appropriate primary CTA for every forward-moving state. The gear icon is reserved exclusively for backwards/override actions.

### Per-status behavior

**NotStarted (no reviewer assigned)**
- Shows: "Assign Reviewer" / "Assign to Me" button (unchanged from today)
- Gear: hidden

**NotStarted (reviewer assigned)**
- Shows: reviewer name
- CTA: prominent "Start Review" button (replaces gear/modal path)
- Gear: hidden (no meaningful backwards action)

**InProgress**
- Shows: reviewer name, status
- CTA: prominent "Review & Finalize Report" button — navigates to the draft report page
- Gear: opens modal containing "Stop Review" only

**ReviewComplete**
- Shows: reviewer name, status
- Description: "The review is complete. Work should be verified after fixes have been applied."
- CTA: "Start Verification" button
- Assigns verifier using the same assign pattern as reviewer
- Gear: opens modal containing "Re-open Review" only

**VerificationReview**
- Shows: verifier name, status (reviewer is recorded on the report itself)
- CTA: "Complete Verification" button
- Gear: opens modal containing "Pause Verification" only

**Closed**
- Shows: reviewer name, verifier name, status, completed date
- CTA: "View Report" link/button
- Gear: opens modal containing "Re-open" only

### Report Viewers

- Moved from the Report tab into the bottom of the Workflow panel
- Visible from `InProgress` onward
- Same add/remove functionality as today

## Draft Report Page

The existing `project.report` route is enhanced into the completion surface for the `InProgress → ReviewComplete` transition.

### Editing

- Inline editing of the 4 report fields using the same form as the current Report Data component:
  - URLs included in review
  - URLs excluded from review
  - Testing notes and procedure
  - Summary and Overall Findings (required)
- The `update-report` Livewire component is reused/adapted here

### Completing the review

- "Complete Review" button present when the project is `InProgress`
- Button is disabled until Summary is filled
- On click: advances project status to `ReviewComplete`

### Post-completion

- When status is `ReviewComplete` or later, report fields remain editable but "Complete Review" button is not shown

### Guidance (open UX gap)

- The page currently has no framing or context for the reviewer
- A future iteration should add status-aware guidance — e.g., a callout explaining what the page is for, what fields are needed, and that Summary is required before completing
- Exact treatment (callout block, inline helper text, description header) to be determined when seen in context

## Report Tab

- Removed entirely
- Both of its components now live elsewhere: Report Data editing → draft report page, Report Viewers → Workflow panel

## Out of scope

- Verification workflow UX (ReviewComplete → VerificationReview → Closed states are structurally supported but their Workflow panel CTAs are wired up without deep UX treatment for this iteration)
- Timeline/checklist view in the Workflow panel (identified as a future enhancement to show completion status at a glance)
