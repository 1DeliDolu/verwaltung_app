# Active Task Template

## Tomorrow Backlog

- Delivery automation next slice:
  - add CI so the lightweight PHP suite runs on every push and pull request
  - keep test database setup repo-local instead of hidden inside workflow-only shell steps
  - preserve the existing lightweight test harness as the single suite entrypoint
  - keep `_docs` plus verification evidence aligned per slice

## Task Summary

- Request: Start with the highest-priority missing item and continue from CI automation.
- Business goal: Catch regressions automatically by running `php tests/run.php` on each push and pull request in a clean environment.
- Current gap summary:
  - the project has a reliable local test suite but no hosted CI pipeline
  - the suite requires MySQL plus seeded schema state
  - there was no repo-local command to bootstrap that database deterministically for CI
- In-scope:
  - add a GitHub Actions workflow for push and pull request test runs
  - add a repo-local command that recreates and seeds the test database
  - document the local and CI usage path
  - record the slice in `.claude` and `_docs`
- Out-of-scope:
  - replacing the lightweight test harness with PHPUnit
  - adding deployment, release, or rollback automation
  - introducing dependency management tooling
  - executing the hosted GitHub workflow from inside this workspace
- Deadline or urgency: Continue immediately after the audit automation slices.
- Risk level: medium

## Assumptions

- GitHub Actions is the intended hosted CI target because the repository currently has no pipeline files.
- The safest durable CI path is a repo-local DB bootstrap command instead of embedding long SQL loops directly in workflow YAML.
- The bootstrap command must guard against accidental destructive use outside `APP_ENV=testing` or CI.
- Existing step-by-step doc workflow remains mandatory.

## Lead Agent

- Primary agent: Codex
- Supporting agents: repo-local `devops-engineer` and `test-automator` guidance reused
- Relevant skills: `.claude/skills/testing-patterns/SKILL.md`

## Affected Layers

- Delivery automation:
  - new `.github/workflows/ci.yml`
- CLI tooling:
  - repo-local test database bootstrap command
- Documentation:
  - `README.md`
  - `.claude/tasks/todo.md`
  - `_docs`
- Verification:
  - syntax checks for the new CLI command
  - existing lightweight PHP suite

## Execution Plan

1. Lock the slice around hosted CI for the existing test suite.
2. Add a repo-local DB bootstrap command that:
   - recreates the configured test database
   - applies ordered migrations and seeds
   - refuses destructive execution outside testing/CI
3. Add GitHub Actions:
   - provision PHP 8.2 and MySQL
   - call the bootstrap command
   - run `php tests/run.php`
4. Verification and finish:
   - run syntax checks for the new CLI command
   - run the local suite against the current environment
   - document the slice in `.claude` and `_docs`

## Commit Plan

1. `docs: define ci automation slice`
   - update this task record with the CI scope
2. `feat: add github actions php test pipeline`
   - add the workflow and repo-local DB bootstrap command
3. `test: document ci verification flow`
   - update docs and finalize verification notes

## Checkable Work Items

- [x] Clarify the CI and database bootstrap gap
- [x] Add a repo-local test database bootstrap command
- [x] Add a GitHub Actions workflow for push and pull request test runs
- [x] Update README with local and hosted CI usage
- [x] Run safe local verification commands and capture evidence
- [x] Document result and open risks in `_docs`

## Progress Log

### Step 1
- Status: completed
- Notes: Reviewed the current test harness, bootstrap code, env loading, and database requirements to isolate the missing CI setup path.

### Step 2
- Status: completed
- Notes: Added a repo-local test DB bootstrap CLI that recreates schema and seed state while refusing destructive use outside testing or CI.

### Step 3
- Status: completed
- Notes: Added a GitHub Actions workflow for push and pull request runs and documented the local bootstrap plus CI usage path in the README.

### Step 4
- Status: completed
- Notes: Verified the new CLI syntax, confirmed the destructive guard rejects non-testing execution, and re-ran the full suite successfully.

## Verification Plan

- Automated checks:
  - run `php -l` on the new DB bootstrap command
  - run the existing lightweight suite
- CI review checks:
  - inspect the workflow for PHP 8.2 setup, MySQL service boot, DB bootstrap, and suite execution
- Safety checks:
  - confirm the bootstrap command refuses destructive execution outside testing or CI

## Verification Evidence

- Planning evidence:
  - reviewed `tests/run.php`
  - reviewed `tests/bootstrap.php`
  - reviewed `tests/TestCase.php`
  - reviewed `bootstrap/app.php`
  - reviewed `bootstrap/console.php`
  - reviewed `config/database.php`
  - reviewed `.env.example`
  - reviewed `app/Core/Database.php`
- Implementation evidence:
  - added `bin/bootstrap-test-database.php`
  - added `.github/workflows/ci.yml`
  - updated `README.md`
  - added `_docs/199-github-actions-ci-for-php-tests.md`
  - added `_docs/200-github-actions-ci-for-php-tests-verification.md`
  - `php -l bin/bootstrap-test-database.php` -> `No syntax errors detected in bin/bootstrap-test-database.php`
  - `APP_ENV=local php bin/bootstrap-test-database.php` -> `Refusing to reset the database outside APP_ENV=testing or CI.`
  - `php tests/run.php` -> `Executed 72 tests, 0 failed.`

## Result Review

- Outcome: completed
- What changed:
  - the repo now contains a hosted CI workflow definition
  - test DB bootstrap moved into a repo-local CLI command instead of workflow-only shell loops
  - README now documents how to reuse the same bootstrap path locally
- What did not change:
  - the test harness remains the existing lightweight `php tests/run.php` flow
  - deployment automation is still out of scope
- Risks still open:
  - hosted GitHub Actions execution cannot be observed from this local workspace

## Completion Notes

- Definition of done met: yes
- Lessons update required: no
- Related lesson entry: Lesson 4, keep slice planning and completion evidence separated cleanly
