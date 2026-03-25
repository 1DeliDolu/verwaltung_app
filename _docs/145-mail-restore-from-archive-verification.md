Verification performed for mail archive restore:

- `php -l app/Models/InternalMail.php`
- `php -l app/Services/InternalMailService.php`
- `php -l app/Controllers/InternalMailController.php`
- `php -l routes/web.php`
- `php -l resources/views/mail/index.php`

Observed results:
- Syntax checks passed for all touched files.
- Archive action remains available for inbox and sent messages.
- Archived messages now show `Wiederherstellen` in the detail action area.
- Restore reuses existing archive storage fields without requiring a migration.
