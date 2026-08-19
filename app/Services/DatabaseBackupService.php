<?php

namespace App\Services;

use App\Models\Setting;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DatabaseBackupService
{
    /**
     * Directory path where backup files are stored.
     */
    public static function getBackupDirectory(): string
    {
        $path = storage_path('app/backups');
        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }
        return $path;
    }

    /**
     * Generate a complete MySQL database dump (.sql or .sql.gz) using PDO streaming.
     * Guaranteed to work on shared hosting (Hostinger) without requiring mysqldump binary.
     *
     * @param bool $compress
     * @return array ['success' => bool, 'path' => string, 'filename' => string, 'size_bytes' => int, 'tables_count' => int, 'error' => string|null]
     */
    public function generateDump(bool $compress = false): array
    {
        try {
            $dbName = config('database.connections.mysql.database');
            $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
            $extension = $compress ? 'sql.gz' : 'sql';
            $filename = "backup_{$dbName}_{$timestamp}.{$extension}";
            $fullPath = self::getBackupDirectory() . DIRECTORY_SEPARATOR . $filename;

            $tables = DB::select('SHOW TABLES');
            $tableKey = 'Tables_in_' . $dbName;
            $tableNames = [];

            foreach ($tables as $table) {
                if (isset($table->$tableKey)) {
                    $tableNames[] = $table->$tableKey;
                } else {
                    $arr = (array) $table;
                    $tableNames[] = reset($arr);
                }
            }

            $pdo = DB::connection()->getPdo();

            $handle = $compress ? gzopen($fullPath, 'w9') : fopen($fullPath, 'w');
            if (!$handle) {
                throw new Exception("Unable to create backup file at: {$fullPath}");
            }

            $write = function ($content) use ($handle, $compress) {
                if ($compress) {
                    gzwrite($handle, $content);
                } else {
                    fwrite($handle, $content);
                }
            };

            // Write SQL Header
            $write("-- ============================================================\n");
            $write("-- FrontField Credit Union - Complete Database Backup\n");
            $write("-- Generated at: " . Carbon::now()->toDateTimeString() . " UTC\n");
            $write("-- Database: `{$dbName}`\n");
            $write("-- Total Tables: " . count($tableNames) . "\n");
            $write("-- ============================================================\n\n");
            $write("SET FOREIGN_KEY_CHECKS=0;\n");
            $write("SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n");
            $write("SET time_zone = '+00:00';\n");
            $write("SET NAMES utf8mb4;\n\n");

            // Loop through each table
            foreach ($tableNames as $tableName) {
                // Table schema
                $write("-- ------------------------------------------------------------\n");
                $write("-- Table structure for `{$tableName}`\n");
                $write("-- ------------------------------------------------------------\n");
                $write("DROP TABLE IF EXISTS `{$tableName}`;\n");

                $createTableResult = DB::select("SHOW CREATE TABLE `{$tableName}`");
                if (!empty($createTableResult)) {
                    $createObj = (array) $createTableResult[0];
                    $createSql = $createObj['Create Table'] ?? reset($createObj);
                    $write($createSql . ";\n\n");
                }

                // Table data
                $write("-- Dumping data for `{$tableName}`\n");

                $totalRows = DB::table($tableName)->count();
                if ($totalRows > 0) {
                    DB::table($tableName)->orderBy(DB::raw('1'))->chunk(200, function ($rows) use ($write, $tableName, $pdo) {
                        $valuesArr = [];
                        foreach ($rows as $row) {
                            $rowValues = [];
                            foreach ((array) $row as $val) {
                                if (is_null($val)) {
                                    $rowValues[] = 'NULL';
                                } elseif (is_numeric($val) && !is_string($val)) {
                                    $rowValues[] = $val;
                                } else {
                                    $rowValues[] = $pdo->quote((string) $val);
                                }
                            }
                            $valuesArr[] = '(' . implode(', ', $rowValues) . ')';
                        }

                        if (!empty($valuesArr)) {
                            $write("INSERT INTO `{$tableName}` VALUES \n" . implode(",\n", $valuesArr) . ";\n");
                        }
                    });
                }
                $write("\n");
            }

            // Write SQL Footer
            $write("SET FOREIGN_KEY_CHECKS=1;\n");
            $write("-- ============================================================\n");
            $write("-- End of Backup: {$filename}\n");
            $write("-- ============================================================\n");

            if ($compress) {
                gzclose($handle);
            } else {
                fclose($handle);
            }

            $sizeBytes = File::size($fullPath);

            // Clean old backups older than 14 days
            $this->cleanOldBackups(14);

            return [
                'success' => true,
                'path' => $fullPath,
                'filename' => $filename,
                'size_bytes' => $sizeBytes,
                'size_formatted' => $this->formatBytes($sizeBytes),
                'tables_count' => count($tableNames),
                'error' => null,
            ];
        } catch (Exception $e) {
            Log::error('DatabaseBackupService Error: ' . $e->getMessage());
            return [
                'success' => false,
                'path' => null,
                'filename' => null,
                'size_bytes' => 0,
                'size_formatted' => '0 B',
                'tables_count' => 0,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send a generated backup file directly to Telegram.
     *
     * @param string|null $filePath
     * @param string|null $customMessage
     * @return array ['success' => bool, 'message' => string]
     */
    public function sendToTelegram(?string $filePath = null, ?string $customMessage = null): array
    {
        $botToken = Setting::get('telegram_bot_token', 'telegram') ?? config('services.telegram.bot_token');
        $chatId = Setting::get('telegram_chat_id', 'telegram') ?? config('services.telegram.chat_id');

        if (empty($botToken) || empty($chatId)) {
            return [
                'success' => false,
                'message' => __('Telegram Bot Token or Chat ID is not configured in Admin Settings.'),
            ];
        }

        // If no file path provided, generate a fresh compressed backup
        if (!$filePath || !File::exists($filePath)) {
            $dumpResult = $this->generateDump(true); // compress for telegram
            if (!$dumpResult['success']) {
                return [
                    'success' => false,
                    'message' => __('Database export failed: ') . $dumpResult['error'],
                ];
            }
            $filePath = $dumpResult['path'];
            $tablesCount = $dumpResult['tables_count'];
            $fileSizeFormatted = $dumpResult['size_formatted'];
        } else {
            $tablesCount = count(DB::select('SHOW TABLES'));
            $fileSizeFormatted = $this->formatBytes(File::size($filePath));
        }

        $filename = basename($filePath);
        $siteTitle = setting('site_title') ?? 'FrontField Credit Union';
        $timestamp = Carbon::now()->toDateTimeString();

        $caption = "💾 *{$siteTitle} - Database Backup*\n"
            . "━━━━━━━━━━━━━━━━━━━━\n"
            . "📅 *Date:* `{$timestamp} UTC`\n"
            . "📁 *File:* `{$filename}`\n"
            . "📊 *Total Tables:* `{$tablesCount}`\n"
            . "📦 *Size:* `{$fileSizeFormatted}`\n"
            . "🛡️ *Status:* `SUCCESS`\n"
            . ($customMessage ? "💬 *Note:* {$customMessage}\n" : "")
            . "━━━━━━━━━━━━━━━━━━━━";

        try {
            $fileContent = file_get_contents($filePath);
            $url = "https://api.telegram.org/bot{$botToken}/sendDocument";

            $response = Http::timeout(120)
                ->attach('document', $fileContent, $filename)
                ->post($url, [
                    'chat_id' => $chatId,
                    'caption' => $caption,
                    'parse_mode' => 'Markdown',
                ]);

            if ($response->successful() && ($response->json('ok') === true)) {
                return [
                    'success' => true,
                    'message' => __('Database backup successfully delivered to Telegram!'),
                ];
            } else {
                $err = $response->json('description') ?? $response->body();
                Log::error("Telegram Backup API Error: {$err}");
                return [
                    'success' => false,
                    'message' => __('Telegram API Error: ') . $err,
                ];
            }
        } catch (Exception $e) {
            Log::error('Telegram Backup Dispatch Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => __('Failed to send backup to Telegram: ') . $e->getMessage(),
            ];
        }
    }

    /**
     * Get a list of existing stored backup files.
     */
    public function getStoredBackups(): array
    {
        $dir = self::getBackupDirectory();
        $files = File::files($dir);
        $backups = [];

        foreach ($files as $file) {
            $name = $file->getFilename();
            if (str_ends_with($name, '.sql') || str_ends_with($name, '.sql.gz')) {
                $backups[] = [
                    'filename' => $name,
                    'path' => $file->getPathname(),
                    'size_bytes' => $file->getSize(),
                    'size_formatted' => $this->formatBytes($file->getSize()),
                    'created_at' => Carbon::createFromTimestamp($file->getMTime()),
                    'is_compressed' => str_ends_with($name, '.gz'),
                ];
            }
        }

        // Sort by newest first
        usort($backups, function ($a, $b) {
            return $b['created_at']->timestamp <=> $a['created_at']->timestamp;
        });

        return $backups;
    }

    /**
     * Clean backups older than specified days.
     */
    public function cleanOldBackups(int $keepDays = 14): void
    {
        try {
            $dir = self::getBackupDirectory();
            $files = File::files($dir);
            $threshold = Carbon::now()->subDays($keepDays)->timestamp;

            foreach ($files as $file) {
                if ($file->getMTime() < $threshold) {
                    File::delete($file->getPathname());
                }
            }
        } catch (Exception $e) {
            Log::warning('Failed cleaning old backups: ' . $e->getMessage());
        }
    }

    /**
     * Format bytes to human-readable size.
     */
    public function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
