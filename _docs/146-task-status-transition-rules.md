Task status changes now follow explicit workflow transition rules instead of allowing arbitrary status selection.

Implemented changes:
- Added backend-enforced transition rules in `TaskService`.
- Task workers and task managers now have different allowed transitions.
- Status updates now reject invalid jumps even if a forged request is submitted.
- Task detail UI only shows statuses that are valid for the current actor and current state.

Workflow rules:
- `open`
  - worker: `in_progress`, `blocked`
  - manager: `in_progress`, `blocked`, `done`
- `in_progress`
  - worker: `blocked`, `done`
  - manager: `open`, `blocked`, `done`
- `blocked`
  - worker: `in_progress`
  - manager: `open`, `in_progress`, `done`
- `done`
  - worker: no transitions
  - manager: `open`, `in_progress`

Role meaning:
- manager = admin, creator, or department team leader according to existing authorization rules
- worker = assigned user without management authority

User outcome:
- Status changes now reflect a real task lifecycle.
- Reopening or resetting tasks is limited to management-capable actors.
- Finished tasks no longer expose meaningless transitions for normal workers.
