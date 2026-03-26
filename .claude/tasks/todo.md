# Active Task Template

## Tomorrow Backlog

- Audit dashboard next slice:
  - automate the weekly audit email report after manual send support
  - keep `_docs` plus per-slice commit workflow
  - reuse the existing weekly report service rather than fork report logic
  - prefer cron-friendly CLI execution over another web-only trigger


## Task Summary

- Request: Continue the audit dashboard backlog with cron/CLI automation for the weekly audit report.
- Business goal: Let operations schedule the existing weekly audit report without relying on a manual admin click in `/audit`.
- Current gap summary:
  - `/audit` can now send the weekly report manually
  - the report service, templates, and CSV attachment already exist
  - there is still no cron-safe command entrypoint for unattended weekly delivery
  - deployment docs do not yet describe how to schedule the report
- In-scope:
  - add a CLI command for the weekly audit report
  - add a cron-friendly wrapper script
  - allow explicit admin identity and deterministic runtime options for operations and tests
  - document example cron usage and operational behavior
- Out-of-scope:
  - queue workers or background job infrastructure
  - changes to report content itself
  - non-admin report sending
  - replacing the existing manual dashboard trigger
- Deadline or urgency: Execute as the next isolated audit-dashboard slice.
- Risk level: low to medium

## Assumptions

- The existing `AuditWeeklyReportService` should remain the single source of truth for report composition and delivery.
- A CLI entrypoint plus wrapper script is sufficient for cron integration in this repo.
- The automation path should support explicit overrides such as admin email, timestamp, and capture path so verification remains deterministic.
- Existing step-by-step doc and commit workflow remains mandatory.

## Lead Agent

- Primary agent: Codex
- Supporting agents: none currently assigned
- Relevant skills: none required from AGENTS skill list for this task

## Affected Layers

- Bootstrap / runtime:
  - shared CLI bootstrap if needed
  - new command entrypoint under `bin/`
- Service layer:
  - weekly report service option overrides if needed
  - command runner service if useful
- Infra / ops:
  - cron-friendly wrapper script under `infra/scripts/`
  - deployment documentation and cron example
- Documentation:
  - `_docs`
  - `README.md`
  - `infra/DEPLOYMENT-CHECKLIST.md`
- Verification:
  - feature tests for CLI dry-run and send execution
  - targeted linting and existing lightweight suite

## Execution Plan

1. Lock the slice on CLI/cron automation and keep the manual dashboard trigger intact.
2. Add a CLI command that:
   - boots the app safely
   - resolves an admin context
   - supports dry-run and explicit overrides for automation/testing
   - reuses `AuditWeeklyReportService`
3. Add a cron-friendly wrapper plus operator docs:
   - stable shell entrypoint
   - example cron line
   - deployment notes for logs and environment expectations
4. Verification and finish:
   - run targeted PHP lint and shell syntax checks
   - run the existing lightweight suite
   - verify dry-run and real send behavior through command execution
   - document the slice in `_docs`

## Commit Plan

1. `docs: define audit report automation slice plan`
   - update this task record with the scoped implementation plan
2. `feat: add cron-friendly weekly audit report command`
   - implement the CLI entrypoint, wrapper, and automation docs
3. `test: verify weekly audit report automation`
   - add command-level verification coverage and finalize `_docs` verification note

## Checkable Work Items

- [x] Clarify the current behavior and target behavior
- [x] Identify affected bootstrap, service, infra, and documentation layers
- [x] Choose CLI/cron automation over another web-only trigger
- [x] Implement the weekly audit report CLI command
- [x] Add a cron-friendly wrapper script
- [x] Verify dry-run and real send behavior
- [x] Review logs, warnings, and edge cases
- [x] Document result and open risks in `_docs`

## Progress Log

### Step 1
- Status: completed
- Notes: Reviewed the current manual weekly report flow, existing bootstrap path, and infrastructure scripts already used for operational entrypoints.

### Step 2
- Status: completed
- Notes: Chose a CLI-first automation slice because it integrates cleanly with host cron and reuses the already finished report service.

### Step 3
- Status: completed
- Notes: Added `bootstrap/console.php`, the weekly report CLI command, a cron-friendly shell wrapper, and option overrides for admin email, recipients, timestamp, and capture path.

### Step 4
- Status: completed
- Notes: Added command-level feature coverage, ran lint plus shell syntax checks and the full lightweight suite, and documented the result in `_docs`.

## Verification Plan

- Automated tests:
  - run targeted PHP lint on new bootstrap, command, service, and docs-related PHP files
  - run shell syntax checks for the wrapper script
  - run the existing lightweight suite
- Command checks:
  - verify `--dry-run` reports the expected admin, recipients, and window without sending mail
  - verify the real command path sends or captures the weekly report successfully
- Data integrity checks:
  - confirm CLI overrides feed the same weekly report window, recipients, and capture path
  - confirm the command still uses the shared weekly report service
- Error-path checks:
  - confirm a non-admin or missing admin email fails safely with a non-zero exit
  - confirm missing recipients or delivery failures surface clear CLI errors

## Verification Evidence

- Planning evidence:
  - reviewed `bootstrap/app.php`
  - reviewed `public/index.php`
  - reviewed `app/Services/AuditWeeklyReportService.php`
  - reviewed `infra/scripts/*` operational script style
  - reviewed `README.md` and `infra/DEPLOYMENT-CHECKLIST.md`
- Implementation evidence:
  - added `bootstrap/console.php`
  - added `app/Services/AuditWeeklyReportCommandService.php`
  - updated `app/Services/AuditWeeklyReportService.php`
  - updated `app/Services/MailService.php`
  - added `bin/send-weekly-audit-report.php`
  - added `infra/scripts/send-weekly-audit-report.sh`
  - updated `config/mail.php`
  - updated `.env.example`
  - updated `README.md`
  - updated `infra/DEPLOYMENT-CHECKLIST.md`
  - added `_docs/189-weekly-audit-report-automation.md`
  - added `_docs/190-weekly-audit-report-automation-verification.md`
  - added `tests/Feature/AuditWeeklyReportAutomationTest.php`
  - `php tests/run.php` -> `Executed 63 tests, 0 failed.`

## Result Review

- Outcome: completed
- What changed:
  - weekly audit reporting now has a dedicated CLI entrypoint and cron-friendly wrapper
  - the command supports dry-run plus explicit admin, recipient, timestamp, and capture-path overrides
  - deployment and README docs now include operator guidance and a cron example
- What did not change:
  - the manual `/audit` send action remains intact
  - there is still no queue or scheduler framework inside the app itself
- Risks still open:
  - future automation beyond cron should keep reusing the same command/service path
  - host-level cron logging and alerting still need operational ownership outside the app
- Recommended follow-up: only add queueing or scheduler infrastructure if delivery orchestration becomes more complex than a single weekly cron.

## Completion Notes

- Definition of done met: yes
- Lessons update required: no
- Related lesson entry: Lesson 4, separate each meaningful step into its own docs and commit unit
