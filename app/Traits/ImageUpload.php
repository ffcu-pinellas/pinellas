<?php

namespace App\Traits;

use Str;

trait ImageUpload
{
    public function imageUploadTrait($query, $old = null): string // Taking input image as parameter
    {

        $allowExt = ['jpeg', 'png', 'jpg', 'gif', 'svg'];
        $ext = strtolower($query->getClientOriginalExtension());

        if ($query->getSize() > 5100000) {
            notify()->error(__('Max file size:5MB '), 'Error');

            return redirect()->back();
        }

        if (! in_array($ext, $allowExt)) {
            notify()->error(__('Only allow : jpeg, png, jpg, gif, svg'), 'Error');

            return redirect()->back();
        }

        if ($old != null) {
            self::delete($old);
        }
        $image_name = Str::random(20);
        $image_full_name = $image_name.'.'.$ext;
        $relative_dir = 'assets/global/images/';
        $image_url = $relative_dir . $image_full_name;

        // Ensure directories exist and are writable in both base and public paths
        $target_dirs = [
            base_path('assets/global/images'),
            public_path('assets/global/images'),
        ];

        foreach ($target_dirs as $dir) {
            if (!file_exists($dir)) {
                @mkdir($dir, 0777, true);
            }
            @chmod($dir, 0777);
        }

        // Try standard Symfony move first; fallback to copy/stream if permission restriction
        $saved = false;
        try {
            $query->move(base_path('assets/global/images'), $image_full_name);
            $saved = true;
        } catch (\Throwable $e) {
            \Log::warning('ImageUpload::move failed, attempting copy fallback: ' . $e->getMessage());
        }

        if (!$saved) {
            try {
                $sourcePath = $query->getRealPath() ?: $query->getPathname();
                if ($sourcePath && file_exists($sourcePath)) {
                    @copy($sourcePath, base_path('assets/global/images/' . $image_full_name));
                    $saved = true;
                }
            } catch (\Throwable $e) {
                \Log::warning('ImageUpload::copy fallback failed: ' . $e->getMessage());
            }
        }

        // Ensure copy exists in public_path as well for direct web serving
        try {
            $baseFile = base_path('assets/global/images/' . $image_full_name);
            $publicFile = public_path('assets/global/images/' . $image_full_name);
            if (file_exists($baseFile) && !file_exists($publicFile)) {
                @copy($baseFile, $publicFile);
            }
        } catch (\Throwable $e) {}

        return $image_url; // Return full path starting with assets/
    }

    protected function delete($path)
    {
        if (file_exists('assets/'.$path)) {
            @unlink('assets/'.$path);
        }
    }
}
