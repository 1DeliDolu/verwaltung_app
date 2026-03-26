<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Services\AuditLogService;
use App\Services\CalendarService;

final class CalendarController extends Controller
{
    public function audit(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);

        $service = new CalendarService($this->app);
        $audit = new AuditLogService($this->app);
        $user = $service->currentUser();

        if ($user === null) {
            $this->redirect('/login');
            return;
        }

        $filters = [
            'search' => trim((string) $request->input('search', '')),
            'department_id' => (int) $request->input('department_id', 0),
            'action' => trim((string) $request->input('action', '')),
            'outcome' => trim((string) $request->input('outcome', '')),
            'date_from' => trim((string) $request->input('date_from', '')),
            'date_to' => trim((string) $request->input('date_to', '')),
        ];

        $events = array_values(array_filter(
            $audit->readCalendarActivityEvents($filters),
            static fn (array $event): bool => $service->auditEventVisibility($user, [
                'created_by' => (int) ($event['calendar_event']['created_by'] ?? 0),
                'department_ids' => (array) ($event['calendar_event']['department_ids'] ?? []),
            ])
        ));

        if ((string) $request->input('format', '') === 'csv') {
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="calendar-activity-audit.csv"');
            echo $audit->calendarActivityEventsAsCsv($events);
            return;
        }

