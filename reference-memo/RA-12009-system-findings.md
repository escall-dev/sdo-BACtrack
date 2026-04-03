# RA 12009 Reference Review for SDO-BACtrack

Date: March 6, 2026

## Scope

This document summarizes the review of the reference memo in `reference-memo/Implementing-Rules-and-Regulations-of-RA-12009.pdf` against the current SDO-BACtrack system, using the actual intended purpose of the system as basis.

## Purpose Clarification

The main purpose of SDO-BACtrack is not to be a full procurement compliance platform.

Its main purpose is to:

1. Track the BAC procedural timeline.
2. Make BAC members and project owners aware of each procedural step.
3. Automatically calculate duration based on the planned start date, procedural duration days, and planned end date.
4. Show whether activities are on time, completed, delayed, or pending.

## Statement

Based on the actual purpose of BACtrack, the system substantially corresponds to the reference memo as a timeline tracking and procedural awareness tool.

SDO-BACtrack already functions as an internal BAC procedural timeline tracker. It supports project drafting and BAC review, approval and disapproval workflow, activity timelines, compliance tagging, document uploads, adjustment requests, notifications, and printable reports.

The reference memo under RA 12009 covers a broader procurement environment, but BACtrack does not need to implement the entire law if its approved scope is limited to timeline tracking, procedural visibility, and duration monitoring.

For its stated scope, the system is aligned. The main need is to keep terminology accurate, keep duration calculations consistent across all views, and improve awareness features step by step.

## Findings

### Areas That Already Align

1. The system tracks core public bidding milestones such as issuance of bidding documents, post-qualification, BAC resolution recommending award, notice of award, and notice to proceed.
2. The system includes a review and approval workflow using draft, pending approval, approved, and disapproved project states.
3. The system supports project and activity document uploads to support each procedural step.
4. The system makes both BAC members and project owners aware of the progress of activities through status views, notifications, and reports.
5. The duration display in the project owner view correctly computes the number of days from planned start date to planned end date, inclusive.

### Main Gaps Within the Current Scope

1. The legal wording in the system still had outdated RA 9184 references.
2. Duration calculation is correct in the project owner view, but the logic is duplicated in multiple places and should be standardized for consistency.
3. The system can still improve the visibility of step duration, next activity, days remaining, and overdue counts.
4. The system currently supports only one procurement type, which is acceptable for now if public bidding remains the only tracked process.
5. Some future features may still be added if the tracking scope expands, but they are not mandatory if the system remains a timeline tracker only.

### Direct Mismatch Identified

1. The printable report and footer previously referenced RA 9184 instead of RA 12009.
2. This wording should be corrected to avoid mislabeling the system basis.

## What Must Be Added or Applied to the System

To strengthen the system within its actual scope, the following should be applied one by one:

### Step 1. Legal and Terminology Cleanup

- Replace outdated RA 9184 references with RA 12009-aligned wording.
- Keep the wording focused on timeline reference and procedural tracking, not full legal compliance, unless you want that scope expanded.

### Step 2. Standardize Duration Calculation

- Create one shared duration-calculation rule for all screens and reports.
- Use the same inclusive day-count logic already working in the project owner view.
- Ensure planned start, planned end, and procedural duration always match across project view, activity view, reports, and print view.

### Step 3. Improve Procedural Awareness

- Show clearer next-step indicators.
- Show days remaining or days overdue per activity.
- Highlight the current step more clearly for both BAC members and project owners.

### Step 4. Strengthen Timeline Template Control

- Review all template steps and their procedural duration days.
- Confirm that each template duration matches the intended public bidding timeline you want to implement.
- Allow easier updating of durations if the office needs internal adjustments.

### Step 5. Improve Reports and Summaries

- Keep printed and on-screen reports concise and accurate.
- Include total duration, completed steps, delayed steps, and remaining steps.
- Keep the same duration logic in all report outputs.

### Step 6. Optional Scope Expansion

- Only if you want BACtrack to grow beyond timeline tracking, consider adding planning, supplier, PhilGEPS, or contract modules later.
- These are optional future expansions, not immediate requirements for the current system purpose.

## One-by-One Plan

### 1. Fix terminology mismatch

Update visible references that still mention RA 9184 so the system wording is accurate.

### 2. Centralize duration logic

Move the inclusive duration calculation into one reusable rule or helper so every page computes the same number of days.

### 3. Audit all duration displays

Check project view, activity view, reports, print view, and calendar-related summaries to make sure they all use the same duration result.

### 4. Improve user awareness

Add or refine indicators for current step, next step, days remaining, and delayed steps.

### 5. Validate template durations

Review each procedural step duration and confirm it matches the actual office process you want BACtrack to follow.

### 6. Decide future boundary

Decide whether BACtrack will remain a timeline tracker only, or later expand into planning and procurement compliance features.

## Questions for Clarification

1. Do you want BACtrack to remain strictly a timeline tracker, or do you want a limited expansion beyond tracking later?
2. Do you want me to standardize the duration calculation next so all pages use one shared rule?
3. Should the planned duration always be inclusive of both start date and end date, as currently shown in the project owner view?
4. Aside from public bidding, do you plan to track any other procurement procedure in this system?
5. For user awareness, which is more important to add next: next-step display, days remaining, or overdue highlighting?

## Conclusion

SDO-BACtrack is properly understood as a BAC procedural timeline tracking system. With that scope in mind, it already aligns with the need to track steps, make users aware of progress, and compute planned durations.

The immediate work should focus on wording accuracy, standardized duration calculation, and stronger timeline awareness features rather than full procurement-law expansion.