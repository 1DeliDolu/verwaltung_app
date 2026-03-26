# Active Task Template

## Tomorrow Backlog

- Weekly audit automation next slice:
  - remove optional host-tool assumptions from renderer scripts
  - harden template substitution against delimiter-sensitive values
  - preserve renderer scripts as the single source of truth for install helpers
  - keep `_docs` plus verification evidence aligned per slice

## Task Summary

- Request: Continue the weekly audit automation work after the install-helper slice.
- Business goal: Keep weekly audit host automation predictable on minimal Linux hosts and avoid broken rendered assets when config values contain special characters.
- Current gap summary:
  - host renderers still assume `rg` is installed for placeholder checks
  - `sed` replacement values are only partially escaped today
  - special characters such as `#` and `&` can break rendered host assets or inject incorrect content
- In-scope:
  - replace optional `rg` checks with ubiquitous host tooling
  - centralize safe `sed` replacement escaping for renderer values
  - add regression coverage for delimiter-sensitive values
  - document the hardening slice in `_docs`
- Out-of-scope:
  - changing weekly audit delivery semantics
  - altering install-helper CLI usage
  - invoking host activation commands automatically
  - expanding beyond the weekly audit automation area
- Deadline or urgency: Continue immediately after the install-helper slice.
- Risk level: low

## Assumptions

- Host-oriented scripts should avoid depending on developer-only utilities when a standard tool is sufficient.
- Install helpers must continue delegating rendering rather than introducing parallel placeholder logic.
- Regression coverage should prove both normal rendering and special-character rendering remain intact.
- Existing step-by-step doc workflow remains mandatory.

## Lead Agent

- Primary agent: Codex
- Supporting agents: repo-local `devops-engineer` and `test-automator` guidance reviewed
- Relevant skills: `.claude/skills/testing-patterns/SKILL.md`

## Affected Layers

- Infra scripts:
  - shared template helper for weekly audit renderers
  - hardened systemd and cron render scripts
- Verification:
  - renderer regression coverage for special-character values
  - shell syntax checks plus the lightweight PHP suite
- Documentation:
  - `.claude/tasks/todo.md`
  - `.claude/tasks/lessons.md`
  - `_docs`

## Execution Plan

1. Lock the hardening slice around renderer portability and safe replacement behavior.
2. Add a shared helper for:
   - escaping `sed` replacement values safely
   - checking unresolved placeholders with `grep`
3. Update renderer tests:
   - cover special characters in admin email and cron log path
   - keep the existing missing-argument checks intact
4. Verification and finish:
   - run targeted shell syntax checks
   - run the lightweight suite
   - record the slice and lesson in `.claude` / `_docs`

## Commit Plan

1. `docs: define audit renderer hardening slice plan`
   - update this task record for the new hardening scope
2. `fix: harden weekly audit host renderers`
   - add shared helper and remove optional host-tool assumptions
3. `test: cover weekly audit renderer special characters`
   - extend renderer coverage and finalize verification notes

## Checkable Work Items

- [x] Clarify the current renderer portability gap
- [x] Add shared template helper logic
- [x] Replace `rg` placeholder checks with standard host tooling
- [x] Harden special-character replacement for rendered values
- [x] Add renderer regression checks for special characters
- [x] Update `.claude` and `_docs` records
- [ ] Run final verification commands and capture evidence

## Progress Log

### Step 1
- Status: completed
- Notes: Reviewed the latest weekly audit automation slices, deployment guidance, and renderer scripts to isolate the remaining portability and escaping gap.

### Step 2
- Status: completed
- Notes: Added a shared template helper, switched placeholder verification to `grep`, and applied safer replacement escaping in both renderers.

### Step 3
- Status: completed
- Notes: Extended renderer regression coverage with special-character cases for admin email and cron log path values.

### Step 4
- Status: in progress
- Notes: Final syntax checks, full test execution, and evidence capture are the remaining actions.

## Verification Plan

- Automated checks:
  - run shell syntax checks for the shared helper and both renderers
  - run `php -l` on the updated renderer test file
  - run the existing lightweight suite
- Rendering checks:
  - verify systemd rendering still produces service and timer files
  - verify cron rendering still produces a placeholder-free cron asset
  - verify `#` and `&` survive substitution in rendered content
- Error-path checks:
  - confirm missing output arguments still fail with usage output

## Verification Evidence

- Planning evidence:
  - reviewed `.claude/CLAUDE.md`
  - reviewed `.claude/tasks/todo.md`
  - reviewed `.claude/tasks/lessons.md`
  - reviewed `.claude/agents/devops-engineer.md`
  - reviewed `.claude/agents/test-automator.md`
  - reviewed `.claude/skills/testing-patterns/SKILL.md`
  - reviewed `infra/scripts/render-weekly-audit-report-systemd.sh`
  - reviewed `infra/scripts/render-weekly-audit-report-cron.sh`
  - reviewed `tests/Feature/AuditWeeklyReportHostAutomationAssetsTest.php`
- Implementation evidence:
  - added `infra/scripts/lib/template-helpers.sh`
  - updated `infra/scripts/render-weekly-audit-report-systemd.sh`
  - updated `infra/scripts/render-weekly-audit-report-cron.sh`
  - updated `tests/Feature/AuditWeeklyReportHostAutomationAssetsTest.php`
  - updated `.claude/tasks/lessons.md`
  - added `_docs/195-weekly-audit-host-renderer-hardening.md`
  - added `_docs/196-weekly-audit-host-renderer-hardening-verification.md`

## Result Review

- Outcome: in progress
- What changed so far:
  - renderer placeholder checks no longer rely on `rg`
  - template replacement now escapes `#` and `&` safely through a shared helper
  - renderer regression coverage now exercises delimiter-sensitive values
- What did not change:
  - install-helper CLI remains unchanged
  - weekly report delivery semantics remain unchanged
  - host activation remains explicit and manual
- Risks still open:
  - final verification still needs to be executed and captured

## Completion Notes

- Definition of done met: not yet
- Lessons update required: yes
- Related lesson entry: Lesson 5, avoid optional tool assumptions and weak template escaping in host automation
