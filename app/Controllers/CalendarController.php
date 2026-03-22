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

        $this->render('pages/calendar', [
            'app' => $this->app,
            'user' => $user,
            'events' => $service->upcomingEvents(),
            'departments' => $user === null ? [] : $service->selectableDepartments($user),
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

    public function complete(Request $request, array $params = []): void
    {
        AuthMiddleware::handle($this->app);
        CsrfMiddleware::validate($this->app, (string) $request->input('_token', ''));

        (new CalendarService($this->app))->markComplete((int) ($params['id'] ?? 0));
        $this->app->session()->flash('success', 'Termin wurde als erledigt markiert.');

        $this->redirect('/calendar');
    }
}
