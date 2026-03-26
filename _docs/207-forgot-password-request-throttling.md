# Forgot Password Request Throttling

## Scope
- Add backend throttling for forgot-password requests.
- Keep the guest-facing recovery response generic while suppressing excess mail dispatch.
- Apply the throttle before a new reset token is issued.

## Implementation
- Added `password_reset_request_limits` storage keyed by normalized email and IP.
- Added `PasswordResetRequestThrottleService` to track request windows and temporary lockouts.
- Integrated the throttle into `PasswordResetService` so repeated requests in the lock window stop sending new mails.
- Added config knobs for forgot-password request thresholds and decay seconds.

## Operational Notes
- The recovery form still returns the same generic success message for existing and unknown accounts.
- Requests up to the configured threshold still dispatch; additional requests inside the active lock window are suppressed.
- This slice does not add broader IP-only or network-wide abuse analytics.
