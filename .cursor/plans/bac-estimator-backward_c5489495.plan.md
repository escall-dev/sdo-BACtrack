---
name: bac-estimator-backward
overview: Update `admin/landing.php` so the timeline section is a single backward-tracking “Procurement Timeline Estimator” with a procurement-type dropdown, real-time SVP budget validation, and an auto-computed “Latest Allowable Date” based on the selected implementation date.
todos:
  - id: remove-quick-estimator
    content: In `[admin/landing.php]`, delete the entire “Procurement Timeline Estimator” card (HTML + the JS `calculateTimeline()` function + any related form elements like `timelineForm`/`timelineResults`).
    status: completed
  - id: rename-planner-header
    content: In `[admin/landing.php]`, change the estimator table card header text from “Procurement Timeline Planner” to “Procurement Timeline Estimator” (header text only).
    status: completed
  - id: add-controls-to-estimator
    content: In `[admin/landing.php]`, add a procurement-type dropdown and an `Estimated Budget (PHP)` input above the implementation date picker; add a read-only `Latest Allowable Date` field below the table. Repurpose the existing date input to mean *Implementation date* (based on your requirement).
    status: completed
  - id: sv-budget-validation
    content: In `[admin/landing.php]`, implement real-time SVP budget validation tied to the selected procurement type. Show warnings with the exact messages you provided on budget input/change events.
    status: completed
  - id: backward-tracking-engine
    content: In `[admin/landing.php]`, replace `PLANNER_STAGES` + forward computation with a backward-tracking scheduler using `config/procurement.php`’s `backward_timeline_stages` for the selected procurement type. Compute latest allowable END dates (user-selected) anchored on `implementationDate - 1 day`, including 0-day milestone handling.
    status: completed
  - id: wire-recompute-events
    content: In `[admin/landing.php]`, ensure the schedule and the bottom `Latest Allowable Date` auto-refresh on procurement-type change and implementation-date change, and recompute when “Add days” is updated (via Update button and/or input event as you prefer).
    status: completed
isProject: false
---

## Key changes

1. Consolidate the UI
  - Remove the entire card section labeled “Procurement Timeline Estimator” (the quick estimator form that calls `calculateTimeline()`).
  - Rename the card header “Procurement Timeline Planner” to “Procurement Timeline Estimator” (text only).
2. Extend the remaining estimator table
  - Add a dropdown to select “Procurement Type / Mode of Procurement”.
  - Add an “Estimated Budget (PHP)” input and show SVP validation warnings in real time.
  - Repurpose the existing date picker to represent the *Implementation date* (per your requirement) and run backward tracking from it.
  - Add a read-only “Latest Allowable Date” field at the bottom of the estimator.
3. Implement backward tracking (latest allowable schedule)
  - Replace the hard-coded `PLANNER_STAGES` with procurement-type-specific backward steps and day counts from `config/procurement.php` (`backward_timeline_stages`).
  - For the selected implementation date, compute backward through each backward step:
    - Set an internal cursor to `implementationDate - 1 day`.
    - Iterate steps from last to first, computing each step’s latest allowable END date and derived START date using the same zero-day milestone behavior as the backend `ProcurementTimelineService`.
  - Populate the table:
    - Fill “End Date” as the latest allowable date per step.
    - Fill “Start Date” derived from END date and effective duration.
  - Compute the bottom “Latest Allowable Date” as the END date of the first backward step.
4. Real-time budget validation (SVP only)
  - When procurement type is `SMALL_VALUE_PROCUREMENT` (SVP 200k and below):
    - Warn if `budget >= 200000.00` with: `The budget for Small Value Procurement (200k and below) must not exceed 199,999.99.`
  - When procurement type is `SMALL_VALUE_PROCUREMENT_200K` (SVP 200k and above):
    - Warn if `budget < 200000.00` with: `The minimum budget for this procurement type is 200,000.00.`
    - Warn if `budget >= 2000000.00` with: `The maximum budget for this procurement type is 1,999,999.99.`
  - Apply validation on `input` / `change` events (not only on submit).

## File(s) to change

- `[admin/landing.php](admin/landing.php)`

## Implementation notes

- Filter the dropdown to procurement types that exist in `procurementConfig()['workflows']` (the config currently lacks `DIRECT_PROCUREMENT_STI`).
- Keep the existing table structure/columns, but make the calculated date inputs read-only so the schedule is fully driven by implementation date + step durations + “Add days”.
- Update `computeEarliest()`, `computeLatest()`, `updateRow()`, and the row rendering logic so they call the new backward-tracking compute function instead of the current forward/percentage buffer approach.

### Backward scheduling (concept)

```mermaid
flowchart TD
  A[User selects Procurement Type] --> B[Load backward steps + day counts]
  C[User selects Implementation date] --> D[Backward compute]
  D --> E[Compute each step latest allowable END date]
  E --> F[Fill Start/End columns]
  E --> G[Latest Allowable Date field = first step END]
  H[User inputs Budget] --> I[Real-time SVP validation warnings]
```



