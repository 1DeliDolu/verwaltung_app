<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;

final class PageController extends Controller
{
    public function news(Request $request): void
    {
        $this->render('pages/news', [
            'app' => $this->app,
        ]);
    }

    public function calendar(Request $request): void
    {
        $this->render('pages/calendar', [
            'app' => $this->app,
        ]);
    }
}
