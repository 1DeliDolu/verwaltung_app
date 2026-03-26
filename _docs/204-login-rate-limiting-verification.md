# Login Rate Limiting Verification

## Scope
- Verify repeated failed logins are throttled on the server side.
- Confirm valid credentials remain blocked during an active lockout window.

## Automated Checks
- `php bin/setup-database.php`
- `php -l app/Services/LoginThrottleService.php`
- `php -l tests/Feature/AuthenticationTest.php`
- `php -l tests/Unit/LoginThrottleServiceTest.php`
- `php tests/run.php`

## Result
- `php bin/setup-database.php` passed and applied the pending login throttle migration.
- Syntax checks passed for the new login throttle service and updated test files.
- `php bin/setup-database.php --dry-run` reported no pending migration or seed files after setup.
- `APP_ENV=local php bin/setup-database.php --fresh` failed safely with `Database setup failed: Refusing fresh database setup outside APP_ENV=testing or CI.`
- `php tests/run.php` passed with `Executed 80 tests, 0 failed.`

## Notes
- Feature coverage now verifies both generic invalid-login behavior and the lockout path for repeated failures from the same email and IP.
- Unit coverage verifies that lockouts activate at the configured threshold and clear again after the decay window.
- The existing workspace database initially exposed a legacy-adoption edge in the setup runner, so the verification also confirmed the corrected migration application path before auth tests ran.
