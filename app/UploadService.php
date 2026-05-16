<?php

declare(strict_types=1);

namespace App;

use RuntimeException;

final class UploadService
{
    private const ALLOWED_TYPES = [
        'images' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
        'teachers' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'application/pdf'],
        'lessons' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'application/pdf'],
        'documents' => ['application/pdf', 'image/jpeg', 'image/png', 'image/webp', 'text/plain'],
    ];

    public function store(array $file, string $type): ?string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Upload failed. Try a smaller file or upload again.');
        }

        if (!array_key_exists($type, self::ALLOWED_TYPES)) {
            throw new RuntimeException('Unsupported upload type.');
        }

        if (($file['size'] ?? 0) > (int) \config('uploads.max_bytes', 8388608)) {
            throw new RuntimeException('File is larger than the configured upload limit.');
        }

        $mime = mime_content_type((string) $file['tmp_name']) ?: 'application/octet-stream';
        if (!in_array($mime, self::ALLOWED_TYPES[$type], true)) {
            throw new RuntimeException('This file type is not allowed.');
        }

        $directory = \upload_base_path() . $type;
        if (!is_dir($directory) && !mkdir($directory, 0775, true)) {
            throw new RuntimeException($this->directoryHelp($directory, 'could not be created'));
        }
        if (!is_writable($directory)) {
            throw new RuntimeException($this->directoryHelp($directory, 'is not writable'));
        }

        $filename = $this->safeFilename((string) ($file['name'] ?? 'upload.bin'));
        $destination = $this->uniqueDestination($directory, $filename);

        if (!move_uploaded_file((string) $file['tmp_name'], $destination)) {
            throw new RuntimeException('Could not save uploaded file.');
        }

        return $type . '/' . basename($destination);
    }

    public static function sanitizeType(string $type): ?string
    {
        return preg_match('/\A[a-zA-Z0-9_-]+\z/', $type) ? $type : null;
    }

    public static function sanitizeFilename(string $filename): ?string
    {
        $filename = rawurldecode($filename);
        $filename = str_replace(["\0", '\\', '/'], '', $filename);
        if (str_contains($filename, '..') || str_starts_with($filename, '.')) {
            return null;
        }

        if (!preg_match('/\A[a-zA-Z0-9_-]+\.[a-zA-Z0-9]+\z/', $filename)) {
            return null;
        }

        return $filename;
    }

    private function safeFilename(string $name): string
    {
        $name = basename(str_replace('\\', '/', $name));
        $extension = pathinfo($name, PATHINFO_EXTENSION);
        $stem = pathinfo($name, PATHINFO_FILENAME);
        $stem = preg_replace('/[^a-zA-Z0-9_-]+/', '-', $stem) ?: 'upload';
        $extension = preg_replace('/[^a-zA-Z0-9]+/', '', $extension) ?: 'bin';

        return trim($stem, '-_') . '.' . strtolower($extension);
    }

    private function uniqueDestination(string $directory, string $filename): string
    {
        $path = $directory . '/' . $filename;
        if (!file_exists($path)) {
            return $path;
        }

        $stem = pathinfo($filename, PATHINFO_FILENAME);
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        return $directory . '/' . $stem . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(3)) . '.' . $extension;
    }

    private function directoryHelp(string $directory, string $reason): string
    {
        $base = \upload_base_path();
        if (str_starts_with($base, '/private/tmp')) {
            return 'Upload storage still points to the local development path. Set UPLOAD_BASE_PATH to a Hostinger folder outside public_html, for example /home/USERNAME/uploads, then create images, teachers, lessons, and documents inside it.';
        }

        return 'Upload directory ' . $reason . ': ' . $directory . '. Create it outside public_html and make sure PHP can write to it.';
    }
}
