# Active Task Template

## Tomorrow Backlog

- Department module next slice:
  - start from `config/departments.php`, `app/Services/DepartmentService.php`, and `resources/views/departments`
  - keep `_docs` plus per-slice commit workflow
  - land one user-visible slice at a time
  - defer the audit-dashboard backlog until this department slice is closed


## Task Summary

- Request: Continue department-module work starting with `config/departments.php` and `resources/views/departments`.
- Business goal: Make department pages more consistently config-driven and reduce UI drift between configured department profiles and what the user actually sees.
- Current gap summary:
  - department config already defines `tagline`, `focus`, `hero`, `responsibilities`, `workflows`, `kpis`, and `leader_tasks`
  - `leader_tasks` are visible in department pages, but configured KPI data is only surfaced on the dashboard, not in department index/detail screens
  - department-specific partials still carry hardcoded "playbook" content that overlaps with config intent and increases duplication
  - the next slice should improve department-facing UI without weakening existing department and role boundaries
- In-scope:
  - review and tighten config-to-view mapping for department pages
  - expose missing config-backed department information in the departments UI
  - reduce repeated hardcoded department presentation where config should be the source of truth
  - verify department visibility and management actions remain correct
- Out-of-scope:
  - unrelated infrastructure work
  - unrelated auth changes unless directly required by department flow updates
  - new workflow modules outside the department area
- Deadline or urgency: Resume from this checkpoint and execute in small committed slices.
- Risk level: medium

## Assumptions

- Department behavior is centrally enriched by `DepartmentService::departmentProfile()` and `DepartmentService::enrichDepartment()`.
- The safest next slice is a UI-focused one that reuses existing service methods before changing deeper business rules.
- `summaryStatsForDepartment()` and existing profile normalization logic can be reused instead of adding parallel presentation logic.
- Existing step-by-step doc and commit workflow remains mandatory.

## Lead Agent

- Primary agent: Codex
- Supporting agents: none currently assigned
- Relevant skills: none required from AGENTS skill list for this task

## Affected Layers

- Config: `config/departments.php`
- Service layer: `app/Services/DepartmentService.php`
- Controller layer: `app/Controllers/DepartmentController.php` only if additional view data is required
- View layer:
  - `resources/views/departments/index.php`
  - `resources/views/departments/show.php`
  - selected `resources/views/departments/*/index.php` partials if duplication is removed
- Verification:
  - relevant unit/feature tests if behavior changes justify coverage
  - targeted linting and manual permission-path checks

## Execution Plan

1. Freeze the next slice around the actual gap:
   - confirm which config fields are already rendered
   - confirm which fields are still hidden or duplicated
   - lock the first implementation slice before editing
2. Slice A: bring config-backed department summary data into the department UI:
   - expose KPI/stat cards in department index and/or department detail where they are currently missing
   - keep labels driven by configured `kpis`
   - avoid duplicating dashboard-only logic in the views
3. Slice B: reduce hardcoded department playbook duplication:
   - identify department-specific partial content that overlaps with config responsibilities/workflows/leader guidance
   - move repeated presentation intent into config-driven structures where practical
   - keep only genuinely department-unique markup in partials
4. Verification and finish:
   - run targeted PHP lint
   - run relevant existing tests
   - manually verify positive and negative department paths
   - document the slice in `_docs`

## Commit Plan

1. `docs: define department module next-slice plan`
   - update this task record with the scoped implementation plan
2. `feat: surface department profile summary data in department pages`
   - implement Slice A
   - add/update `_docs` entry for the slice
3. `refactor: reduce duplicated department playbook view content`
   - implement Slice B
   - add/update `_docs` entry for the slice
4. `test: verify department config-driven page behavior`
   - add or adjust verification coverage if needed
   - finalize `_docs` verification note

## Checkable Work Items

- [x] Clarify the current behavior and target behavior
- [x] Identify affected controllers, services, views, models, and routes
- [x] Review config-to-view gaps in the current department module
- [ ] Review permission and department boundaries during implementation
- [x] Implement Slice A
- [x] Verify Slice A positive-path behavior
- [x] Verify Slice A negative-path behavior
- [ ] Implement Slice B
- [ ] Review logs, warnings, and edge cases
- [ ] Document result and open risks in `_docs`

## Progress Log

### Step 1
- Status: completed
- Notes: Reviewed `config/departments.php`, `DepartmentService`, and the current department views. Confirmed that KPI config exists but is not surfaced in department pages, while several department partials still duplicate profile-style guidance.

### Step 2
- Status: completed
- Notes: Slice A scope was kept narrow: expose config-backed summary stats in department index and detail pages without changing department permissions or workflow rules.

### Step 3
- Status: completed
- Notes: Implemented summary stat rendering for `/departments` and `/departments/{slug}`, added `_docs/180...` plus `_docs/181...`, and verified the slice with linting plus the full lightweight test suite.

### Step 4
- Status: pending
- Notes: Implement Slice B, verify it, document it in `_docs`, and commit it as its own unit.

## Verification Plan

- Automated tests:
  - run targeted PHP lint on changed config, service, controller, and view files
  - run the existing lightweight suite if the slice touches behavior already covered by tests
- Manual checks:
  - validate department index cards after the config-driven summary update
  - validate department detail pages for IT, HR, Operations, and one generic department
- Permission checks:
  - confirm non-managers still see read-only department content
  - confirm manager-only actions remain hidden and blocked server-side
- Data integrity checks:
  - confirm config/view changes do not break employee, document, file, or task summary flows
- Error-path checks:
  - confirm missing config data still falls back safely via defaults
  - confirm empty KPI or content lists do not render broken sections

## Verification Evidence

- Planning evidence:
  - reviewed `config/departments.php`
  - reviewed `app/Services/DepartmentService.php`
  - reviewed `resources/views/departments/index.php`
  - reviewed `resources/views/departments/show.php`
  - reviewed representative department partials for IT, HR, Operations, and Marketing
- Implementation evidence:
  - added summary stat rendering to department index and department detail pages
  - added feature note `_docs/180-department-page-config-summary-stats.md`
  - added verification note `_docs/181-department-page-config-summary-stats-verification.md`
  - `php -l app/Services/DepartmentService.php`
  - `php -l app/Controllers/DepartmentController.php`
  - `php -l resources/views/departments/index.php`
  - `php -l resources/views/departments/show.php`
  - `php tests/run.php`
  - pending Slice B

## Result Review

- Outcome: planning updated
- What changed: The active task record now contains a scoped, codebase-backed implementation plan with explicit commit boundaries.
- What did not change: Slice B and deeper partial deduplication have not started yet.
- Risks still open:
  - exact refactor depth for department partials should stay conservative to avoid mixing content cleanup with unrelated UI redesign
  - permission behavior must be rechecked after any department-page rendering change
- Recommended follow-up: Execute Slice A first and keep Slice B separate unless the diff stays tightly scoped.

## Completion Notes

- Definition of done met: no
- Lessons update required: no
- Related lesson entry: Lesson 4, separate each meaningful step into its own docs and commit unit
