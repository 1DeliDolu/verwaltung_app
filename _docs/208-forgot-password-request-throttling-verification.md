# Forgot Password Request Throttling Verification

## Scope
- Verify repeated forgot-password requests stop dispatching excess reset mails.
- Confirm the recovery form still returns the same generic success response.

## Automated Checks
- `php bin/setup-database.php`
- `php -l app/Services/PasswordResetRequestThrottleService.php`
- `php -l app/Services/PasswordResetService.php`
- `php -l tests/Feature/ForgotPasswordTest.php`
- `php -l tests/Unit/PasswordResetRequestThrottleServiceTest.php`
- `php bin/setup-database.php --dry-run`
- `APP_ENV=local php bin/setup-database.php --fresh`
- `php tests/run.php`

## Result
- `php bin/setup-database.php` passed and applied the pending forgot-password throttle migration.
- Syntax checks passed for the new throttle service and updated forgot-password coverage.
- `php bin/setup-database.php --dry-run` reported no pending migration or seed files after setup.
- `APP_ENV=local php bin/setup-database.php --fresh` failed safely with `Database setup failed: Refusing fresh database setup outside APP_ENV=testing or CI.`
- `php tests/run.php` passed with `Executed 88 tests, 0 failed.`

## Notes
- Feature coverage now verifies that throttled forgot-password requests keep the same generic success response while suppressing extra reset mails.
- Unit coverage verifies threshold lock behavior and decay-window expiry for the forgot-password throttle state.
- Existing forgot-password recovery coverage continues to verify mail capture, token use, and token reuse rejection on top of the new request throttle layer.
