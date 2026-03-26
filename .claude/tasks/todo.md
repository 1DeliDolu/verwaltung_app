# Active Task Template

## Tomorrow Backlog

- Authentication hardening next slice:
  - add backend throttling for admin login challenge verification attempts
  - keep the challenge UI generic while blocking brute-force retries
  - reset the attempt state on successful code verification
  - keep `_docs` plus verification evidence aligned per slice

## Task Summary

- Request: Continue directly with the next auth hardening priority after the admin email challenge slice.
- Business goal: Reduce brute-force risk on the emailed login code step without changing the overall MFA shape.
- Current gap summary:
  - the admin email challenge can currently be retried indefinitely
  - failed challenge verification has no backend pressure control
  - the code step now needs the same style of server-side throttling already applied to login and forgot-password
- In-scope:
  - add DB-backed throttling for admin login challenge verification
  - enforce throttling on the challenge verification path
  - clear attempt state after a successful challenge
  - add config knobs and focused regression coverage
  - document the slice in `.claude` and `_docs`
- Out-of-scope:
  - recovery codes
  - broader MFA rollout to all roles
  - challenge resend UX
  - broader network-level abuse analytics
- Deadline or urgency: Continue immediately after the admin email challenge slice.
- Risk level: medium

## Assumptions

- The challenge verification lock scope should be tied to the pending challenge and request IP.
- Failed code attempts should lock the challenge step temporarily after a small threshold.
- Successful code verification should clear the associated attempt state.
- Existing step-by-step doc workflow remains mandatory.

## Lead Agent

- Primary agent: Codex
- Supporting agents: none
- Relevant skills: none

## Affected Layers

- Schema:
  - login challenge attempt throttle tracking table
- Services:
  - new login challenge throttle service
  - email login challenge service integration
- Auth flow:
  - challenge verification path
- Configuration:
  - auth email challenge throttle settings in config and env example
- Verification:
  - auth feature tests
  - login challenge throttle unit tests
  - syntax checks plus the lightweight PHP suite
- Documentation:
  - `README.md`
  - `.claude/tasks/todo.md`
  - `_docs`

## Execution Plan

1. Lock the slice around admin login challenge verification throttling.
2. Add storage and service logic:
   - create a challenge attempt throttle table
   - add throttle tracking keyed by challenge and IP
3. Wire the auth surface:
   - reject verification attempts when the challenge step is locked
   - clear attempt state after successful code verification
4. Verification and finish:
   - add focused feature and unit coverage
   - run database setup, syntax checks, and the full suite
   - document the slice in `.claude` and `_docs`

## Commit Plan

1. `docs: define login challenge throttling slice`
   - update this task record with the auth hardening scope
2. `feat: throttle login challenge verification`
   - add storage, service, auth flow updates, config, and docs
3. `test: verify login challenge throttling`
   - add auth regression coverage and finalize verification notes

## Checkable Work Items

- [x] Clarify the login challenge throttling gap
- [ ] Add DB-backed challenge throttle storage and service logic
- [ ] Enforce throttling in the challenge verification flow
- [ ] Clear attempt state after successful verification
- [ ] Add focused feature and unit tests
- [ ] Run verification commands and capture evidence
- [ ] Document result and open risks in `_docs`

## Progress Log

### Step 1
- Status: completed
- Notes: Reviewed the current email login challenge service, challenge verification path, config, and tests to scope a narrow brute-force hardening slice.

### Step 2
- Status: pending
- Notes: Add challenge verification throttle storage and service logic.

### Step 3
- Status: pending
- Notes: Wire challenge verification through the throttle before successful session creation.

### Step 4
- Status: pending
- Notes: Add coverage, run verification commands, and capture final evidence.

## Verification Plan

- Automated checks:
  - run `php bin/setup-database.php`
  - run syntax checks for the new service and updated tests
  - run the existing lightweight suite
- Feature checks:
  - verify repeated wrong codes trigger a challenge lock
  - verify a correct code stays blocked during the active lock window
  - verify a successful code clears the attempt state before login completion
- Unit checks:
  - verify the challenge throttle locks after the configured threshold
  - verify an expired challenge lock clears after the decay window

## Verification Evidence

- Planning evidence:
  - reviewed `app/Services/EmailLoginChallengeService.php`
  - reviewed `app/Controllers/AuthController.php`
  - reviewed `config/auth.php`
  - reviewed `tests/Feature/AuthenticationTest.php`
  - reviewed `tests/Unit/EmailLoginChallengeServiceTest.php`
- Implementation evidence:
  - pending

## Result Review

- Outcome: in progress
- What changed so far:
  - login challenge throttling scope is documented and constrained
- What did not change:
  - the overall admin email challenge shape remains unchanged
  - forgot-password flow remains unchanged
- Risks still open:
  - challenge resend and recovery UX are not part of this slice

## Completion Notes

- Definition of done met: not yet
- Lessons update required: no
- Related lesson entry: pending
