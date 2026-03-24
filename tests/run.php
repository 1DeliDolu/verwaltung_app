<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
require __DIR__ . '/TestCase.php';

$testFiles = [
    __DIR__ . '/Unit/AuthServiceTest.php',
    __DIR__ . '/Unit/DepartmentServiceTest.php',
];

$failures = [];
$executed = 0;

foreach ($testFiles as $testFile) {
    require_once $testFile;
}

foreach (get_declared_classes() as $className) {
    if (!is_subclass_of($className, TestCase::class)) {
        continue;
    }

    $testCase = new $className();
    $reflection = new ReflectionClass($testCase);

    foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
        if (!str_starts_with($method->getName(), 'test')) {
            continue;
        }

        $executed++;

        try {
            $method->invoke($testCase);
            echo '[PASS] ' . $className . '::' . $method->getName() . PHP_EOL;
        } catch (Throwable $throwable) {
            $failures[] = '[FAIL] ' . $className . '::' . $method->getName() . ' - ' . $throwable->getMessage();
        }
    }
}

foreach ($failures as $failure) {
    echo $failure . PHP_EOL;
}

echo PHP_EOL . sprintf('Executed %d tests, %d failed.', $executed, count($failures)) . PHP_EOL;

exit($failures === [] ? 0 : 1);
