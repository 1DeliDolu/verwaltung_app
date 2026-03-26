# Active Task Template

## Tomorrow Backlog

- Audit dashboard next slice:
  - implement weekly audit email report after saved presets
  - keep `_docs` plus per-slice commit workflow
  - reuse the existing audit dashboard aggregation wherever possible
  - avoid introducing scheduler-only behavior without a manual admin trigger


## Task Summary

- Request: Continue the audit dashboard backlog with the weekly audit email report slice.
- Business goal: Let admins send a consistent last-7-days central audit summary by email without rebuilding the same review package manually.
- Current gap summary:
  - `/audit` already centralizes cross-source audit visibility
  - saved presets now reduce repeated on-screen filtering
  - there is still no reusable email summary for weekly audit review or stakeholder handoff
  - the codebase has SMTP delivery but no audit-specific report composition flow
- In-scope:
  - add an admin-triggered weekly audit email report flow
  - keep the report based on the central audit sources
  - include a stable weekly window and a compact summary in the email body
  - attach a CSV export for the same report window
  - surface the action from `/audit`
- Out-of-scope:
  - background scheduling or cron orchestration
  - non-admin report delivery
  - redesigning source audit log formats
  - replacing the existing central dashboard filters
- Deadline or urgency: Execute as the next isolated audit-dashboard slice.
- Risk level: medium

## Assumptions

- A manual admin trigger is the safest slice because the repo has SMTP delivery but no first-class scheduler abstraction.
- The weekly report should ignore the admin's currently active dashboard filters and always use the same last-7-days full-dashboard window.
- The same audit aggregation logic should back both the on-screen dashboard and the email report to avoid drift.
- Existing step-by-step doc and commit workflow remains mandatory.

## Lead Agent

- Primary agent: Codex
- Supporting agents: none currently assigned
- Relevant skills: none required from AGENTS skill list for this task

## Affected Layers

- Routes: `routes/web.php`
- Controller layer: `app/Controllers/AuditController.php`
- Service layer:
  - shared audit dashboard aggregation service if extracted
  - new weekly audit email report service
  - existing mail delivery service
- Config layer: `config/mail.php` and environment documentation if new mail settings are needed
- View layer:
  - `resources/views/audit/index.php`
  - new mail templates for the weekly report
- Verification:
  - feature coverage for admin report send flow
  - targeted linting and existing lightweight suite

## Execution Plan

1. Lock the slice on a manual weekly report send flow and exclude scheduler automation.
2. Extract or centralize the reusable audit dashboard aggregation needed by both `/audit` and the email report.
3. Add weekly report composition and delivery:
   - compute the fixed last-7-days report window
   - render text/html email content
   - attach a CSV export for the same window
   - add an admin-only dashboard action to send the report
4. Verification and finish:
   - run targeted PHP lint
   - run relevant existing tests
   - verify admin send behavior and email payload capture
   - verify non-admin boundaries remain unchanged
   - document the slice in `_docs`

## Commit Plan

1. `docs: define weekly audit email slice plan`
   - update this task record with the scoped implementation plan
2. `feat: add weekly audit email reporting`
   - implement shared audit aggregation, report composition, dashboard action, and docs
3. `test: verify weekly audit email reporting`
   - add or adjust verification coverage and finalize `_docs` verification note

## Checkable Work Items

- [x] Clarify the current behavior and target behavior
- [x] Identify affected controllers, services, views, routes, and config
- [x] Choose manual weekly report delivery over scheduler-only automation for this slice
- [ ] Implement weekly audit report composition
- [ ] Add admin report-send action to the audit dashboard
- [ ] Verify admin send behavior and email payload shape
- [ ] Verify non-admin boundaries still hold
- [ ] Review logs, warnings, and edge cases
- [ ] Document result and open risks in `_docs`

## Progress Log

### Step 1
- Status: completed
- Notes: Reviewed the current audit dashboard, SMTP mail delivery, and the absence of a built-in scheduler abstraction.

### Step 2
- Status: completed
- Notes: Chose a manual admin-triggered weekly report slice so the report can ship now and remain reusable later from cron or another orchestrator.

### Step 3
- Status: pending
- Notes: Implement shared audit aggregation plus weekly report composition and dashboard send action.

### Step 4
- Status: pending
- Notes: Verify send behavior, document the slice in `_docs`, and commit it as its own unit.

## Verification Plan

- Automated tests:
  - run targeted PHP lint on route, controller, config, service, and view files
  - run the existing lightweight suite
- Manual checks:
  - validate the `/audit` weekly report card renders the expected report window and recipients
  - validate the send action uses the fixed weekly window rather than current dashboard filters
  - validate the email includes summary content and a CSV attachment
- Permission checks:
  - confirm non-admin users still cannot use central audit management flows
  - confirm report-send routes remain admin-only
- Data integrity checks:
  - confirm the report window is consistent across subject, body, and CSV attachment
  - confirm the report uses all central audit sources
- Error-path checks:
  - confirm SMTP or delivery failures degrade safely through flash messaging
  - confirm missing recipients fail safely

## Verification Evidence

- Planning evidence:
  - reviewed `app/Controllers/AuditController.php`
  - reviewed `app/Services/MailService.php`
  - reviewed `config/mail.php`
  - reviewed existing audit dashboard and mail audit views
  - confirmed the repo currently has no dedicated scheduler abstraction
- Implementation evidence:
  - pending weekly report slice implementation

## Result Review

- Outcome: planning updated
- What changed: The active task record now scopes the next audit-dashboard slice to manual weekly email reporting with explicit commit boundaries.
- What did not change: No weekly audit report code has been added in this planning update.
- Risks still open:
  - the report should reuse dashboard aggregation rather than fork it
  - delivery tests need a stable local mail capture strategy
- Recommended follow-up: extract the shared audit aggregation first, then wire email composition and the admin send action on top of it.

## Completion Notes

- Definition of done met: no
- Lessons update required: no
- Related lesson entry: Lesson 4, separate each meaningful step into its own docs and commit unit
