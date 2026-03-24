# Add Stronger Audit Logging Around Personnel-Document Access

## Goal

Increase traceability for HR personnel-document handling without introducing a new database dependency. The audit trail should capture who accessed or modified a personnel document, from which request path, and with what outcome.

## Scope

- add a dedicated audit logging service for personnel-document events
- log HR personnel-document upload events
- log HR personnel-document download events
- log HR personnel-document delete events
- capture request metadata such as path, client IP, and user agent
- keep the implementation file-based so it is low-risk to deploy

## Implementation

### New Service

- added `app/Services/AuditLogService.php`
- writes JSON-lines entries to `storage/logs/personnel-document-access.log`
- payload includes:
  - timestamp
  - event name
  - action
  - outcome
  - optional reason
  - actor metadata
  - department metadata
  - employee metadata
  - document metadata
  - request metadata

### Request Metadata Support

- extended `app/Core/Request.php`
- added:
  - `ip()`
  - `userAgent()`

### HR Flow Integration

- updated `app/Controllers/DepartmentController.php`
- personnel-document events now write audit entries for:
  - successful upload
  - failed upload
  - successful download
  - failed download
  - successful delete
  - failed delete

### Service Return Values

- updated `app/Services/DepartmentService.php`
- `createEmployeeDocument()` now returns the created document row so the controller can log concrete metadata
- `deleteEmployeeDocument()` now returns the deleted document row for the same reason

### Automated Test Coverage

- added `tests/Unit/AuditLogServiceTest.php`
- updated `tests/run.php` to include the new test

## Result

HR personnel-document access now leaves a structured audit trail in a dedicated log file. This improves accountability and supports Datenschutz-oriented review of sensitive document handling without coupling the feature to a schema migration.
