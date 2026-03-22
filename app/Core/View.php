<?php

declare(strict_types=1);

namespace App\Core;

final class View
{
    public static function render(string $view, array $data = [], string $layout = 'app'): string
    {
        $basePath = dirname(__DIR__, 2) . '/resources/views';
        $viewPath = $basePath . '/' . $view . '.php';
        $layoutPath = $basePath . '/layouts/' . $layout . '.php';

        if (!is_file($viewPath)) {
            throw new \RuntimeException(sprintf('View not found: %s', $view));
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $viewPath;
        $content = (string) ob_get_clean();

        if (!is_file($layoutPath)) {
            return $content;
        }

        ob_start();
        require $layoutPath;

        return (string) ob_get_clean();
    }
}
