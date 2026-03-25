<?php

declare(strict_types=1);

use App\Core\App;
use App\Core\Database;
use App\Core\Env;

define('BASE_PATH', dirname(__DIR__));
define('APP_RUNNING_TESTS', true);

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $path = BASE_PATH . '/app/' . str_replace('\\', '/', $relativeClass) . '.php';

    if (is_file($path)) {
        require_once $path;
    }
});

require_once BASE_PATH . '/app/Core/Env.php';

Env::load(BASE_PATH . '/.env');

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        if ($value === false || $value === null || $value === '') {
            return $default;
        }

        return $value;
    }
}

function testApp(): App
{
    static $app = null;

    if ($app instanceof App) {
        return $app;
    }

    $config = [
        'app' => require BASE_PATH . '/config/app.php',
        'auth' => require BASE_PATH . '/config/auth.php',
        'database' => require BASE_PATH . '/config/database.php',
        'departments' => require BASE_PATH . '/config/departments.php',
        'filesystems' => require BASE_PATH . '/config/filesystems.php',
        'mail' => require BASE_PATH . '/config/mail.php',
    ];
    Database::connect($config['database']['connections'][$config['database']['default']]);

    $app = new App($config);

    return $app;
}

function freshTestApp(): App
{
    $config = [
        'app' => require BASE_PATH . '/config/app.php',
        'auth' => require BASE_PATH . '/config/auth.php',
        'database' => require BASE_PATH . '/config/database.php',
        'departments' => require BASE_PATH . '/config/departments.php',
        'filesystems' => require BASE_PATH . '/config/filesystems.php',
        'mail' => require BASE_PATH . '/config/mail.php',
    ];
    Database::connect($config['database']['connections'][$config['database']['default']]);

    return new App($config);
}
