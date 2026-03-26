# Active Task Template

## Tomorrow Backlog

- Weekly audit automation next slice:
  - reduce manual copy steps after rendering host automation assets
  - keep systemd timer and `/etc/cron.d` flows aligned with the existing wrapper
  - preserve repo-local rendering as the single source for generated host files
  - keep `_docs` plus per-slice verification workflow

## Task Summary

- Request: Continue the weekly audit automation work with install helper scripts for rendered host assets.
- Business goal: Let ops teams install weekly audit scheduler files into target host paths with fewer manual copy mistakes.
- Current gap summary:
  - committed renderers already generate correct systemd and cron files
  - operators still need to manually copy rendered files into `/etc/systemd/system/` or `/etc/cron.d/`
  - the deployment docs still describe a multi-step render-then-copy flow
- In-scope:
  - add repo-local install helper scripts for systemd and cron targets
  - reuse the existing renderers instead of duplicating placeholder substitution logic
  - document direct install commands and remaining activation steps
  - add lightweight verification around installer outputs and failure paths
- Out-of-scope:
  - automatic privilege escalation
  - invoking `systemctl` from the repo scripts
  - changing weekly report delivery semantics
  - replacing the current renderer scripts
- Deadline or urgency: Continue immediately after the host asset renderer slice.
- Risk level: low

## Assumptions

- Install helpers should still work with non-root target paths so they remain testable and safe in local verification.
- Renderer scripts remain the only place that knows how placeholders are replaced.
- systemd activation should stay an explicit host-side step because not every environment has a live `systemctl` during repo execution.
- Existing step-by-step doc and commit workflow remains mandatory.

## Lead Agent

- Primary agent: Codex
- Supporting agents: none currently assigned
- Relevant skills: none required from AGENTS skill list for this task

## Affected Layers

- Infra scripts:
  - new install helper scripts for systemd and cron targets
- Documentation:
  - `README.md`
  - `infra/DEPLOYMENT-CHECKLIST.md`
  - `_docs`
- Verification:
  - shell syntax checks for new install helpers
  - lightweight tests for copied outputs and usage failures

## Execution Plan

1. Lock the slice on install helpers that build on the existing renderers.
2. Add new scripts for:
   - installing rendered systemd service and timer files into a target directory
   - installing a rendered cron file into a target path
3. Update docs:
   - document direct install usage
   - keep explicit activation steps for systemd
4. Verification and finish:
   - run targeted shell syntax checks
   - run the lightweight suite
   - verify installers copy fully rendered files without unresolved placeholders
   - document the slice in `_docs`

## Commit Plan

1. `docs: define audit host install helper slice plan`
   - update this task record with the scoped implementation plan
2. `feat: add weekly audit host install helpers`
   - implement systemd/cron install helpers and update ops docs
3. `test: verify weekly audit host install helpers`
   - add installer verification coverage and finalize `_docs` verification note

## Checkable Work Items

- [x] Clarify the current behavior and target behavior
- [x] Identify affected scripts, docs, and verification layers
- [x] Add systemd install helper that reuses the renderer output
- [x] Add cron install helper that reuses the renderer output
- [x] Document direct install flow and remaining host activation steps
- [x] Verify copied output behavior
- [x] Review failure paths and cleanup behavior
- [x] Document result and open risks in `_docs`

## Progress Log

### Step 1
- Status: completed
- Notes: Reviewed the renderer slice, deployment docs, and the remaining manual copy gap for host installation.

### Step 2
- Status: completed
- Notes: Added install helper scripts that delegate rendering and then copy systemd or cron assets into explicit target locations.

### Step 3
- Status: completed
- Notes: Updated README and deployment documentation to describe direct install commands while keeping systemd activation explicit.

### Step 4
- Status: completed
- Notes: Added installer-level feature tests, verified temp cleanup behavior, and ran syntax checks plus the full lightweight suite.

## Verification Plan

- Automated checks:
  - run shell syntax checks for install helper scripts
  - run the existing lightweight suite
- Install checks:
  - verify systemd install helper copies rendered `.service` and `.timer` files into the requested directory
  - verify cron install helper copies a rendered cron file into the requested path
- Data integrity checks:
  - confirm installed files still invoke `infra/scripts/send-weekly-audit-report.sh`
  - confirm no unresolved placeholders remain after install
- Error-path checks:
  - confirm missing install targets fail safely with usage output

## Verification Evidence

- Planning evidence:
  - reviewed `infra/scripts/render-weekly-audit-report-systemd.sh`
  - reviewed `infra/scripts/render-weekly-audit-report-cron.sh`
  - reviewed `README.md`
  - reviewed `infra/DEPLOYMENT-CHECKLIST.md`
- Implementation evidence:
  - added `infra/scripts/install-weekly-audit-report-systemd.sh`
  - added `infra/scripts/install-weekly-audit-report-cron.sh`
  - updated `README.md`
  - updated `infra/DEPLOYMENT-CHECKLIST.md`
  - added `_docs/193-weekly-audit-host-install-helpers.md`
  - updated `_docs/194-weekly-audit-host-install-helpers-verification.md`
  - added `tests/Feature/AuditWeeklyReportHostAutomationInstallersTest.php`
  - `php tests/run.php` -> `Executed 69 tests, 0 failed.`

## Result Review

- Outcome: completed
- What changed:
  - install helper scripts now reuse the committed renderers and copy finished systemd or cron assets into operator-chosen target paths
  - README and deployment documentation now include direct install commands alongside the render-only flow
  - installer-level test coverage now verifies copied output, missing-argument failures, file modes, and temp cleanup behavior
- What did not change:
  - the repo still does not attempt privilege escalation or call `systemctl` automatically
  - the weekly audit command and wrapper script semantics remain unchanged
- Risks still open:
  - operators still need to choose host-appropriate schedule values and validate timezone expectations
  - host-specific activation and scheduler reload steps remain manual by design
- Recommended follow-up:
  - add rollout notes only if production hosts need distro-specific scheduler validation beyond the current generic install helpers

## Completion Notes

- Definition of done met: yes
- Lessons update required: no
- Related lesson entry: Lesson 4, separate each meaningful step into its own docs and commit unit
