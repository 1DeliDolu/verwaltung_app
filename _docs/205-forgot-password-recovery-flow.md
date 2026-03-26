# Forgot Password Recovery Flow

## Scope
- Add a guest-facing forgot-password request and reset flow.
- Keep reset request responses generic for existing and unknown accounts.
- Use single-use, expiring reset tokens backed by the database.

## Implementation
- Added `password_reset_tokens` storage with token hash, expiry, use tracking, and request metadata.
- Added `PasswordResetService` to issue reset links, validate active tokens, and complete password updates.
- Added guest auth routes for requesting a reset link and submitting a new password with a token.
- Added dedicated forgot-password and reset-password views and linked the request flow from the login screen.
- Reused the existing mail service so reset links follow the same local MailHog and capture-path workflow.

## Operational Notes
- Reset links are sent only when the submitted email is valid; the UI still uses a generic success message for unknown accounts.
- Issuing a new reset link invalidates any previous unused links for the same user.
- Reset links expire based on `AUTH_PASSWORD_RESET_EXPIRE_SECONDS`.
