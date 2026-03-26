# Weekly Audit Email Report Verification

## Scope
- Verify the admin weekly audit report send flow from `/audit`.
- Confirm the report ignores currently active dashboard filters and uses the fixed weekly window.
- Confirm non-admin users still cannot trigger central audit report delivery.

## Automated Checks
- `php -l app/Services/AuditDashboardService.php`
- `php -l app/Services/AuditWeeklyReportService.php`
- `php -l app/Controllers/AuditController.php`
- `php -l app/Services/MailService.php`
- `php -l config/mail.php`
- `php -l routes/web.php`
- `php -l resources/views/audit/index.php`
- `php -l resources/views/mail/templates/audit-weekly-report-text.php`
- `php -l resources/views/mail/templates/audit-weekly-report-html.php`
- `php -l tests/Feature/AuditWeeklyReportTest.php`
- `php tests/run.php`

## Result
- Targeted lint checks passed for all new and changed weekly-report files.
- `php tests/run.php` passed with `Executed 60 tests, 0 failed.`
- Added feature coverage in `tests/Feature/AuditWeeklyReportTest.php` for:
  - admin send flow
  - fixed weekly-window behavior independent of active dashboard filters
  - non-admin denial

## Notes
- The report send test uses `MAIL_CAPTURE_PATH` to capture the outbound message payload without depending on live SMTP.
- The captured payload verifies the rendered mail body, recipient list, and CSV attachment content for the weekly report window.
