# Active Task Template

## Tomorrow Backlog

- Audit dashboard next slice:
  - add ready-to-install host automation assets after the CLI command
  - keep `_docs` plus per-slice commit workflow
  - support both systemd timer and `/etc/cron.d` style installation paths
  - reuse the existing wrapper script instead of embedding report logic again


## Task Summary

- Request: Continue by preparing host-side installation assets for the weekly audit report automation.
- Business goal: Let ops teams install the weekly report on a real host with minimal manual editing.
- Current gap summary:
  - the weekly report already has a CLI entrypoint and cron-friendly wrapper
  - the docs describe example cron usage
  - there are still no committed installable host assets for systemd or `/etc/cron.d`
  - operators would still need to handcraft unit or cron files
- In-scope:
  - add committed example assets for systemd service/timer and host cron
  - add render scripts that replace placeholders with host-specific values
  - document install and activation flow
  - add lightweight verification around the generated outputs
- Out-of-scope:
  - automatic root-level installation on the host
  - adding a scheduler framework into the app
  - changing report content or command semantics
  - replacing the existing CLI command or wrapper
- Deadline or urgency: Execute as the next isolated ops slice for weekly audit reporting.
- Risk level: low

## Assumptions

- Operators need both systemd and cron options because deployment targets may differ.
- Renderer scripts are safer than shipping hardcoded absolute paths inside committed unit files.
- The existing `infra/scripts/send-weekly-audit-report.sh` wrapper should stay the single command invoked by host schedulers.
- Existing step-by-step doc and commit workflow remains mandatory.

## Lead Agent

- Primary agent: Codex
- Supporting agents: none currently assigned
- Relevant skills: none required from AGENTS skill list for this task

## Affected Layers

- Infra examples:
  - new `.example` assets for systemd service, timer, and cron
- Infra scripts:
  - renderer scripts for systemd and cron outputs
- Documentation:
  - `README.md`
  - `infra/DEPLOYMENT-CHECKLIST.md`
  - `_docs`
- Verification:
  - shell syntax checks for renderer scripts
  - lightweight tests for rendered output

## Execution Plan

1. Lock the slice on renderable host automation assets and exclude root-install automation.
2. Add committed example templates for:
   - systemd service
   - systemd timer
   - `/etc/cron.d` entry
3. Add renderer scripts and docs:
   - substitute app root, user, group, admin email, and schedule values
   - explain installation for systemd and cron
4. Verification and finish:
   - run targeted shell syntax checks
   - run the existing lightweight suite
   - verify rendered outputs replace placeholders correctly
   - document the slice in `_docs`

## Commit Plan

1. `docs: define audit host automation asset slice plan`
   - update this task record with the scoped implementation plan
2. `feat: add weekly audit host automation assets`
   - implement systemd/cron templates, renderers, and install docs
3. `test: verify weekly audit host automation assets`
   - add rendered-output verification coverage and finalize `_docs` verification note

## Checkable Work Items

- [x] Clarify the current behavior and target behavior
- [x] Identify affected infra, scripts, docs, and verification layers
- [x] Choose renderable host assets over hardcoded install automation
- [x] Add example systemd service and timer assets
- [x] Add example host cron asset
- [x] Add renderer scripts for systemd and cron outputs
- [x] Verify rendered output behavior
- [x] Review logs, warnings, and edge cases
- [x] Document result and open risks in `_docs`

## Progress Log

### Step 1
- Status: completed
- Notes: Reviewed the current CLI command, wrapper script, deployment docs, and existing `infra/examples` structure.

### Step 2
- Status: completed
- Notes: Chose renderable example assets so host-specific paths and users can be filled in without committing unsafe absolute values.

### Step 3
- Status: completed
- Notes: Added renderable systemd and cron templates, renderer scripts, and updated README plus deployment documentation for host installation flow.

### Step 4
- Status: completed
- Notes: Added renderer-level feature tests, ran shell syntax checks plus the lightweight suite, and documented the verification outcome in `_docs`.

## Verification Plan

- Automated checks:
  - run shell syntax checks for renderer scripts
  - run the existing lightweight suite
- Render checks:
  - verify systemd render output replaces app root, user, group, schedule, and admin email placeholders
  - verify cron render output replaces schedule, app root, and user placeholders
- Data integrity checks:
  - confirm rendered assets still invoke `infra/scripts/send-weekly-audit-report.sh`
  - confirm systemd and cron examples stay aligned with the same wrapper command
- Error-path checks:
  - confirm missing required output paths fail safely
  - confirm renderer scripts avoid leaving unresolved placeholders behind

## Verification Evidence

- Planning evidence:
  - reviewed `infra/examples`
  - reviewed `README.md`
  - reviewed `infra/DEPLOYMENT-CHECKLIST.md`
  - reviewed `infra/scripts/send-weekly-audit-report.sh`
- Implementation evidence:
  - added `infra/examples/weekly-audit-report.service.example`
  - added `infra/examples/weekly-audit-report.timer.example`
  - added `infra/examples/weekly-audit-report.cron.example`
  - added `infra/scripts/render-weekly-audit-report-systemd.sh`
  - added `infra/scripts/render-weekly-audit-report-cron.sh`
  - updated `README.md`
  - updated `infra/DEPLOYMENT-CHECKLIST.md`
  - added `_docs/191-weekly-audit-host-automation-assets.md`
  - added `_docs/192-weekly-audit-host-automation-assets-verification.md`
  - added `tests/Feature/AuditWeeklyReportHostAutomationAssetsTest.php`
  - `php tests/run.php` -> `Executed 66 tests, 0 failed.`

## Result Review

- Outcome: completed
- What changed:
  - renderable systemd service/timer and `/etc/cron.d` style cron assets now exist under `infra/examples`
  - renderer scripts now generate host-specific files without leaving placeholders behind
  - README and deployment docs now describe an explicit install flow for both scheduling options
- What did not change:
  - the repo still does not perform privileged host installation automatically
  - the weekly report command and wrapper semantics remain unchanged
- Risks still open:
  - operators still need to own the final copy into `/etc/systemd/system/` or `/etc/cron.d/`
  - schedule values should be reviewed per host timezone and reporting expectation
- Recommended follow-up: only add privileged installer scripts if host rollout repeatedly needs one-step root automation.

## Completion Notes

- Definition of done met: yes
- Lessons update required: no
- Related lesson entry: Lesson 4, separate each meaningful step into its own docs and commit unit
