# Weekly Audit Host PHP Binary Overrides Verification

## Scope
- Verify weekly audit host renderers and install helpers can persist an explicit host PHP binary.
- Confirm default assets remain backward compatible with `PHP_BIN=php`.

## Automated Checks
- `bash -n infra/scripts/render-weekly-audit-report-systemd.sh`
- `bash -n infra/scripts/render-weekly-audit-report-cron.sh`
- `bash -n infra/scripts/install-weekly-audit-report-systemd.sh`
- `bash -n infra/scripts/install-weekly-audit-report-cron.sh`
- `php -l tests/Feature/AuditWeeklyReportHostAutomationAssetsTest.php`
- `php -l tests/Feature/AuditWeeklyReportHostAutomationInstallersTest.php`
- `php tests/run.php`

## Result
- All four `bash -n` checks passed.
- `php -l tests/Feature/AuditWeeklyReportHostAutomationAssetsTest.php` passed.
- `php -l tests/Feature/AuditWeeklyReportHostAutomationInstallersTest.php` passed.
- `php tests/run.php` passed with `Executed 72 tests, 0 failed.`

## Notes
- Renderer coverage now verifies both the default `PHP_BIN=php` path and explicit `/usr/bin/php8.2` overrides.
- Installer coverage confirms the same override survives the copy step into target host asset paths.
- The wrapper script remains the runtime single source of truth; this slice only made host asset configuration explicit.
