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
- [x] Add DB-backed throttle storage and service logic
- [x] Enforce throttling in the forgot-password request flow
- [x] Keep the recovery response generic while suppressing excess mail dispatch
- [x] Add focused feature and unit tests
- [x] Run verification commands and capture evidence
- [x] Document result and open risks in `_docs`

## Progress Log

### Step 1
- Status: completed
- Notes: Reviewed the current password reset service, auth controller flow, and forgot-password tests to scope a narrow request-throttling slice.

### Step 2
- Status: completed
- Notes: Added DB-backed forgot-password request throttle storage and a service that tracks normalized email plus IP request windows.

### Step 3
- Status: completed
- Notes: Integrated the throttle into the reset request path so excess requests stop dispatching mails while the UI keeps the generic success response.

### Step 4
- Status: completed
- Notes: Applied the pending migration, verified syntax, checked the guarded fresh-reset refusal path, and re-ran the full suite with 88 passing tests.

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
  - added `database/migrations/025_create_password_reset_request_limits_table.sql`
  - added `app/Services/PasswordResetRequestThrottleService.php`
  - updated `app/Services/PasswordResetService.php`
  - updated `config/auth.php`
  - updated `.env.example`
  - updated `README.md`
  - added `tests/Unit/PasswordResetRequestThrottleServiceTest.php`
  - updated `tests/Feature/ForgotPasswordTest.php`
  - added `_docs/207-forgot-password-request-throttling.md`
  - added `_docs/208-forgot-password-request-throttling-verification.md`
  - `php bin/setup-database.php` -> `Applied migrations: 1`
  - `php -l app/Services/PasswordResetRequestThrottleService.php` -> `No syntax errors detected in app/Services/PasswordResetRequestThrottleService.php`
  - `php -l app/Services/PasswordResetService.php` -> `No syntax errors detected in app/Services/PasswordResetService.php`
  - `php -l tests/Feature/ForgotPasswordTest.php` -> `No syntax errors detected in tests/Feature/ForgotPasswordTest.php`
  - `php -l tests/Unit/PasswordResetRequestThrottleServiceTest.php` -> `No syntax errors detected in tests/Unit/PasswordResetRequestThrottleServiceTest.php`
  - `php bin/setup-database.php --dry-run` -> `Pending migrations: 0`, `Pending seeds: 0`
  - `APP_ENV=local php bin/setup-database.php --fresh` -> `Database setup failed: Refusing fresh database setup outside APP_ENV=testing or CI.`
  - `php tests/run.php` -> `Executed 88 tests, 0 failed.`

## Result Review

- Outcome: completed
- What changed:
  - forgot-password requests now pass through a backend throttle keyed by normalized email and IP
  - repeated requests inside the lock window stop dispatching additional reset mails
  - the guest-facing recovery response remains generic for both throttled and non-throttled requests
- What did not change:
  - MFA remains out of scope
  - the current password reset token flow remains unchanged
- Risks still open:
  - broader IP-level or tenant-level abuse detection is not part of this slice

## Completion Notes

- Definition of done met: yes
- Lessons update required: no
- Related lesson entry: Lesson 1, enforce auth controls on the server side
