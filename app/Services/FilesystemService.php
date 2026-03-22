<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\App;
use RuntimeException;

final class FilesystemService
{
    public function __construct(private readonly App $app)
    {
    }

    public function listDepartmentFiles(string $departmentSlug): array
    {
        $directory = $this->departmentRoot($departmentSlug);

        if (!is_dir($directory)) {
            return [];
        }

        $files = [];
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }

            $relativePath = str_replace($directory . DIRECTORY_SEPARATOR, '', $file->getPathname());
            $files[] = [
                'name' => $file->getFilename(),
                'path' => str_replace(DIRECTORY_SEPARATOR, '/', $relativePath),
                'size' => $file->getSize(),
                'modified_at' => date('Y-m-d H:i:s', $file->getMTime()),
            ];
        }

        usort($files, static fn (array $left, array $right): int => strcmp($left['path'], $right['path']));

        return $files;
    }

    public function storeDepartmentUpload(string $departmentSlug, array $file): void
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Upload failed.');
        }

        $originalName = (string) ($file['name'] ?? 'upload.bin');
        $sanitizedName = preg_replace('/[^A-Za-z0-9._-]/', '-', $originalName) ?: 'upload.bin';
        $targetDirectory = $this->departmentRoot($departmentSlug) . '/uploads';

        if (!is_dir($targetDirectory) && !mkdir($targetDirectory, 0777, true) && !is_dir($targetDirectory)) {
            throw new RuntimeException('Upload directory could not be created.');
        }

        $targetPath = $targetDirectory . '/' . time() . '-' . $sanitizedName;
        $tmpName = (string) ($file['tmp_name'] ?? '');

        if ($tmpName === '') {
            throw new RuntimeException('Temporary upload file is missing.');
        }

        if (!@move_uploaded_file($tmpName, $targetPath) && !@rename($tmpName, $targetPath)) {
            throw new RuntimeException('Uploaded file could not be stored.');
        }
    }

    private function departmentRoot(string $departmentSlug): string
    {
        $root = (string) $this->app->config('filesystems.disks.department_shares.root', BASE_PATH . '/infra/file/shares');

        return rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $departmentSlug;
    }
}
