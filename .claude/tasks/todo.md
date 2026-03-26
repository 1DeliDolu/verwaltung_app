# Active Task Template

## Tomorrow Backlog

- Database automation next slice:
  - replace manual migration and seed application with a repo-local runner
  - keep production-safe default behavior non-destructive
  - preserve a fresh reset path for testing and CI
  - keep `_docs` plus verification evidence aligned per slice

## Task Summary

- Request: Continue from the next high-priority gap without waiting for commit confirmation.
- Business goal: Let local and hosted environments prepare schema plus seed state with a single repo-local command instead of manual SQL execution.
- Current gap summary:
  - local setup still told operators to execute migration and seed SQL files manually
  - CI had a test-only reset command, but the app had no general migration/seed runner
  - some seed files are not safe to rerun blindly, so a tracked pending-file runner is required
  - existing manually prepared databases need a safe adoption path instead of replaying legacy SQL files
- In-scope:
  - add a general database setup command for pending migrations and seeds
  - track applied migrations and seeds in dedicated tables
  - keep a fresh reset path for test and CI flows
  - update README, CI, `.claude`, and `_docs`
- Out-of-scope:
  - introducing a framework migration package
  - replacing the ordered SQL file strategy
  - adding rollback/down migrations
  - changing application data models or schema semantics
- Deadline or urgency: Continue immediately after the CI automation slice.
- Risk level: medium

## Assumptions

- Future schema and seed changes should be delivered as new ordered SQL files rather than in-place edits to previously applied files.
- The default setup command must stay non-destructive so it can be used safely for local and production provisioning.
- Fresh resets remain appropriate only for testing and CI-style environments.
- Existing step-by-step doc workflow remains mandatory.

## Lead Agent

- Primary agent: Codex
- Supporting agents: repo-local `devops-engineer` and `test-automator` guidance reused
- Relevant skills: `.claude/skills/testing-patterns/SKILL.md`

## Affected Layers

- CLI tooling:
  - new general database setup command
  - refactored test DB bootstrap wrapper
- Application services:
  - shared database setup service for SQL file application and tracking
- Delivery automation:
  - CI now uses the general setup command for fresh database preparation
- Documentation:
  - `README.md`
  - `.claude/tasks/todo.md`
  - `_docs`
- Verification:
  - unit coverage for new command guard logic
  - syntax checks, safe dry-run/refusal checks, and the lightweight suite

## Execution Plan

1. Lock the slice around replacing manual DB setup with a tracked runner.
2. Add a shared database setup service that:
   - ensures the configured database exists
   - records applied migrations and seeds
   - supports non-destructive setup plus fresh resets
3. Expose the service through CLI:
   - add `bin/setup-database.php`
   - refactor `bin/bootstrap-test-database.php` to reuse the same logic
   - switch CI to the general command
4. Verification and finish:
   - add focused unit tests for guard and mode logic
   - run syntax checks plus safe command verification and the full suite
   - document the slice in `.claude` and `_docs`

## Commit Plan

1. `docs: define database setup automation slice`
   - update this task record with the new scope
2. `feat: add tracked database setup runner`
   - add the service, CLI, and CI/README integration
3. `test: verify database setup automation`
   - add focused regression coverage and finalize verification notes

## Checkable Work Items

- [x] Clarify the manual migration/seed gap
- [x] Add a tracked database setup service and general CLI command
- [x] Keep fresh reset support for CI and testing
- [x] Update CI and README to use the new runner
- [x] Add focused regression coverage for command guard logic
- [ ] Run safe verification commands and capture evidence
- [x] Document result and open risks in `_docs`

## Progress Log

### Step 1
- Status: completed
- Notes: Reviewed the current manual DB instructions, the CI-only bootstrap command, and the migration/seed file behavior to isolate the need for tracking and non-destructive defaults.

### Step 2
- Status: completed
- Notes: Added a shared database setup service and general `bin/setup-database.php` command with tracked migrations, tracked seeds, dry-run support, guarded fresh resets, and legacy database adoption.

### Step 3
- Status: completed
- Notes: Rewired the test bootstrap wrapper and CI workflow to reuse the new runner and updated the README to document local setup usage.

### Step 4
- Status: in progress
- Notes: Focused unit tests are in place; syntax, dry-run, refusal-path, and full-suite verification still need to be captured for this slice.

## Verification Plan

- Automated checks:
  - run `php -l` on the new setup command, refactored test bootstrap, and shared service
  - run the new unit tests through the existing suite
  - run the full lightweight suite
- Command checks:
  - verify `php bin/setup-database.php --dry-run` completes without modifying the current environment
  - verify fresh setup is refused outside testing/CI
- CI review checks:
  - confirm the workflow now uses the new general setup command

## Verification Evidence

- Planning evidence:
  - reviewed `README.md`
  - reviewed `bin/bootstrap-test-database.php`
  - reviewed `.github/workflows/ci.yml`
  - reviewed `database/migrations/*.sql`
  - reviewed `database/seeds/*.sql`
  - reviewed `app/Core/Database.php`
- Implementation evidence:
  - added `app/Services/DatabaseSetupService.php`
  - added `bin/setup-database.php`
  - updated `bin/bootstrap-test-database.php`
  - updated `.github/workflows/ci.yml`
  - updated `README.md`
  - added `tests/Unit/DatabaseSetupServiceTest.php`
  - added `_docs/201-database-setup-runner.md`

## Result Review

- Outcome: in progress
- What changed so far:
  - the project now has a single non-destructive DB setup command for pending migrations and seeds
  - fresh test/CI resets now reuse the same shared setup logic
  - existing manually prepared databases can be adopted into tracking without replaying old SQL files
  - CI and README now point at the same repo-local database preparation path
- What did not change:
  - the project still uses ordered SQL files rather than a framework migration layer
  - rollback/down migration support is still out of scope
- Risks still open:
  - final verification output still needs to be captured in the task record
  - changing an already-applied migration or seed file in place will not rerun it; future data/schema changes should go into new files

## Completion Notes

- Definition of done met: not yet
- Lessons update required: no
- Related lesson entry: Lesson 4, keep slice planning and completion evidence separated cleanly
