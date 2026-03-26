# Active Task Template

## Tomorrow Backlog

- Weekly audit automation next slice:
  - make host PHP binary selection explicit in rendered scheduler assets
  - keep `infra/scripts/send-weekly-audit-report.sh` as the single runtime entrypoint
  - preserve backward compatibility for hosts that still rely on plain `php`
  - keep `_docs` plus verification evidence aligned per slice

## Task Summary

- Request: Continue the weekly audit automation work after renderer hardening.
- Business goal: Let ops teams pin the PHP binary used by weekly audit scheduler assets on hosts where `php` from `PATH` is not the desired executable.
- Current gap summary:
  - the weekly audit wrapper already supports `PHP_BIN`
  - rendered systemd and cron assets did not expose that host-level choice
  - install helpers therefore also could not persist an explicit PHP binary into installed assets
- In-scope:
  - add optional `PHP_BIN` support to rendered systemd and cron assets
  - carry the same optional parameter through install helpers
  - document host-facing usage with explicit PHP binary examples
  - add regression coverage for render and install flows
- Out-of-scope:
  - changing wrapper execution semantics
  - auto-detecting PHP versions on the host
  - changing weekly report delivery logic
  - invoking host activation commands automatically
- Deadline or urgency: Continue immediately after the renderer hardening slice.
- Risk level: low

## Assumptions

- The default host behavior must stay backward compatible and continue to fall back to `php`.
- The new `PHP_BIN` value is intended for path-like tokens such as `php` or `/usr/bin/php8.2`.
- Install helpers should continue delegating to renderers instead of duplicating asset-generation logic.
- Existing step-by-step doc workflow remains mandatory.

## Lead Agent

- Primary agent: Codex
- Supporting agents: repo-local `devops-engineer` and `test-automator` guidance reused
- Relevant skills: `.claude/skills/testing-patterns/SKILL.md`

## Affected Layers

- Infra templates:
  - `infra/examples/weekly-audit-report.service.example`
  - `infra/examples/weekly-audit-report.cron.example`
- Infra scripts:
  - render and install helpers for weekly audit host assets
- Documentation:
  - `README.md`
  - `infra/DEPLOYMENT-CHECKLIST.md`
  - `.claude/tasks/todo.md`
  - `_docs`
- Verification:
  - host asset renderer coverage
  - install-helper coverage
  - shell syntax checks plus the lightweight PHP suite

## Execution Plan

1. Lock the slice around explicit host PHP binary overrides for scheduler assets.
2. Extend templates and scripts:
   - render `PHP_BIN` into systemd and cron assets
   - accept the same optional parameter in install helpers
3. Update tests and docs:
   - cover renderer and installer propagation of the custom PHP binary
   - document host-facing usage examples
4. Verification and finish:
   - run targeted shell syntax checks
   - run the updated feature tests and full suite
   - record the slice in `_docs`

## Commit Plan

1. `docs: define audit host php binary override slice`
   - update this task record with the new scope
2. `feat: support php binary overrides for audit host assets`
   - extend templates and scripts
3. `test: verify audit host php binary overrides`
   - cover propagation and finalize verification notes

## Checkable Work Items

- [x] Clarify the host PHP binary configuration gap
- [x] Add `PHP_BIN` placeholders to rendered scheduler assets
- [x] Carry optional `PHP_BIN` support through install helpers
- [x] Update ops documentation with explicit host examples
- [x] Add regression checks for render and install propagation
- [x] Run final verification commands and capture evidence
- [x] Document result and open risks in `_docs`

## Progress Log

### Step 1
- Status: completed
- Notes: Reviewed templates, wrapper behavior, install helpers, and ops docs to isolate the missing host PHP binary path.

### Step 2
- Status: completed
- Notes: Added `PHP_BIN` rendering to systemd and cron assets and carried the optional argument through both install helpers while preserving the `php` fallback.

### Step 3
- Status: completed
- Notes: Updated README and deployment guidance and extended renderer/installer tests to verify both default and explicit PHP binary propagation.

