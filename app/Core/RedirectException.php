<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

final class RedirectException extends RuntimeException
{
    public function __construct(private readonly string $path)
    {
        parent::__construct('Redirect to ' . $path);
    }

    public function path(): string
    {
        return $this->path;
    }
}
