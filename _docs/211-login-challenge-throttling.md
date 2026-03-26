# Login Challenge Throttling

## Scope
- Add backend throttling for admin login challenge verification attempts.
- Keep the challenge UI generic while blocking repeated brute-force retries.
- Clear the attempt state after successful code verification.

## Implementation
- Added `login_challenge_attempt_limits` storage keyed by challenge and IP.
- Added `LoginChallengeThrottleService` to track failed verification windows and temporary lockouts.
- Integrated the throttle into `EmailLoginChallengeService::verify()` before code comparison.
- Successful challenge verification now clears the associated attempt state for the same challenge and IP.
- Added config knobs for challenge verification thresholds and decay seconds.

## Operational Notes
- Wrong challenge codes now move the pending challenge into a temporary lock state after the configured threshold.
- A correct code remains blocked while that lock is active.
- This slice does not add challenge resend UX or broader network-level abuse analytics.
