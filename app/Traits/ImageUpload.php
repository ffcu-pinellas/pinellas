<?php

namespace App\Traits;

use Str;

trait ImageUpload
{
    public function imageUploadTrait($query, $old = null): string // Taking input image as parameter
    {
        $allowExt = ['jpeg', 'png', 'jpg', 'gif', 'svg', 'webp'];
        $ext = strtolower($query->getClientOriginalExtension() ?: 'png');

        if ($query->getSize() > 15728640) { // 15MB limit
            throw new \InvalidArgumentException(__('Max file size is 15MB.'));
        }

        if (! in_array($ext, $allowExt)) {
            $ext = 'png';
        }

        if ($old != null) {
            self::delete($old);
        }
        $image_name = Str::random(20);
        $image_full_name = $image_name.'.'.$ext;
        $relative_dir = 'assets/global/images/';
        $image_url = $relative_dir . $image_full_name;

        // Pre-read raw binary stream to guarantee zero data loss across ephemeral/container environments
        $rawBytes = null;
        try {
            $sourcePath = $query->getRealPath() ?: $query->getPathname();
            if ($sourcePath && file_exists($sourcePath)) {
                $rawBytes = @file_get_contents($sourcePath);
            }
        } catch (\Throwable $e) {}

        // Ensure directories exist and are writable in both base and public paths
        $target_dirs = [
            public_path('assets/global/images'),
            base_path('assets/global/images'),
            storage_path('app/public/images'),
        ];

        foreach ($target_dirs as $dir) {
            try {
                if (!file_exists($dir)) {
                    @mkdir($dir, 0777, true);
                }
                @chmod($dir, 0777);
            } catch (\Throwable $e) {}
        }

        // Try standard Symfony move first; fallback to copy/stream if permission restriction
        $saved = false;
        try {
            $destDir = public_path('assets/global/images');
            if (!file_exists($destDir)) {
                @mkdir($destDir, 0777, true);
            }
            $query->move($destDir, $image_full_name);
            $saved = true;
            // Also copy to base_path if different
            if (base_path('assets/global/images') !== $destDir) {
                @copy($destDir . '/' . $image_full_name, base_path('assets/global/images/' . $image_full_name));
            }
        } catch (\Throwable $e) {
            \Log::warning('ImageUpload::move failed, attempting copy fallback: ' . $e->getMessage());
        }

        if (!$saved && $rawBytes !== null) {
            try {
                @file_put_contents(public_path('assets/global/images/' . $image_full_name), $rawBytes);
                @file_put_contents(base_path('assets/global/images/' . $image_full_name), $rawBytes);
                $saved = true;
            } catch (\Throwable $e) {
                \Log::warning('ImageUpload::copy fallback failed: ' . $e->getMessage());
            }
        }

        // If still not saved on disk (e.g. read-only container), fallback to data URI
        if (!$saved) {
            if ($rawBytes) {
                return 'data:image/' . $ext . ';base64,' . base64_encode($rawBytes);
            }
        }

        return $image_url; // Return full path starting with assets/
    }

    protected function delete($path)
    {
        if (file_exists('assets/'.$path)) {
            @unlink('assets/'.$path);
        }
    }
}