### Step 4
- Status: completed
- Notes: Ran shell syntax checks, `php -l`, and the full lightweight suite; all checks passed with 72 tests green.

## Verification Plan

- Automated checks:
  - run shell syntax checks for updated render and install scripts
  - run `php -l` on both updated feature test files
  - run the existing lightweight suite
- Rendering checks:
  - verify default rendered assets still include `PHP_BIN=php`
  - verify explicit `/usr/bin/php8.2` overrides are rendered into systemd and cron assets
- Install checks:
  - verify install helpers copy assets that preserve explicit PHP binary overrides
- Error-path checks:
  - confirm missing target arguments still fail with usage output

## Verification Evidence

- Planning evidence:
  - reviewed `infra/examples/weekly-audit-report.service.example`
  - reviewed `infra/examples/weekly-audit-report.cron.example`
  - reviewed `infra/scripts/send-weekly-audit-report.sh`
  - reviewed `infra/scripts/install-weekly-audit-report-systemd.sh`
  - reviewed `infra/scripts/install-weekly-audit-report-cron.sh`
  - reviewed `tests/Feature/AuditWeeklyReportHostAutomationAssetsTest.php`
  - reviewed `tests/Feature/AuditWeeklyReportHostAutomationInstallersTest.php`
  - reviewed `README.md`
  - reviewed `infra/DEPLOYMENT-CHECKLIST.md`
- Implementation evidence:
  - updated `infra/examples/weekly-audit-report.service.example`
  - updated `infra/examples/weekly-audit-report.cron.example`
  - updated `infra/scripts/render-weekly-audit-report-systemd.sh`
  - updated `infra/scripts/render-weekly-audit-report-cron.sh`
  - updated `infra/scripts/install-weekly-audit-report-systemd.sh`
  - updated `infra/scripts/install-weekly-audit-report-cron.sh`
  - updated `tests/Feature/AuditWeeklyReportHostAutomationAssetsTest.php`
  - updated `tests/Feature/AuditWeeklyReportHostAutomationInstallersTest.php`
  - updated `README.md`
  - updated `infra/DEPLOYMENT-CHECKLIST.md`
  - added `_docs/197-weekly-audit-host-php-bin-overrides.md`
  - added `_docs/198-weekly-audit-host-php-bin-overrides-verification.md`
  - `bash -n infra/scripts/render-weekly-audit-report-systemd.sh` -> passed
  - `bash -n infra/scripts/render-weekly-audit-report-cron.sh` -> passed
  - `bash -n infra/scripts/install-weekly-audit-report-systemd.sh` -> passed
  - `bash -n infra/scripts/install-weekly-audit-report-cron.sh` -> passed
  - `php -l tests/Feature/AuditWeeklyReportHostAutomationAssetsTest.php` -> `No syntax errors detected in tests/Feature/AuditWeeklyReportHostAutomationAssetsTest.php`
  - `php -l tests/Feature/AuditWeeklyReportHostAutomationInstallersTest.php` -> `No syntax errors detected in tests/Feature/AuditWeeklyReportHostAutomationInstallersTest.php`
  - `php tests/run.php` -> `Executed 72 tests, 0 failed.`

## Result Review

- Outcome: completed
- What changed:
  - rendered systemd and cron assets now make the host PHP binary explicit
  - install helpers can now persist a custom PHP binary into installed assets
  - ops docs now show when and how to append `/usr/bin/php8.2` as a host override
- What did not change:
  - `infra/scripts/send-weekly-audit-report.sh` remains the execution wrapper
  - the default fallback stays `php`
  - weekly report delivery semantics and host activation flow remain unchanged
- Risks still open:
  - host operators still need to choose the correct PHP binary for their distro and runtime layout

## Completion Notes

- Definition of done met: yes
- Lessons update required: no
- Related lesson entry: Lesson 5, avoid hidden host assumptions in automation assets
