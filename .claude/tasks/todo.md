# Active Task Template

## Tomorrow Backlog

- Authentication hardening next slice:
  - add a guest-facing forgot-password request and reset flow
  - keep request responses generic to avoid account enumeration
  - use single-use expiring reset tokens with server-side validation
  - keep `_docs` plus verification evidence aligned per slice

## Task Summary

- Request: Continue directly with the next auth hardening priority after login rate limiting.
- Business goal: Let legitimate users recover access without admin intervention while keeping the reset surface narrow and safe.
- Current gap summary:
  - there is no guest-facing forgot-password flow
  - password resets currently depend on admin-triggered reset actions
  - the project needs a time-bound reset path before larger auth slices like MFA
- In-scope:
  - add DB-backed password reset token storage
  - add guest routes and views for requesting and completing a reset
  - send reset links through the existing mail service
  - add focused regression and service coverage
  - document the slice in `.claude` and `_docs`
- Out-of-scope:
  - MFA or second-factor prompts
  - self-service account unlock dashboards
  - audit UI for password reset events
  - replacing the existing session-based login design
- Deadline or urgency: Continue immediately after the login throttling slice.
- Risk level: medium

## Assumptions

- Forgot-password must remain available to guests and should not require an active session.
- Reset request responses must stay generic for existing and non-existing accounts.
- Reset links should be single-use and expire after a configurable window.
- Successful reset should update the stored password without logging the user in automatically.
- Existing step-by-step doc workflow remains mandatory.

## Lead Agent

- Primary agent: Codex
- Supporting agents: none
- Relevant skills: none

## Affected Layers

- Schema:
  - password reset token tracking table
- Services:
  - new password reset service
- Auth flow:
  - request-reset and complete-reset controller handling
- Views:
  - forgot-password request screen
  - reset-password form
- Configuration:
  - auth password reset expiry settings in config and env example
- Verification:
  - forgot-password feature tests
  - password reset service unit tests
  - syntax checks plus the lightweight PHP suite
- Documentation:
  - `README.md`
  - `.claude/tasks/todo.md`
  - `_docs`

## Execution Plan

1. Lock the slice around guest-facing forgot-password recovery.
2. Add storage and service logic:
   - create a password reset token table
   - add reset-link issuance, lookup, expiry, and single-use handling
3. Wire the auth surface:
   - add request and reset routes
   - render request/reset forms and send reset links through the mail service
4. Verification and finish:
   - add focused feature and unit coverage
   - run database setup, syntax checks, and the full suite
   - document the slice in `.claude` and `_docs`

## Commit Plan

1. `docs: define forgot password slice`
   - update this task record with the forgot-password scope
2. `feat: add forgot password recovery flow`
   - add storage, service, routes, controller wiring, views, and docs
3. `test: verify forgot password recovery flow`
   - add regression coverage and finalize verification notes

## Checkable Work Items

- [x] Clarify the forgot-password gap
- [x] Add DB-backed password reset token storage and service logic
- [x] Add guest request and reset routes with views
- [x] Send reset links through the existing mail service
- [x] Add focused feature and unit tests
- [x] Run verification commands and capture evidence
- [x] Document result and open risks in `_docs`

## Progress Log

### Step 1
- Status: completed
- Notes: Reviewed the current auth controller, routes, mail service, login view, and test harness to scope a guest-facing token reset flow that fits the existing MVC structure.

### Step 2
- Status: completed
- Notes: Added DB-backed password reset token storage and a service that issues, validates, expires, and invalidates reset links.

### Step 3
- Status: completed
- Notes: Wired guest request/reset routes into the auth controller, added dedicated views, and linked the request flow from the login screen.

### Step 4
- Status: completed
- Notes: Applied the pending migration, verified syntax, checked the guarded fresh-reset refusal path, and re-ran the full suite with 85 passing tests.

## Verification Plan

- Automated checks:
  - run `php bin/setup-database.php`
  - run syntax checks for the new service and updated tests
  - run the existing lightweight suite
- Feature checks:
  - verify a guest can request a reset and the mail capture receives a link
  - verify unknown emails still receive the same generic success response
  - verify a guest can complete a reset with a valid token and cannot reuse it
- Unit checks:
  - verify issuing a second reset replaces the previous token
  - verify expired tokens are rejected

## Verification Evidence

- Planning evidence:
  - reviewed `app/Controllers/AuthController.php`
  - reviewed `app/Services/AuthService.php`
  - reviewed `app/Services/MailService.php`
  - reviewed `app/Models/User.php`
  - reviewed `resources/views/auth/login.php`
  - reviewed `tests/Feature/AuthenticationTest.php`
  - reviewed `tests/TestCase.php`
- Implementation evidence:
  - added `database/migrations/024_create_password_reset_tokens_table.sql`
  - added `app/Models/PasswordResetToken.php`
  - added `app/Services/PasswordResetService.php`
  - updated `app/Controllers/AuthController.php`
  - updated `routes/web.php`
  - added `resources/views/auth/forgot-password.php`
  - added `resources/views/auth/reset-password.php`
  - updated `resources/views/auth/login.php`
  - updated `config/auth.php`
  - updated `.env.example`
  - updated `README.md`
  - updated `tests/TestCase.php`
  - added `tests/Feature/ForgotPasswordTest.php`
  - added `tests/Unit/PasswordResetServiceTest.php`
  - added `_docs/205-forgot-password-recovery-flow.md`
  - added `_docs/206-forgot-password-recovery-flow-verification.md`
  - `php bin/setup-database.php` -> `Applied migrations: 1`
  - `php -l app/Services/PasswordResetService.php` -> `No syntax errors detected in app/Services/PasswordResetService.php`
  - `php -l app/Models/PasswordResetToken.php` -> `No syntax errors detected in app/Models/PasswordResetToken.php`
  - `php -l app/Controllers/AuthController.php` -> `No syntax errors detected in app/Controllers/AuthController.php`
  - `php -l tests/Feature/ForgotPasswordTest.php` -> `No syntax errors detected in tests/Feature/ForgotPasswordTest.php`
  - `php -l tests/Unit/PasswordResetServiceTest.php` -> `No syntax errors detected in tests/Unit/PasswordResetServiceTest.php`
  - `php -l tests/TestCase.php` -> `No syntax errors detected in tests/TestCase.php`
  - `php bin/setup-database.php --dry-run` -> `Pending migrations: 0`, `Pending seeds: 0`
  - `APP_ENV=local php bin/setup-database.php --fresh` -> `Database setup failed: Refusing fresh database setup outside APP_ENV=testing or CI.`
  - `php tests/run.php` -> `Executed 85 tests, 0 failed.`

## Result Review

- Outcome: completed
- What changed:
  - guests can now request a password reset through a generic-response recovery form
  - reset links are issued as expiring single-use tokens and sent through the existing mail service
  - a valid reset link now allows a password update and cannot be reused afterward
- What did not change:
  - MFA remains out of scope
  - the current session-based login architecture remains unchanged
- Risks still open:
  - reset request abuse controls and throttling are not part of this slice yet

## Completion Notes

- Definition of done met: yes
- Lessons update required: no
- Related lesson entry: Lesson 1, enforce auth controls on the server side
