# Weekly Audit Host Install Helpers Verification

## Scope
- Verify systemd and cron install helpers for the weekly audit report.
- Confirm installer scripts reuse rendered output, copy files into the requested targets, and fail safely when required install arguments are missing.

## Automated Checks
- `bash -n infra/scripts/install-weekly-audit-report-systemd.sh`
- `bash -n infra/scripts/install-weekly-audit-report-cron.sh`
- `php -l tests/Feature/AuditWeeklyReportHostAutomationInstallersTest.php`
- `php tests/run.php`

## Result
- `bash -n` passed for both install helper scripts.
- `php -l tests/Feature/AuditWeeklyReportHostAutomationInstallersTest.php` passed.
- `php tests/run.php` passed with `Executed 69 tests, 0 failed.`

## Notes
- The verification uses temporary install targets so the scripts stay testable without writing into live host scheduler directories.
- Installer coverage complements the existing renderer coverage instead of replacing it.
- The new feature test also verifies temporary `mktemp` artifacts are cleaned up after installer execution.
