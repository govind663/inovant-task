<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;

trait UploadFile
{
    /**
     * Upload multiple files (dynamic folder)
     */
    public function uploadMultiple(array $files, string $folder): array
    {
        $paths = [];

        foreach ($files as $file) {
            $paths[] = $file->store($folder, 'public');
        }

        return $paths;
    }

    /**
     * Delete multiple files safely
     */
    public function deleteFiles(array $paths): void
    {
        foreach ($paths as $path) {
            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }
    }

    /**
     * Upload single file (optional helper)
     */
    public function uploadSingle($file, string $folder): string
    {
        return $file->store($folder, 'public');
    }
}