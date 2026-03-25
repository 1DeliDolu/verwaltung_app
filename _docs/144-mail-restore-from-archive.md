Internal mail archive now supports restoring messages back into the active mailbox.

Implemented changes:
- Added backend restore logic for recipient-side and sender-side archived messages.
- Added `POST /mail/{mailId}/restore` route and controller action.
- Extended `InternalMailService` with a restore method.
- Reused the existing mail detail action button so it switches by context:
  - active folders: `Archivieren`
  - archive folder: `Wiederherstellen`

Behavior:
- Inbox and sent messages can still be archived as before.
- Archived messages can now be restored from the archive detail view.
- Restore uses the same authorization boundary as archive, scoped to the current user only.

Implementation note:
- No schema change was required because the existing `archived_at` and `sender_archived_at` fields already support null-based restoration.
