# Admin Email Login Challenge

## Scope
- Add an email-based second step for configured high-privilege logins.
- Keep password verification server-side but delay session creation until challenge completion.
- Use single-use expiring login challenge codes.

## Implementation
- Added `login_email_challenges` storage for single-use login codes with expiry and consume tracking.
- Added `EmailLoginChallengeService` to issue codes, send them through the existing mail service, and verify them before login completion.
- Refactored `AuthService` to separate credential validation from authenticated session creation.
- Added `/login/challenge` routes and a dedicated challenge view for pending MFA completion.
- Limited the extra login step to configured roles via auth config.

## Operational Notes
- Admin logins now require the emailed code before the auth session is created.
- Non-admin logins continue to follow the existing password-only flow.
- Challenge attempt throttling and broader MFA rollout remain separate follow-up slices.
