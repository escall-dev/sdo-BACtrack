---
name: Landing estimator/projects layout
overview: Fix the side-by-side layout on `admin/landing.php` so the Procurement Timeline Estimator keeps its current width while the Projects List uses remaining space, without clipping. Make the two-panel container full-width with small side padding, and constrain the Projects List to a fixed height with vertical scrolling. Adjust only layout/sizing (including Projects List table column sizing/wrapping) without changing internal logic or data.
todos:
  - id: fix-dom-structure
    content: Move Projects List card inside `.top-panels` and remove extra closing div so both sections share one two-column container.
    status: completed
  - id: fullwidth-wrapper
    content: Add a dedicated full-width wrapper around `.top-panels` that spans the viewport with small side padding, without changing other page sections.
    status: completed
  - id: two-column-sizing
    content: Update `.top-panels` layout to flex/grid with fixed estimator width (no shrink) and projects pane taking remaining width; prevent clipping with `min-width:0`/`minmax(0,1fr)`.
    status: completed
  - id: projects-fixed-height-scroll
    content: Set fixed height for Projects List card body and make only the table container vertically scrollable.
    status: completed
  - id: projects-table-columns-wrap
    content: "In Projects List table only: add explicit 6-column width distribution, set Project Title min-width 150px, and enforce `white-space: normal` + `word-break: normal` on all cells."
    status: completed
isProject: false
---

## What’s broken now (root cause)

- The estimator is inside `.top-panels`, but **Projects List is outside** due to an extra closing `</div>` after the estimator. You can see this around the end of the estimator card where `.top-panels` is closed before the Projects List starts.

```1313:1392:c:\xampp\htdocs\SDO-BACtrack\admin\landing.php
<main class="main-content">
    <div class="content-wrap">
        <section id="landing-home-panel" class="landing-tab-panel active" role="tabpanel" aria-labelledby="landing-home-tab">

        <div class="top-panels">

        <!-- Detailed Procurement Timeline Planner (table) -->
        <div class="data-card estimator-card">
            ...
        </div>
        </div>

        <!-- Projects List -->
        <?php
        // ...
```

- The page container `.content-wrap` is capped at `max-width: 1120px`, so to make just these two panels “full width”, we should **not** remove that global cap; we should instead create a dedicated full-width wrapper for just this row.

```362:377:c:\xampp\htdocs\SDO-BACtrack\admin\landing.php
.main-content {
    flex: 1;
    /* leave space for the fixed header */
    padding: 160px 20px 32px;
}
.content-wrap {
    max-width: 1120px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 24px;
    padding: 32px 32px;
}
```

## Target behavior (per your requirements)

- Full-width container for both sections (viewport width), with small left/right padding.
- Two columns (grid or flex):
  - **Estimator**: fixed width equal to its current visual size; never shrinks.
  - **Projects List**: fills all remaining space; never clipped.
- Both top-aligned; no vertical stretching.
- Projects List has a fixed height, with vertical scrolling inside it.
- Projects table only:
  - Min width for Project Title column: **150px**.
  - `white-space: normal` and `word-break: normal` on all cells.
  - Explicit width distribution across all 6 columns: **12/25/20/15/18/10**.

## Implementation approach (minimal, layout-only)

- **HTML structure fix (layout only)**
  - Move the Projects List `<div class="data-card projects-card">...</div>` so it sits as the **second child inside** the `.top-panels` container.
  - Remove the extra premature closing `</div>` that currently ends `.top-panels` before Projects List.
  - Add a new wrapper around `.top-panels` (e.g. `.top-panels-fullwidth`) to make *only this row* span the full viewport width.
- **Full-width wrapper (scoped to this row only)**
  - Add CSS for `.top-panels-fullwidth` using the “full-bleed” technique so it escapes `.content-wrap`’s `max-width` without affecting the rest of the page:
    - `width: 100vw; margin-left: calc(50% - 50vw); margin-right: calc(50% - 50vw); padding: 0 12px;`
    - Keep `box-sizing: border-box;`
- **Two-column layout sizing (no estimator shrink)**
  - Convert `.top-panels` from the current `grid-template-columns: minmax(640px, 1.8fr) minmax(360px, 1fr)` to either:
    - **Flex**: `display:flex; align-items:flex-start;` with:
      - `.estimator-card { flex: 0 0 640px; }` (or the current computed width if it’s different once we verify in-browser)
      - `.projects-card { flex: 1 1 auto; min-width: 0; }` (prevents overflow/clipping issues in flex)
    - Or **Grid**: `grid-template-columns: 640px minmax(0, 1fr); align-items:start;`
  - Keep the existing responsive rule at `@media (max-width: 1100px)` to stack into 1 column.
- **Projects List fixed height + vertical scrolling**
  - Make `.projects-card .card-body` a fixed height (e.g. `520px`) and a flex column.
  - Make only `.projects-card .table-responsive` scroll:
    - `flex: 1 1 auto; overflow-y: auto; overflow-x: auto;`
  - Ensure the pager remains visible below the scroller (no layout pushing).
- **Projects table column sizing + wrapping rules (Projects List table only)**
  - Add a `<colgroup>` inside the Projects List table with the suggested 6-column percentage widths.
  - Add CSS scoped to `.projects-card .data-table th, .projects-card .data-table td`:
    - `white-space: normal; word-break: normal;`
  - Enforce min width for the Project Title column via nth-child selector scoped to this table:
    - `.projects-card .data-table th:nth-child(2), .projects-card .data-table td:nth-child(2) { min-width: 150px; }`
  - Remove/override the inline pixel widths on the existing `<th style="width: ...">` entries (this is still “layout only” and is required so the percentage distribution actually applies).

## Verification checklist (quick visual)

- At wide desktop widths, estimator stays the same size and Projects List fills the remaining width.
- No horizontal clipping of Projects List card; no overflow outside viewport.
- Projects List table area scrolls vertically within the fixed card height.
- Project Title column is at least 150px and text wraps by word.
- At ≤1100px, cards stack 1-column and remain usable.

