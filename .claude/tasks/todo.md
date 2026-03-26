# Active Task Template

## Tomorrow Backlog

- Audit dashboard next slice:
  - implement saved filter presets first
  - keep `_docs` plus per-slice commit workflow
  - defer weekly email audit report until preset flow is stable
  - start from current central audit dashboard state


## Task Summary

- Request: Continue the audit dashboard backlog with saved filter presets.
- Business goal: Let admins save and quickly reapply recurring central-audit filter combinations without re-entering the same search, source, outcome, and date ranges.
- Current gap summary:
  - `/audit` already supports useful filters and drill-down links
  - recurring admin queries still need to be rebuilt manually
  - there is no named preset list for common audit investigations such as failure-only, task-only, or date-bounded review views
  - weekly email audit report is a larger follow-up and should remain deferred until preset behavior is stable
- In-scope:
  - add named saved filter presets for the central audit dashboard
  - keep the feature admin-only
  - support saving the current filter combination
  - support listing and deleting saved presets
  - support reapplying presets directly from the dashboard UI
- Out-of-scope:
  - weekly email audit report delivery
  - non-admin preset management
  - changes to the underlying audit log formats
- Deadline or urgency: Execute as the next isolated audit-dashboard slice.
- Risk level: medium

## Assumptions

- The central audit dashboard remains admin-only and can safely own admin-specific preset storage.
- Saved presets should be durable across sessions, so persistence should not depend only on flash state or URL history.
- The feature should reuse the existing `/audit` query parameters instead of inventing a second filter language.
- Existing step-by-step doc and commit workflow remains mandatory.

## Lead Agent

- Primary agent: Codex
- Supporting agents: none currently assigned
- Relevant skills: none required from AGENTS skill list for this task

## Affected Layers

- Routes: `routes/web.php`
- Controller layer: `app/Controllers/AuditController.php`
- Service layer: new audit preset service if needed
- Model layer: new audit preset model if persistence is database-backed
- Database layer: new SQL migration for preset storage
- View layer:
  - `resources/views/audit/index.php`
- Verification:
  - feature tests for admin save/apply/delete flows
  - targeted linting and existing lightweight suite

## Execution Plan

1. Lock the slice on saved presets and exclude weekly email behavior.
2. Add persistence for named presets keyed to the admin user.
3. Add dashboard UI to:
   - save the current filter set
   - list saved presets
   - reapply a preset through the existing `/audit` filter query shape
   - delete a preset
4. Verification and finish:
   - run targeted PHP lint
   - run relevant existing tests
   - verify admin preset save/apply/delete behavior
   - verify non-admin access boundaries remain unchanged
   - document the slice in `_docs`

## Commit Plan

1. `docs: define audit preset slice plan`
   - update this task record with the scoped implementation plan
2. `feat: add saved filter presets to the audit dashboard`
   - implement preset storage and dashboard UI
   - add/update `_docs` entry for the slice
3. `test: verify audit dashboard preset behavior`
   - add or adjust verification coverage and finalize `_docs` verification note

## Checkable Work Items

- [x] Clarify the current behavior and target behavior
- [x] Identify affected controllers, services, views, models, and routes
- [x] Choose saved presets over weekly email report for the next slice
- [ ] Implement persistence for audit filter presets
- [ ] Render saved presets in the audit dashboard
- [ ] Verify admin save/apply/delete behavior
- [ ] Verify non-admin boundaries still hold
- [ ] Review logs, warnings, and edge cases
- [ ] Document result and open risks in `_docs`

## Progress Log

### Step 1
- Status: completed
- Notes: Reviewed `AuditController`, `resources/views/audit/index.php`, existing audit tests, and the current `/audit` filter shape.

### Step 2
- Status: completed
- Notes: Chose `saved filter presets` as the next slice because it extends the current dashboard directly and avoids the extra delivery/runtime concerns of a weekly email report.

### Step 3
- Status: pending
- Notes: Add preset persistence plus audit dashboard save/list/delete UI.

### Step 4
- Status: pending
- Notes: Verify preset flows, document the slice in `_docs`, and commit it as its own unit.

## Verification Plan

- Automated tests:
  - run targeted PHP lint on route, controller, model, service, and view files
  - run the existing lightweight suite
- Manual checks:
  - validate preset save from a filtered audit dashboard view
  - validate preset reapply links populate the existing dashboard filters
  - validate preset delete removes the entry from the dashboard
- Permission checks:
  - confirm non-admin users still cannot reach central audit management flows
  - confirm preset mutation routes remain admin-only
- Data integrity checks:
  - confirm preset storage preserves the same filter semantics used by `/audit`
- Error-path checks:
  - confirm invalid or empty preset submissions degrade safely
  - confirm deleting a foreign or missing preset fails safely

## Verification Evidence

- Planning evidence:
  - reviewed `app/Controllers/AuditController.php`
  - reviewed `resources/views/audit/index.php`
  - reviewed existing audit-related feature tests
  - reviewed the current `/audit` routing and request flow
- Implementation evidence:
  - pending preset slice implementation

## Result Review

- Outcome: planning updated
- What changed: The active task record now scopes the next audit-dashboard slice to saved filter presets with explicit commit boundaries.
- What did not change: No audit dashboard code changed in this planning update.
- Risks still open:
  - persistence must stay aligned with the existing `/audit` filter semantics
  - admin-only mutation routes need explicit verification
- Recommended follow-up: implement the preset persistence layer first, then wire the dashboard UI on top of it.

## Completion Notes

- Definition of done met: no
- Lessons update required: no
- Related lesson entry: Lesson 4, separate each meaningful step into its own docs and commit unit
