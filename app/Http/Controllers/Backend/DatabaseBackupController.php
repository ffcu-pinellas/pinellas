<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\DatabaseBackupService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DatabaseBackupController extends Controller
{
    protected DatabaseBackupService $backupService;

    public function __construct(DatabaseBackupService $backupService)
    {
        $this->backupService = $backupService;
    }

    /**
     * Display the database backup management page.
     */
    public function index()
    {
        $dbName = config('database.connections.mysql.database');
        
        // Total tables count
        $tables = DB::select('SHOW TABLES');
        $tablesCount = count($tables);

        // Calculate total database size from information_schema
        $dbSizeResult = DB::select("
            SELECT SUM(data_length + index_length) AS db_size 
            FROM information_schema.TABLES 
            WHERE table_schema = ?
        ", [$dbName]);

        $dbSizeBytes = $dbSizeResult[0]->db_size ?? 0;
        $dbSizeFormatted = $this->backupService->formatBytes((int) $dbSizeBytes);

        // Stored backup files
        $backups = $this->backupService->getStoredBackups();

        // Telegram Settings
        $telegramEnabled = (bool) Setting::get('telegram_backup_enabled', 'telegram');
        $telegramBotToken = Setting::get('telegram_bot_token', 'telegram') ?? '';
        $telegramChatId = Setting::get('telegram_chat_id', 'telegram') ?? '';
        $telegramSchedule = Setting::get('telegram_backup_schedule', 'telegram') ?? 'weekly_sunday';

        return view('backend.backup.index', compact(
            'dbName',
            'tablesCount',
            'dbSizeBytes',
            'dbSizeFormatted',
            'backups',
            'telegramEnabled',
            'telegramBotToken',
            'telegramChatId',
            'telegramSchedule'
        ));
    }

    /**
     * Generate and instantly stream a fresh backup file to the browser.
     */
    public function download(Request $request)
    {
        $compress = $request->boolean('compressed', false);
        $result = $this->backupService->generateDump($compress);

        if (!$result['success']) {
            notify()->error(__('Failed to export database: ') . $result['error'], __('Error'));
            return back();
        }

        return response()->download($result['path'], $result['filename']);
    }

    /**
     * Download a previously generated backup file.
     */
    public function downloadFile(string $filename): BinaryFileResponse|RedirectResponse
    {
        // Sanitize filename
        $safeFilename = basename($filename);
        $filePath = DatabaseBackupService::getBackupDirectory() . DIRECTORY_SEPARATOR . $safeFilename;

        if (!File::exists($filePath)) {
            notify()->error(__('Requested backup file not found.'), __('Error'));
            return back();
        }

        return response()->download($filePath, $safeFilename);
    }

    /**
     * Delete a stored backup file.
     */
    public function deleteFile(string $filename): RedirectResponse
    {
        $safeFilename = basename($filename);
        $filePath = DatabaseBackupService::getBackupDirectory() . DIRECTORY_SEPARATOR . $safeFilename;

        if (File::exists($filePath)) {
            File::delete($filePath);
            notify()->success(__('Backup file deleted successfully.'), __('Success'));
        } else {
            notify()->error(__('Backup file not found.'), __('Error'));
        }

        return back();
    }

    /**
     * Save Telegram automated backup settings.
     */
    public function updateTelegramSettings(Request $request): RedirectResponse
    {
        $request->validate([
            'telegram_bot_token' => 'nullable|string|max:255',
            'telegram_chat_id' => 'nullable|string|max:255',
            'telegram_backup_schedule' => 'required|string|in:weekly_sunday,daily,every_3_days',
        ]);

        $enabled = $request->has('telegram_backup_enabled') ? '1' : '0';

        Setting::updateOrCreate(
            ['name' => 'telegram_backup_enabled'],
            ['val' => $enabled]
        );

        Setting::updateOrCreate(
            ['name' => 'telegram_bot_token'],
            ['val' => (string) $request->telegram_bot_token]
        );

        Setting::updateOrCreate(
            ['name' => 'telegram_chat_id'],
            ['val' => (string) $request->telegram_chat_id]
        );

        Setting::updateOrCreate(
            ['name' => 'telegram_backup_schedule'],
            ['val' => (string) $request->telegram_backup_schedule]
        );

        notify()->success(__('Telegram backup configuration saved successfully!'), __('Success'));
        return back();
    }

    /**
     * Test Telegram connection by generating and sending a live database backup.
     */
    public function testTelegramBackup(): RedirectResponse
    {
        $result = $this->backupService->sendToTelegram(null, __('Manual test dispatched from FrontField Admin Panel.'));

        if ($result['success']) {
            notify()->success($result['message'], __('Success'));
        } else {
            notify()->error($result['message'], __('Error'));
        }

        return back();
    }
}
