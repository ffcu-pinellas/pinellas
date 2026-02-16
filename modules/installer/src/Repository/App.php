<?php

namespace Remotelywork\Installer\Repository;

use DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class App
{
    protected static $token = 'E9dgn4KPtrRxUwdZ4n03tsc3qesqzBiN';

    protected static $cacheKey = 'license_validated';

    protected static $validatedTtl = 86400;

    protected static $installedFile = 'installed';

    public static function dbConnectionCheck(): bool
    {
        $ok = false;

        try {

            DB::getPdo();
            DB::connection()->getDatabaseName();

            $ok = true;

            if (! file_exists(storage_path(self::$installedFile))) {
                $ok = false;
            }

            return $ok;

        } catch (\Throwable $th) {
            $ok = false;
        }

        return $ok;
    }

    public static function initApp()
    {
        return true;
    }

    public static function validateLicense($code = null)
    {
        return true;
    }
}
