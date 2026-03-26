# Active Task Template

## Tomorrow Backlog

- Authentication hardening next slice:
  - add an email-based second step for privileged logins
  - keep the first credential check server-side and separate from session creation
  - use single-use expiring login challenge codes
  - keep `_docs` plus verification evidence aligned per slice

## Task Summary

- Request: Continue directly with the next auth hardening priority after forgot-password request throttling.
- Business goal: Add a practical MFA-style control for high-privilege logins without introducing external dependencies.
- Current gap summary:
  - admin logins still complete after only password verification
  - the project has mail delivery and verification primitives but no second login step
  - a narrow admin email challenge is the next feasible hardening slice before broader MFA options
- In-scope:
  - add DB-backed single-use email login challenges
  - require the extra verification step for configured high-privilege roles
  - add config knobs, challenge views, and focused regression coverage
  - document the slice in `.claude` and `_docs`
- Out-of-scope:
  - TOTP apps or authenticator QR enrollment
  - recovery codes
  - challenge-attempt throttling
  - broad MFA rollout to every role
- Deadline or urgency: Continue immediately after the forgot-password throttling slice.
- Risk level: medium

## Assumptions

- A narrow email challenge for admin logins is acceptable as an interim MFA step in this codebase.
- Password verification should succeed before the challenge is issued, but the auth session must not be created until the challenge is completed.
- Login challenge codes should be single-use and expire after a configurable window.
- Existing step-by-step doc workflow remains mandatory.

## Lead Agent

- Primary agent: Codex
- Supporting agents: none
- Relevant skills: none

## Affected Layers

- Schema:
  - login email challenge tracking table
- Services:
  - auth service refactor for pre-session credential validation
  - new email login challenge service
- Auth flow:
  - login controller challenge issuance and verification path
  - pending MFA session handling
- Views:
  - login challenge screen
- Configuration:
  - auth email challenge settings in config and env example
- Verification:
  - auth feature tests
  - email login challenge unit tests
  - syntax checks plus the lightweight PHP suite
- Documentation:
  - `README.md`
  - `.claude/tasks/todo.md`
  - `_docs`

## Execution Plan

1. Lock the slice around admin email login challenges.
2. Add storage and service logic:
   - create a login email challenge table
   - separate credential validation from authenticated session creation
3. Wire the auth surface:
   - issue a challenge after successful admin password verification
   - verify the challenge code before creating the auth session
4. Verification and finish:
   - add focused feature and unit coverage
   - run database setup, syntax checks, and the full suite
   - document the slice in `.claude` and `_docs`

## Commit Plan

1. `docs: define admin email challenge slice`
   - update this task record with the auth hardening scope
2. `feat: add admin email login challenge`
   - add storage, service, auth flow updates, views, and docs
3. `test: verify admin email login challenge`
   - add auth regression coverage and finalize verification notes

## Checkable Work Items

- [x] Clarify the admin email challenge gap
- [ ] Add DB-backed challenge storage and service logic
- [ ] Split password validation from session creation
- [ ] Enforce email challenge completion before admin session creation
- [ ] Add focused feature and unit tests
- [ ] Run verification commands and capture evidence
- [ ] Document result and open risks in `_docs`

## Progress Log

### Step 1
- Status: completed
- Notes: Reviewed the current auth service, user model, login controller flow, and test harness to scope a narrow admin email challenge slice.

### Step 2
- Status: pending
- Notes: Add login email challenge storage and service logic.

### Step 3
- Status: pending
- Notes: Wire password validation, challenge issuance, and challenge verification into the login flow.

### Step 4
- Status: pending
- Notes: Add coverage, run verification commands, and capture final evidence.

## Verification Plan

- Automated checks:
  - run `php bin/setup-database.php`
  - run syntax checks for the new service and updated tests
  - run the existing lightweight suite
- Feature checks:
  - verify admin login redirects to the challenge step and captures a mailed code
  - verify a correct challenge code completes the login
  - verify non-admin logins still complete without the extra challenge
- Unit checks:
  - verify issuing a second challenge invalidates the previous one
  - verify expired challenge codes are rejected

## Verification Evidence

- Planning evidence:
  - reviewed `app/Services/AuthService.php`
  - reviewed `app/Controllers/AuthController.php`
  - reviewed `app/Models/User.php`
  - reviewed `config/auth.php`
  - reviewed `tests/Feature/AuthenticationTest.php`
- Implementation evidence:
  - pending

## Result Review

- Outcome: in progress
- What changed so far:
  - admin email challenge scope is documented and constrained
- What did not change:
  - forgot-password flow remains unchanged
  - broad MFA rollout remains out of scope
- Risks still open:
  - challenge-attempt throttling is not part of this slice

## Completion Notes

- Definition of done met: not yet
- Lessons update required: no
- Related lesson entry: pending
