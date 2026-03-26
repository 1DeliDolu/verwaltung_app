# Active Task Template

## Tomorrow Backlog

- Authentication hardening next slice:
  - add server-side login rate limiting
  - keep lockout messaging generic and safe
  - preserve the existing login flow and first-login password rotation
  - keep `_docs` plus verification evidence aligned per slice

## Task Summary

- Request: Continue directly with the next priority item after database setup automation.
- Business goal: Reduce brute-force risk by throttling repeated failed logins on the server side.
- Current gap summary:
  - login currently checks credentials without any rate limiting
  - repeated failures can be retried indefinitely from the same email/IP combination
  - auth hardening should start with a backend-enforced control before larger slices like forgot-password or MFA
- In-scope:
  - add DB-backed login throttling for repeated failures
  - wire throttling into the existing login controller flow
  - add config knobs and focused regression coverage
  - document the slice in `.claude` and `_docs`
- Out-of-scope:
  - forgot-password flow
  - MFA or second-factor prompts
  - auth audit UI
  - replacing the current session-based login design
- Deadline or urgency: Continue immediately after the database setup automation slice.
- Risk level: medium

## Assumptions

- Login throttling should stay server-side and must not depend on client-side behavior or hidden form state.
- The safest lock scope for this project is a normalized email plus request IP combination.
- Lockout messaging must remain generic and should not confirm whether a user exists.
- Existing step-by-step doc workflow remains mandatory.

## Lead Agent

- Primary agent: Codex
- Supporting agents: repo-local `security-engineer` guidance reviewed
- Relevant skills: `.claude/skills/authentication-authorization-patterns/SKILL.md`

## Affected Layers

- Schema:
  - login throttle tracking table
- Services:
  - new login throttle service
- Auth flow:
  - login controller handling for pre-check, failure tracking, and success reset
- Configuration:
  - auth throttle settings in config and env example
- Verification:
  - auth feature tests
  - login throttle unit tests
  - syntax checks plus the lightweight PHP suite
- Documentation:
  - `README.md`
  - `.claude/tasks/todo.md`
  - `_docs`

## Execution Plan

1. Lock the slice around server-side login throttling.
2. Add storage and service logic:
   - create a login rate limit table
   - add a throttle service keyed by normalized email and IP
3. Wire the existing login path:
   - reject active lockouts before password verification
   - record failures and clear throttles on success
4. Verification and finish:
   - add focused feature and unit coverage
   - run database setup plus syntax checks and the full suite
   - document the slice in `.claude` and `_docs`

## Commit Plan

1. `docs: define login throttling slice`
   - update this task record with the auth hardening scope
2. `feat: add login rate limiting`
   - add storage, service, config, and controller wiring
3. `test: verify login rate limiting`
   - add auth regression coverage and finalize verification notes

## Checkable Work Items

- [x] Clarify the login throttling gap
- [x] Add DB-backed throttle storage and service logic
- [x] Enforce throttling in the existing login flow
- [x] Add focused feature and unit tests
- [ ] Run verification commands and capture evidence
- [x] Document result and open risks in `_docs`

## Progress Log

### Step 1
- Status: completed
- Notes: Reviewed the auth controller, auth service, request IP handling, CSRF flow, and security guidance to isolate the missing server-side throttle control.

### Step 2
- Status: completed
- Notes: Added DB-backed login throttle storage and service logic with configurable attempt and decay values.

### Step 3
- Status: completed
- Notes: Wired the login controller to reject active lockouts, record failures, and clear throttle state on successful login.

### Step 4
- Status: in progress
- Notes: Database setup, syntax checks, and the expanded auth verification suite remain to be captured for this slice.

## Verification Plan

- Automated checks:
  - run `php bin/setup-database.php`
  - run syntax checks for the new service and updated tests
  - run the existing lightweight suite
- Feature checks:
  - verify invalid credentials still show the generic error
  - verify repeated failures trigger a lockout message
  - verify valid credentials remain blocked during the lockout window
- Unit checks:
  - verify lockout activation at the configured threshold
  - verify expired lockouts clear after the decay window

## Verification Evidence

- Planning evidence:
  - reviewed `app/Controllers/AuthController.php`
  - reviewed `app/Services/AuthService.php`
  - reviewed `app/Core/Request.php`
  - reviewed `app/Middleware/CsrfMiddleware.php`
  - reviewed `tests/Feature/AuthenticationTest.php`
  - reviewed `.claude/agents/security-engineer.md`
  - reviewed `.claude/skills/authentication-authorization-patterns/SKILL.md`
- Implementation evidence:
  - added `database/migrations/023_create_login_rate_limits_table.sql`
  - added `app/Services/LoginThrottleService.php`
  - updated `app/Controllers/AuthController.php`
  - updated `config/auth.php`
  - updated `.env.example`
  - updated `tests/TestCase.php`
  - updated `tests/Feature/AuthenticationTest.php`
  - added `tests/Unit/LoginThrottleServiceTest.php`
  - updated `README.md`
  - added `_docs/203-login-rate-limiting.md`

## Result Review

- Outcome: in progress
- What changed so far:
  - login attempts now pass through a backend throttle service
  - repeated failed attempts can move the email/IP pair into a temporary lockout
  - success clears throttle state for the same email/IP pair
- What did not change:
  - forgot-password and MFA remain out of scope
  - the current session-based login architecture remains unchanged
- Risks still open:
  - lockouts are scoped to email plus IP and do not yet provide a broader network-level abuse view
  - final verification output still needs to be captured

## Completion Notes

- Definition of done met: not yet
- Lessons update required: no
- Related lesson entry: Lesson 1, enforce auth controls on the server side
