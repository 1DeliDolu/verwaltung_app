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

    public function countDepartmentFiles(string $departmentSlug): int
    {
        return count($this->listDepartmentFiles($departmentSlug));
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

    public function storeEmployeeDocument(string $departmentSlug, int $employeeId, string $employeeNumber, array $file): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Upload failed.');
        }

        $originalName = trim((string) ($file['name'] ?? 'employee-document.bin'));
        $sanitizedName = preg_replace('/[^A-Za-z0-9._-]/', '-', $originalName) ?: 'employee-document.bin';
        $employeeSegment = preg_replace('/[^A-Za-z0-9_-]/', '-', $employeeNumber) ?: (string) $employeeId;
        $targetDirectory = $this->departmentRoot($departmentSlug) . '/employees/' . $employeeSegment;

        if (!is_dir($targetDirectory) && !mkdir($targetDirectory, 0777, true) && !is_dir($targetDirectory)) {
            throw new RuntimeException('Employee upload directory could not be created.');
        }

        $storedName = time() . '-' . $sanitizedName;
        $targetPath = $targetDirectory . '/' . $storedName;
        $tmpName = (string) ($file['tmp_name'] ?? '');

        if ($tmpName === '') {
            throw new RuntimeException('Temporary upload file is missing.');
        }

        if (!@move_uploaded_file($tmpName, $targetPath) && !@rename($tmpName, $targetPath)) {
            throw new RuntimeException('Uploaded file could not be stored.');
        }

        $departmentRoot = $this->departmentRoot($departmentSlug);
        $relativePath = str_replace($departmentRoot . DIRECTORY_SEPARATOR, '', $targetPath);

        return [
            'original_name' => $originalName === '' ? $storedName : $originalName,
            'stored_name' => $storedName,
            'file_path' => str_replace(DIRECTORY_SEPARATOR, '/', $relativePath),
            'mime_type' => (string) ($file['type'] ?? 'application/octet-stream'),
            'file_size' => (int) filesize($targetPath),
        ];
    }

    public function readDepartmentFile(string $departmentSlug, string $relativePath): string
    {
        $target = $this->resolveDepartmentFilePath($departmentSlug, $relativePath);
        $content = file_get_contents($target);

        if ($content === false) {
            throw new RuntimeException('Department file could not be read.');
        }

        return $content;
    }

    public function departmentFileMetadata(string $departmentSlug, string $relativePath): array
    {
        $target = $this->resolveDepartmentFilePath($departmentSlug, $relativePath);
        $mimeType = mime_content_type($target);

        return [
            'name' => basename($target),
            'path' => str_replace(DIRECTORY_SEPARATOR, '/', $relativePath),
            'mime_type' => is_string($mimeType) && $mimeType !== '' ? $mimeType : 'application/octet-stream',
            'size' => (int) filesize($target),
        ];
    }

    public function deleteDepartmentFile(string $departmentSlug, string $relativePath): void
    {
        $target = $this->resolveDepartmentFilePath($departmentSlug, $relativePath);

        if (!@unlink($target)) {
            throw new RuntimeException('Department file could not be deleted.');
        }
    }

    public function deleteEmployeeDirectory(string $departmentSlug, string $employeeNumber): void
    {
        $employeeSegment = preg_replace('/[^A-Za-z0-9_-]/', '-', $employeeNumber) ?: $employeeNumber;
        $directory = $this->departmentRoot($departmentSlug) . '/employees/' . $employeeSegment;

        if (!is_dir($directory)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                @rmdir($item->getPathname());
                continue;
            }

            @unlink($item->getPathname());
        }

        @rmdir($directory);
    }

    private function resolveDepartmentFilePath(string $departmentSlug, string $relativePath): string
    {
        $root = realpath($this->departmentRoot($departmentSlug));

        if ($root === false) {
            throw new RuntimeException('Department root could not be resolved.');
        }

        $target = realpath($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, ltrim($relativePath, '/')));

        if ($target === false || !str_starts_with($target, $root . DIRECTORY_SEPARATOR) || !is_file($target)) {
            throw new RuntimeException('Department file could not be found.');
        }

        return $target;
    }

    private function departmentRoot(string $departmentSlug): string
    {
        $root = (string) $this->app->config('filesystems.disks.department_shares.root', BASE_PATH . '/infra/file/shares');

        return rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $departmentSlug;
    }
}
