# Active Task Template

## Tomorrow Backlog

- Authentication hardening next slice:
  - add backend throttling for forgot-password requests
  - keep the guest-facing reset response generic and safe
  - suppress excess reset mail dispatches inside the lock window
  - keep `_docs` plus verification evidence aligned per slice

## Task Summary

- Request: Continue directly with the next auth hardening priority after forgot-password recovery.
- Business goal: Reduce abuse of the recovery endpoint without changing the guest-facing reset experience.
- Current gap summary:
  - forgot-password links can currently be requested repeatedly without backend throttling
  - excessive reset requests can generate unnecessary outbound mail traffic
  - the recovery flow now needs the same kind of server-side pressure control already added to login
- In-scope:
  - add DB-backed throttling for forgot-password requests
  - apply throttling to the recovery request endpoint while keeping the response generic
  - add config knobs and focused regression coverage
  - document the slice in `.claude` and `_docs`
- Out-of-scope:
  - MFA or second-factor prompts
  - CAPTCHA or external anti-bot providers
  - broader network-level abuse analytics
  - changing the existing reset token flow
- Deadline or urgency: Continue immediately after the forgot-password recovery slice.
- Risk level: medium

## Assumptions

- The forgot-password form must continue to show the same generic success response for existing and unknown accounts.
- The safest initial throttle scope for this project is a normalized email plus request IP combination.
- Throttled requests should not send additional reset mails during the lock window.
- Existing step-by-step doc workflow remains mandatory.

## Lead Agent

- Primary agent: Codex
- Supporting agents: none
- Relevant skills: none

## Affected Layers

- Schema:
  - forgot-password request throttle tracking table
- Services:
  - new forgot-password request throttle service
  - password reset service integration
- Auth flow:
  - forgot-password request handling before reset link dispatch
- Configuration:
  - auth password reset request throttle settings in config and env example
- Verification:
  - forgot-password feature tests
  - forgot-password throttle unit tests
  - syntax checks plus the lightweight PHP suite
- Documentation:
  - `README.md`
  - `.claude/tasks/todo.md`
  - `_docs`

## Execution Plan

1. Lock the slice around forgot-password request throttling.
2. Add storage and service logic:
   - create a request throttle table
   - add throttle state tracking keyed by normalized email and IP
3. Wire the recovery request path:
   - suppress excess reset link dispatches inside the throttle window
   - keep the guest-facing response generic
4. Verification and finish:
   - add focused feature and unit coverage
   - run database setup, syntax checks, and the full suite
   - document the slice in `.claude` and `_docs`

## Commit Plan

1. `docs: define forgot password throttling slice`
   - update this task record with the new auth hardening scope
2. `feat: throttle forgot password requests`
   - add storage, service, config, password reset integration, and docs
3. `test: verify forgot password request throttling`
   - add regression coverage and finalize verification notes

## Checkable Work Items

- [x] Clarify the forgot-password throttling gap
- [ ] Add DB-backed throttle storage and service logic
- [ ] Enforce throttling in the forgot-password request flow
- [ ] Keep the recovery response generic while suppressing excess mail dispatch
- [ ] Add focused feature and unit tests
- [ ] Run verification commands and capture evidence
- [ ] Document result and open risks in `_docs`

## Progress Log

### Step 1
- Status: completed
- Notes: Reviewed the current password reset service, auth controller flow, and forgot-password tests to scope a narrow request-throttling slice.

### Step 2
- Status: pending
- Notes: Add forgot-password request throttle storage and service logic.

### Step 3
- Status: pending
- Notes: Wire throttling into the reset request path while keeping the guest response generic.

### Step 4
- Status: pending
- Notes: Add coverage, run verification commands, and capture final evidence.

## Verification Plan

- Automated checks:
  - run `php bin/setup-database.php`
  - run syntax checks for the new service and updated tests
  - run the existing lightweight suite
- Feature checks:
  - verify repeated forgot-password requests from the same email and IP stop dispatching extra mails
  - verify throttled requests still return the generic success response
  - verify unknown emails still keep the generic success response
- Unit checks:
  - verify the throttle locks after the configured threshold
  - verify an expired forgot-password request lock clears after the decay window

## Verification Evidence

- Planning evidence:
  - reviewed `app/Services/PasswordResetService.php`
  - reviewed `app/Controllers/AuthController.php`
  - reviewed `config/auth.php`
  - reviewed `.env.example`
  - reviewed `tests/Feature/ForgotPasswordTest.php`
  - reviewed `tests/Unit/PasswordResetServiceTest.php`
- Implementation evidence:
  - pending

## Result Review

- Outcome: in progress
- What changed so far:
  - forgot-password request throttling scope is documented and constrained
- What did not change:
  - MFA remains out of scope
  - the current password reset token flow remains unchanged
- Risks still open:
  - broader IP-level or tenant-level abuse detection is not part of this slice

## Completion Notes

- Definition of done met: not yet
- Lessons update required: no
- Related lesson entry: pending
