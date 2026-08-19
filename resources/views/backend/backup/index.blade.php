@extends('backend.layouts.app')
@section('title')
    {{ __('Database Backup & Telegram Exporter') }}
@endsection
@section('content')
    <div class="main-content">
        <div class="page-title">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="title-content">
                            <h2 class="title">{{ __('Database Backup & Telegram Exporter') }}</h2>
                            <p class="text-muted mb-0">{{ __('Export complete database snapshots or automate weekly deliveries directly to your Telegram channel/bot.') }}</p>
                        </div>
                    </div>
                    <div class="col-auto">
                        <a href="{{ route('admin.backup.download') }}" class="title-btn me-2">
                            <i data-lucide="download"></i> {{ __('Download .SQL Now') }}
                        </a>
                        <a href="{{ route('admin.backup.download', ['compressed' => 1]) }}" class="title-btn btn-secondary">
                            <i data-lucide="file-archive"></i> {{ __('Download .SQL.GZ') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid">
            <!-- System Stats Overview -->
            <div class="row g-3 mb-4">
                <div class="col-xl-3 col-lg-6 col-md-6 col-12">
                    <div class="site-card h-100 p-4 border-0 shadow-sm" style="border-radius: 12px;">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm rounded-circle bg-primary bg-opacity-10 text-primary p-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i data-lucide="database" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <div class="text-muted small fw-semibold text-uppercase">{{ __('Database Name') }}</div>
                                <h4 class="mb-0 fw-bold text-dark">{{ $dbName }}</h4>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6 col-12">
                    <div class="site-card h-100 p-4 border-0 shadow-sm" style="border-radius: 12px;">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm rounded-circle bg-info bg-opacity-10 text-info p-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i data-lucide="table" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <div class="text-muted small fw-semibold text-uppercase">{{ __('Total Tables') }}</div>
                                <h4 class="mb-0 fw-bold text-dark">{{ $tablesCount }} {{ __('Tables') }}</h4>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6 col-12">
                    <div class="site-card h-100 p-4 border-0 shadow-sm" style="border-radius: 12px;">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm rounded-circle bg-success bg-opacity-10 text-success p-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i data-lucide="hard-drive" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <div class="text-muted small fw-semibold text-uppercase">{{ __('Est. Database Size') }}</div>
                                <h4 class="mb-0 fw-bold text-dark">{{ $dbSizeFormatted }}</h4>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6 col-12">
                    <div class="site-card h-100 p-4 border-0 shadow-sm" style="border-radius: 12px;">
                        <div class="d-flex align-items-center">
                            <div class="avatar-sm rounded-circle {{ $telegramEnabled ? 'bg-success text-success' : 'bg-warning text-warning' }} bg-opacity-10 p-3 me-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i data-lucide="send" class="w-6 h-6"></i>
                            </div>
                            <div>
                                <div class="text-muted small fw-semibold text-uppercase">{{ __('Telegram Backup') }}</div>
                                <h4 class="mb-0 fw-bold">
                                    @if($telegramEnabled)
                                        <span class="text-success">{{ __('Active') }}</span>
                                    @else
                                        <span class="text-warning">{{ __('Disabled') }}</span>
                                    @endif
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Telegram Weekly Backup Configuration Card -->
                <div class="col-xl-6 col-lg-12">
                    <div class="site-card h-100 border-0 shadow-sm" style="border-radius: 12px;">
                        <div class="site-card-header d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="title mb-1"><i data-lucide="send" class="me-2 text-primary"></i>{{ __('Telegram Automated Backup') }}</h4>
                                <p class="text-muted small mb-0">{{ __('Receive automated full SQL dumps directly in your Telegram inbox.') }}</p>
                            </div>
                        </div>
                        <div class="site-card-body p-4">
                            <form action="{{ route('admin.backup.telegram.settings') }}" method="POST">
                                @csrf
                                <div class="mb-4 d-flex align-items-center justify-content-between p-3 rounded" style="background: #f8f9fa; border: 1px solid #e9ecef;">
                                    <div>
                                        <label class="form-check-label fw-bold text-dark mb-0" for="telegram_backup_enabled">
                                            {{ __('Enable Automated Telegram Backups') }}
                                        </label>
                                        <div class="text-muted small">{{ __('When enabled, scheduled backups will automatically dispatch to your Telegram bot.') }}</div>
                                    </div>
                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input" type="checkbox" name="telegram_backup_enabled" id="telegram_backup_enabled" value="1" {{ $telegramEnabled ? 'checked' : '' }} style="width: 2.5em; height: 1.3em; cursor: pointer;">
                                    </div>
                                </div>

                                <div class="site-input-groups mb-3">
                                    <label class="box-input-label fw-semibold text-dark">{{ __('Telegram Bot Token') }}</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i data-lucide="key"></i></span>
                                        <input type="text" class="box-input form-control" name="telegram_bot_token" value="{{ $telegramBotToken }}" placeholder="e.g. 123456789:ABCdefGhIJKlmNoPQRsTUVwxyZ">
                                    </div>
                                    <div class="form-text small text-muted">
                                        {{ __('Create a bot via') }} <a href="https://t.me/BotFather" target="_blank" class="fw-bold text-primary">@BotFather</a> {{ __('and paste the HTTP API access token here.') }}
                                    </div>
                                </div>

                                <div class="site-input-groups mb-3">
                                    <label class="box-input-label fw-semibold text-dark">{{ __('Telegram Chat ID / Channel ID') }}</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i data-lucide="message-circle"></i></span>
                                        <input type="text" class="box-input form-control" name="telegram_chat_id" value="{{ $telegramChatId }}" placeholder="e.g. 987654321 or -1001234567890">
                                    </div>
                                    <div class="form-text small text-muted">
                                        {{ __('Your Telegram User ID or Channel/Group ID. Use') }} <a href="https://t.me/userinfobot" target="_blank" class="fw-bold text-primary">@userinfobot</a> {{ __('to obtain your ID.') }}
                                    </div>
                                </div>

                                <div class="site-input-groups mb-4">
                                    <label class="box-input-label fw-semibold text-dark">{{ __('Backup Frequency / Schedule') }}</label>
                                    <select class="box-input form-select" name="telegram_backup_schedule">
                                        <option value="weekly_sunday" {{ $telegramSchedule === 'weekly_sunday' ? 'selected' : '' }}>
                                            {{ __('Every Sunday at 11:59 PM UTC (Recommended)') }}
                                        </option>
                                        <option value="daily" {{ $telegramSchedule === 'daily' ? 'selected' : '' }}>
                                            {{ __('Every Day at 11:59 PM UTC') }}
                                        </option>
                                        <option value="every_3_days" {{ $telegramSchedule === 'every_3_days' ? 'selected' : '' }}>
                                            {{ __('Every 3 Days') }}
                                        </option>
                                    </select>
                                </div>

                                <div class="d-flex flex-wrap gap-2 pt-2">
                                    <button type="submit" class="site-btn-sm primary-btn px-4">
                                        <i data-lucide="save"></i> {{ __('Save Settings') }}
                                    </button>
                                </div>
                            </form>

                            <hr class="my-4">

                            <!-- Test Telegram Dispatch -->
                            <div class="d-flex justify-content-between align-items-center p-3 rounded" style="background: #eef2ff; border: 1px solid #c7d2fe;">
                                <div>
                                    <div class="fw-bold text-primary">{{ __('Test Telegram Connection') }}</div>
                                    <div class="text-muted small">{{ __('Generates a fresh backup and sends it immediately to your Telegram bot.') }}</div>
                                </div>
                                <form action="{{ route('admin.backup.telegram.test') }}" method="POST" class="m-0" onsubmit="this.querySelector('button').disabled=true; this.querySelector('button').innerHTML='<i class=\'fas fa-spinner fa-spin me-1\'></i> Sending...';">
                                    @csrf
                                    <button type="submit" class="site-btn-sm btn-primary text-white" style="white-space: nowrap;">
                                        <i data-lucide="send"></i> {{ __('Send Test Backup Now') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hostinger / Cron Integration & Manual Export Guide -->
                <div class="col-xl-6 col-lg-12">
                    <div class="site-card h-100 border-0 shadow-sm" style="border-radius: 12px;">
                        <div class="site-card-header">
                            <h4 class="title mb-1"><i data-lucide="clock" class="me-2 text-primary"></i>{{ __('Scheduled Web Cron Integration') }}</h4>
                            <p class="text-muted small mb-0">{{ __('Set up automated background triggers in Hostinger or any external cron service.') }}</p>
                        </div>
                        <div class="site-card-body p-4">
                            <p class="text-muted small mb-3">
                                {{ __('To automate the Sunday 11:59 PM backup delivery, configure a Cron Job in your Hostinger cPanel / Control Panel with the following settings:') }}
                            </p>

                            <div class="mb-3">
                                <label class="fw-bold text-dark small">{{ __('Direct Web Cron URL:') }}</label>
                                <div class="input-group">
                                    <input type="text" class="form-control bg-light" readonly value="{{ route('cron.backup') }}" id="cronBackupUrl">
                                    <button class="btn btn-outline-secondary" type="button" onclick="navigator.clipboard.writeText(document.getElementById('cronBackupUrl').value); alert('Cron URL copied to clipboard!');">
                                        <i data-lucide="copy"></i> {{ __('Copy') }}
                                    </button>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="fw-bold text-dark small">{{ __('Hostinger / Server Command:') }}</label>
                                <div class="p-3 bg-dark text-light rounded font-monospace small" style="word-break: break-all;">
                                    curl -s "{{ route('cron.backup') }}" >/dev/null 2>&1
                                </div>
                                <div class="text-muted small mt-1">
                                    {{ __('Schedule Expression for Sunday 11:59 PM:') }} <code class="fw-bold text-primary">59 23 * * 0</code>
                                </div>
                            </div>

                            <div class="alert alert-info border-0 rounded p-3 d-flex align-items-start" style="background: #f0fdf4; border-left: 4px solid #16a34a !important;">
                                <i data-lucide="shield-check" class="text-success me-2 flex-shrink-0 mt-1"></i>
                                <div class="small text-dark">
                                    <strong>{{ __('Pure PHP & Zero Server Lock-in') }}:</strong><br>
                                    {{ __('This backup engine uses native PDO streaming chunks. It never requires root terminal access, mysqldump binaries, or elevated exec() privileges, making it 100% stable on Hostinger shared hosting.') }}
                                </div>
                            </div>

                            <div class="alert alert-warning border-0 rounded p-3 d-flex align-items-start mt-3" style="background: #fffbeb; border-left: 4px solid #f59e0b !important;">
                                <i data-lucide="trash-2" class="text-warning me-2 flex-shrink-0 mt-1"></i>
                                <div class="small text-dark">
                                    <strong>{{ __('Automatic Storage Cleanup') }}:</strong><br>
                                    {{ __('Local backups older than 14 days are cleaned automatically during export to keep your hosting disk space optimal.') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Local Backups History Table -->
                <div class="col-12">
                    <div class="site-card border-0 shadow-sm" style="border-radius: 12px;">
                        <div class="site-card-header d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="title mb-1"><i data-lucide="folder-archive" class="me-2 text-primary"></i>{{ __('Generated Backups Archive') }}</h4>
                                <p class="text-muted small mb-0">{{ __('Recent database backups stored on the server.') }}</p>
                            </div>
                        </div>
                        <div class="site-card-body p-0">
                            <div class="site-table table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th scope="col">{{ __('File Name') }}</th>
                                            <th scope="col">{{ __('Format') }}</th>
                                            <th scope="col">{{ __('Size') }}</th>
                                            <th scope="col">{{ __('Created Date') }}</th>
                                            <th scope="col" class="text-end pe-4">{{ __('Actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($backups as $backup)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <i data-lucide="{{ $backup['is_compressed'] ? 'file-archive' : 'file-code' }}" class="text-primary me-2"></i>
                                                        <strong class="text-dark">{{ $backup['filename'] }}</strong>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge {{ $backup['is_compressed'] ? 'badge-primary' : 'badge-secondary' }} px-2 py-1">
                                                        {{ $backup['is_compressed'] ? 'GZIP Compressed (.sql.gz)' : 'Plain SQL (.sql)' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <strong>{{ $backup['size_formatted'] }}</strong>
                                                </td>
                                                <td>
                                                    {{ $backup['created_at']->format('M d, Y h:i A') }} <br>
                                                    <span class="text-muted small">({{ $backup['created_at']->diffForHumans() }})</span>
                                                </td>
                                                <td class="text-end pe-4">
                                                    <a href="{{ route('admin.backup.download.file', $backup['filename']) }}" class="site-btn-sm primary-btn me-2" title="{{ __('Download') }}">
                                                        <i data-lucide="download"></i> {{ __('Download') }}
                                                    </a>
                                                    <form action="{{ route('admin.backup.delete.file', $backup['filename']) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Are you sure you want to delete this backup file?') }}');">
                                                        @csrf
                                                        <button type="submit" class="site-btn-sm red-btn" title="{{ __('Delete') }}">
                                                            <i data-lucide="trash-2"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-5">
                                                    <div class="text-muted">
                                                        <i data-lucide="database" class="w-12 h-12 mb-2 opacity-50"></i>
                                                        <p class="mb-0">{{ __('No stored backups found in the archive yet.') }}</p>
                                                        <small>{{ __('Click "Download .SQL Now" or trigger a Telegram backup above to generate your first backup.') }}</small>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