        $this->render('pages/calendar_audit', [
            'app' => $this->app,
            'user' => $user,
            'events' => $events,
            'filters' => $filters,
            'departments' => $service->selectableDepartments($user),
            'actionOptions' => [
                'create_event' => 'Termin erstellt',
                'update_event' => 'Termin aktualisiert',
                'complete_event' => 'Termin erledigt',
                'delete_event' => 'Termin geloescht',
            ],
            'outcomeOptions' => [
                'success' => 'Erfolg',
                'failure' => 'Fehler',
            ],
        ]);
    }

    public function index(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);

        $service = new CalendarService($this->app);
        $audit = new AuditLogService($this->app);
        $user = $service->currentUser();
        $editingEvent = null;

        if ($user === null) {
            $this->redirect('/login');
            return;
        }

        if ((int) $request->input('edit', 0) > 0) {
            try {
                $editingEvent = $service->editableEvent((int) $request->input('edit', 0), $user);
            } catch (\RuntimeException $exception) {
                $this->app->session()->flash('error', 'Termin konnte nicht zum Bearbeiten geladen werden.');
                $this->redirect('/calendar');
            }
        }

        $this->render('pages/calendar', [
            'app' => $this->app,
            'user' => $user,
            'events' => $service->upcomingEvents(),
            'departments' => $service->selectableDepartments($user),
            'editingEvent' => $editingEvent,
            'csrfToken' => CsrfMiddleware::token($this->app),
            'success' => $this->app->session()->consumeFlash('success'),
            'error' => $this->app->session()->consumeFlash('error'),
            'old' => [
                'title' => (string) $this->app->session()->consumeFlash('calendar_old_title', ''),
                'description' => (string) $this->app->session()->consumeFlash('calendar_old_description', ''),
                'location' => (string) $this->app->session()->consumeFlash('calendar_old_location', ''),
                'starts_at' => (string) $this->app->session()->consumeFlash('calendar_old_starts_at', ''),
                'ends_at' => (string) $this->app->session()->consumeFlash('calendar_old_ends_at', ''),
                'department_ids' => (array) $this->app->session()->consumeFlash('calendar_old_department_ids', []),
                'edit_id' => (int) $this->app->session()->consumeFlash('calendar_old_edit_id', 0),
            ],
        ]);
    }

    public function store(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);
        CsrfMiddleware::validate($this->app, (string) $request->input('_token', ''));

        $service = new CalendarService($this->app);
        $audit = new AuditLogService($this->app);
        $user = $service->currentUser();

        if ($user === null) {
            $this->redirect('/login');
        }

        $payload = [
            'title' => (string) $request->input('title', ''),
            'description' => (string) $request->input('description', ''),
            'location' => (string) $request->input('location', ''),
            'starts_at' => (string) $request->input('starts_at', ''),
            'ends_at' => (string) $request->input('ends_at', ''),
            'department_ids' => (array) $request->input('department_ids', []),
        ];

        try {
            $service->createEvent($user, $payload);
            $departments = $service->departmentsForIds((array) $payload['department_ids']);
            $audit->recordCalendarActivityEvent('create_event', [
                'actor' => $user,
                'calendar_event' => [
                    'title' => $payload['title'],
                    'location' => $payload['location'],
                    'created_by' => (int) ($user['id'] ?? 0),
                    'department_ids' => $payload['department_ids'],
                    'department_names' => array_map(static fn (array $department): string => (string) $department['name'], $departments),
                ],
                'metadata' => [
                    'starts_at' => $payload['starts_at'],
                    'ends_at' => $payload['ends_at'],
                    'description' => $payload['description'],
                ],
            ]);
            $this->app->session()->flash('success', 'Termin wurde erstellt und Benachrichtigungen wurden versendet.');
        } catch (\RuntimeException $exception) {
            $departments = $service->departmentsForIds((array) $payload['department_ids']);
            $audit->recordCalendarActivityEvent('create_event', [
                'actor' => $user,
                'calendar_event' => [
                    'title' => $payload['title'],
                    'location' => $payload['location'],
                    'created_by' => (int) ($user['id'] ?? 0),
                    'department_ids' => $payload['department_ids'],
                    'department_names' => array_map(static fn (array $department): string => (string) $department['name'], $departments),
                ],
                'metadata' => [
                    'starts_at' => $payload['starts_at'],
                    'ends_at' => $payload['ends_at'],
                    'description' => $payload['description'],
                ],
                'outcome' => 'failure',
                'reason' => $exception->getMessage(),
            ]);
            $this->app->session()->flash('error', 'Termin konnte nicht gespeichert werden.');
            $this->app->session()->flash('calendar_old_title', $payload['title']);
            $this->app->session()->flash('calendar_old_description', $payload['description']);
            $this->app->session()->flash('calendar_old_location', $payload['location']);
            $this->app->session()->flash('calendar_old_starts_at', $payload['starts_at']);
            $this->app->session()->flash('calendar_old_ends_at', $payload['ends_at']);
            $this->app->session()->flash('calendar_old_department_ids', $payload['department_ids']);
        }

        $this->redirect('/calendar');
    }

    public function update(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);
        CsrfMiddleware::validate($this->app, (string) $request->input('_token', ''));

        $service = new CalendarService($this->app);
        $audit = new AuditLogService($this->app);
        $user = $service->currentUser();
        $eventId = (int) ($params['id'] ?? 0);

        if ($user === null) {
            $this->redirect('/login');
        }

        $payload = [
            'title' => (string) $request->input('title', ''),
            'description' => (string) $request->input('description', ''),
            'location' => (string) $request->input('location', ''),
            'starts_at' => (string) $request->input('starts_at', ''),
            'ends_at' => (string) $request->input('ends_at', ''),
            'department_ids' => (array) $request->input('department_ids', []),
        ];

        try {
            $event = $service->editableEvent($eventId, $user);
            $service->updateEvent($eventId, $user, $payload);
            $departments = $service->departmentsForIds((array) $payload['department_ids']);
            $audit->recordCalendarActivityEvent('update_event', [
                'actor' => $user,
                'calendar_event' => [
                    'id' => $eventId,
                    'title' => $payload['title'],
                    'location' => $payload['location'],
                    'created_by' => (int) ($event['created_by'] ?? 0),
                    'department_ids' => $payload['department_ids'],
                    'department_names' => array_map(static fn (array $department): string => (string) $department['name'], $departments),
                ],
                'metadata' => [
                    'starts_at' => $payload['starts_at'],
                    'ends_at' => $payload['ends_at'],
                    'description' => $payload['description'],
                ],
            ]);
            $this->app->session()->flash('success', 'Termin wurde aktualisiert.');
        } catch (\RuntimeException $exception) {
            $audit->recordCalendarActivityEvent('update_event', [
                'actor' => $user,
                'calendar_event' => [
                    'id' => $eventId,
                    'title' => $payload['title'],
                    'location' => $payload['location'],
                    'created_by' => (int) ($user['id'] ?? 0),
                    'department_ids' => $payload['department_ids'],
                    'department_names' => array_map(static fn (array $department): string => (string) $department['name'], $service->departmentsForIds((array) $payload['department_ids'])),
                ],
                'metadata' => [
                    'starts_at' => $payload['starts_at'],
                    'ends_at' => $payload['ends_at'],
                    'description' => $payload['description'],
                ],
                'outcome' => 'failure',
                'reason' => $exception->getMessage(),
            ]);
            $this->app->session()->flash('error', 'Termin konnte nicht aktualisiert werden.');
            $this->app->session()->flash('calendar_old_title', $payload['title']);
            $this->app->session()->flash('calendar_old_description', $payload['description']);
            $this->app->session()->flash('calendar_old_location', $payload['location']);
            $this->app->session()->flash('calendar_old_starts_at', $payload['starts_at']);
            $this->app->session()->flash('calendar_old_ends_at', $payload['ends_at']);
            $this->app->session()->flash('calendar_old_department_ids', $payload['department_ids']);
            $this->app->session()->flash('calendar_old_edit_id', $eventId);
            $this->redirect('/calendar?edit=' . $eventId);
        }

        $this->redirect('/calendar');
    }

    public function complete(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);
        CsrfMiddleware::validate($this->app, (string) $request->input('_token', ''));

        $service = new CalendarService($this->app);
        $audit = new AuditLogService($this->app);
        $user = $service->currentUser();

        if ($user === null) {
            $this->redirect('/login');
        }

        try {
            $event = $service->editableEvent((int) ($params['id'] ?? 0), $user);
            $service->completeEvent((int) ($params['id'] ?? 0), $user);
            $audit->recordCalendarActivityEvent('complete_event', [
                'actor' => $user,
                'calendar_event' => [
                    'id' => (int) ($event['id'] ?? 0),
                    'title' => (string) ($event['title'] ?? ''),
                    'location' => (string) ($event['location'] ?? ''),
                    'created_by' => (int) ($event['created_by'] ?? 0),
                    'department_ids' => (array) ($event['department_ids'] ?? []),
                    'department_names' => array_map(static fn (array $department): string => (string) $department['name'], $service->departmentsForIds((array) ($event['department_ids'] ?? []))),
                ],
                'metadata' => [
                    'starts_at' => (string) ($event['starts_at'] ?? ''),
                    'ends_at' => (string) ($event['ends_at'] ?? ''),
                    'description' => (string) ($event['description'] ?? ''),
                ],
            ]);
            $this->app->session()->flash('success', 'Termin wurde als erledigt markiert.');
        } catch (\RuntimeException $exception) {
            $audit->recordCalendarActivityEvent('complete_event', [
                'actor' => $user,
                'calendar_event' => ['id' => (int) ($params['id'] ?? 0)],
                'outcome' => 'failure',
                'reason' => $exception->getMessage(),
            ]);
            $this->app->session()->flash('error', 'Termin konnte nicht als erledigt markiert werden.');
        }

        $this->redirect('/calendar');
    }

    public function destroy(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);
        CsrfMiddleware::validate($this->app, (string) $request->input('_token', ''));

        $service = new CalendarService($this->app);
        $audit = new AuditLogService($this->app);
        $user = $service->currentUser();
        $eventId = (int) ($params['id'] ?? 0);

        if ($user === null) {
            $this->redirect('/login');
        }

        try {
            $event = $service->editableEvent($eventId, $user);
            $service->deleteEvent($eventId, $user);
            $audit->recordCalendarActivityEvent('delete_event', [
                'actor' => $user,
                'calendar_event' => [
                    'id' => (int) ($event['id'] ?? 0),
                    'title' => (string) ($event['title'] ?? ''),
                    'location' => (string) ($event['location'] ?? ''),
                    'created_by' => (int) ($event['created_by'] ?? 0),
                    'department_ids' => (array) ($event['department_ids'] ?? []),
                    'department_names' => array_map(static fn (array $department): string => (string) $department['name'], $service->departmentsForIds((array) ($event['department_ids'] ?? []))),
                ],
                'metadata' => [
                    'starts_at' => (string) ($event['starts_at'] ?? ''),
                    'ends_at' => (string) ($event['ends_at'] ?? ''),
                    'description' => (string) ($event['description'] ?? ''),
                ],
            ]);
            $this->app->session()->flash('success', 'Termin wurde geloescht.');
        } catch (\RuntimeException $exception) {
            $audit->recordCalendarActivityEvent('delete_event', [
                'actor' => $user,
                'calendar_event' => ['id' => $eventId],
                'outcome' => 'failure',
                'reason' => $exception->getMessage(),
            ]);
            $this->app->session()->flash('error', 'Termin konnte nicht geloescht werden.');
        }

        $this->redirect('/calendar');
    }
}
