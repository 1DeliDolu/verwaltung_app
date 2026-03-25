<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;
use App\Services\CalendarService;

final class CalendarController extends Controller
{
    public function index(Request $request, array $params = []): void
    {
        $service = new CalendarService($this->app);
        $user = $service->currentUser();
        $editingEvent = null;

        if ($user !== null && (int) $request->input('edit', 0) > 0) {
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
            'departments' => $user === null ? [] : $service->selectableDepartments($user),
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
            $this->app->session()->flash('success', 'Termin wurde erstellt und Benachrichtigungen wurden versendet.');
        } catch (\RuntimeException $exception) {
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
            $service->updateEvent($eventId, $user, $payload);
            $this->app->session()->flash('success', 'Termin wurde aktualisiert.');
        } catch (\RuntimeException $exception) {
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
        $user = $service->currentUser();

        if ($user === null) {
            $this->redirect('/login');
        }

        try {
            $service->completeEvent((int) ($params['id'] ?? 0), $user);
            $this->app->session()->flash('success', 'Termin wurde als erledigt markiert.');
        } catch (\RuntimeException $exception) {
            $this->app->session()->flash('error', 'Termin konnte nicht als erledigt markiert werden.');
        }

        $this->redirect('/calendar');
    }

    public function destroy(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);
        CsrfMiddleware::validate($this->app, (string) $request->input('_token', ''));

        $service = new CalendarService($this->app);
        $user = $service->currentUser();
        $eventId = (int) ($params['id'] ?? 0);

        if ($user === null) {
            $this->redirect('/login');
        }

        try {
            $service->deleteEvent($eventId, $user);
            $this->app->session()->flash('success', 'Termin wurde geloescht.');
        } catch (\RuntimeException $exception) {
            $this->app->session()->flash('error', 'Termin konnte nicht geloescht werden.');
        }

        $this->redirect('/calendar');
    }
}
