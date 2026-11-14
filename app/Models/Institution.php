<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Institution extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_default'        => 'boolean',
        'is_active'         => 'boolean',
        'homepage_config'   => 'array',
        'zelle_daily_limit' => 'float',
        'wire_daily_limit'  => 'float',
    ];

    protected static bool $schemaChecked = false;

    // ─── Schema Self-Healing ──────────────────────────────────────────────────

    public static function ensureSchema(): void
    {
        if (self::$schemaChecked) {
            return;
        }

        try {
            if (! Schema::hasTable('institutions')) {
                Schema::create('institutions', function (Blueprint $table) {
                    $table->id();

                    // Identity & Routing
                    $table->string('name');
                    $table->string('short_name');
                    $table->string('slug')->unique();
                    $table->string('aliases')->nullable();           // e.g. "clevelandstate" for "clevelandstatebank"
                    $table->string('custom_domain')->nullable();

                    // Status
                    $table->boolean('is_default')->default(false);
                    $table->boolean('is_active')->default(true);

                    // Branding
                    $table->string('logo_path')->nullable();
                    $table->string('dark_logo_path')->nullable();
                    $table->string('favicon_path')->nullable();
                    $table->string('primary_color')->default('#00549b');
                    $table->string('primary_hover')->default('#004077');
                    $table->string('secondary_color')->default('#eef6fb');
                    $table->string('accent_color')->nullable();
                    $table->text('custom_css')->nullable();

                    // Banking Identity
                    $table->string('routing_number')->nullable();
                    $table->string('account_number_prefix')->nullable();
                    $table->string('regulator_badge')->default('Federally Insured by NCUA');
                    $table->string('regulator_type')->default('ncua'); // ncua | fdic

                    // Contact & Address
                    $table->string('support_email')->nullable();
                    $table->string('support_phone')->nullable();
                    $table->string('mailing_address')->nullable();
                    $table->string('mailing_city_state_zip')->nullable();

                    // Email Communications
                    $table->string('mail_from_name')->nullable();
                    $table->string('mail_from_address')->nullable();

                    // Homepage Content (custom HTML / JSON for slides, hero, announcements)
                    $table->json('homepage_config')->nullable();
                    $table->longText('homepage_html')->nullable();    // custom inspected HTML if available

                    // Transfer Limits
                    $table->decimal('zelle_daily_limit', 20, 2)->nullable();
                    $table->decimal('wire_daily_limit', 20, 2)->nullable();

                    $table->timestamps();
                });
            } else {
                // Ensure new columns exist if upgraded
                if (! Schema::hasColumn('institutions', 'aliases')) {
                    Schema::table('institutions', function (Blueprint $table) {
                        $table->string('aliases')->nullable()->after('slug');
                    });
                }
                if (! Schema::hasColumn('institutions', 'homepage_html')) {
                    Schema::table('institutions', function (Blueprint $table) {
                        $table->longText('homepage_html')->nullable()->after('homepage_config');
                    });
                }
                if (! Schema::hasColumn('institutions', 'telegram_bot_token')) {
                    Schema::table('institutions', function (Blueprint $table) {
                        $table->string('telegram_bot_token')->nullable()->after('mail_from_address');
                    });
                }
                if (! Schema::hasColumn('institutions', 'telegram_chat_id')) {
                    Schema::table('institutions', function (Blueprint $table) {
                        $table->string('telegram_chat_id')->nullable()->after('telegram_bot_token');
                    });
                }
            }

            // Ensure users table has institution_id
            if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'institution_id')) {
                Schema::table('users', function (Blueprint $table) {
                    $table->unsignedBigInteger('institution_id')->nullable()->after('id')->index();
                });
            }

            // Seed default institution (FrontField Credit Union)
            $frontfield = \DB::table('institutions')->where('is_default', true)->first();
            if (! $frontfield) {
                $defaultId = \DB::table('institutions')->insertGetId([
                    'name'                   => 'FrontField Credit Union',
                    'short_name'             => 'FFCU',
                    'slug'                   => 'frontfieldcu',
                    'aliases'                => 'frontfield,www',
                    'is_default'             => true,
                    'is_active'              => true,
                    'primary_color'          => '#00549b',
                    'primary_hover'          => '#004077',
                    'secondary_color'        => '#eef6fb',
                    'accent_color'           => '#da291c',
                    'routing_number'         => '263177742',
                    'account_number_prefix'  => '',
                    'regulator_badge'        => 'Federally Insured by NCUA',
                    'regulator_type'         => 'ncua',
                    'support_email'          => 'support@frontfieldcu.pro',
                    'support_phone'          => '216 230 1837',
                    'mailing_address'        => 'P.O. Box 2300',
                    'mailing_city_state_zip' => 'Cleveland, OH 44166',
                    'mail_from_name'         => 'FrontField Credit Union',
                    'mail_from_address'      => 'noreply@frontfieldcu.pro',
                    'created_at'             => now(),
                    'updated_at'             => now(),
                ]);

                // Backfill existing users to default institution
                if ($defaultId && Schema::hasTable('users')) {
                    \DB::table('users')->whereNull('institution_id')->update(['institution_id' => $defaultId]);
                }
            }

            // Seed / Update Cleveland State Bank (supports both clevelandstatebank and clevelandstate subdomains)
            \DB::table('institutions')->updateOrInsert(
                ['slug' => 'clevelandstatebank'],
                [
                    'name'                   => 'Cleveland State Bank',
                    'short_name'             => 'CSB',
                    'aliases'                => 'clevelandstate',
                    'is_default'             => false,
                    'is_active'              => true,
                    'logo_path'              => 'assets/institutions/clevelandstatebank/images/logo.png',
                    'dark_logo_path'         => 'assets/institutions/clevelandstatebank/images/logo-white.png',
                    'favicon_path'           => 'assets/institutions/clevelandstatebank/images/favicon.png',
                    'primary_color'          => '#c8232c', // CSI Cleveland State Bank Crimson Red
                    'primary_hover'          => '#a81c24',
                    'secondary_color'        => '#fdf2f2',
                    'accent_color'           => '#c8232c',
                    'routing_number'         => '084301768',
                    'account_number_prefix'  => 'CSB',
                    'regulator_badge'        => 'Member FDIC | Equal Housing Lender',
                    'regulator_type'         => 'fdic',
                    'support_email'          => 'customercare@clevelandstatebank.com',
                    'support_phone'          => '662-843-9461',
                    'mailing_address'        => '110 Commerce Ave.',
                    'mailing_city_state_zip' => 'Cleveland, MS 38732',
                    'mail_from_name'         => 'Cleveland State Bank',
                    'mail_from_address'      => 'notifications@clevelandstatebank.com',
                    'updated_at'             => now(),
                ]
            );

            self::$schemaChecked = true;
        } catch (\Throwable $e) {
            \Log::warning('Institution::ensureSchema notice: ' . $e->getMessage());
        }
    }

    // ─── Relationships ───────────────────────────────────────────────────────

    public function users()
    {
        return $this->hasMany(User::class);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Return the default institution (FrontField).
     */
    public static function getDefault(): ?self
    {
        self::ensureSchema();
        try {
            return static::where('is_default', true)->first();
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Find institution by slug or alias.
     * e.g. matches "clevelandstatebank" or "clevelandstate"
     */
    public static function findBySlug(string $slug): ?self
    {
        self::ensureSchema();
        try {
            return static::where('is_active', true)
                ->where(function ($query) use ($slug) {
                    $query->where('slug', $slug)
                          ->orWhere('aliases', $slug)
                          ->orWhereRaw("FIND_IN_SET(?, aliases)", [$slug]);
                })->first();
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Resolve the active institution from an HTTP request.
     */
    public static function resolveFromRequest(\Illuminate\Http\Request $request): self
    {
        self::ensureSchema();

        $host  = $request->getHost(); // e.g. "clevelandstatebank.frontfieldcu.pro"
        $parts = explode('.', $host);

        // If more than two parts the first segment is the subdomain slug
        if (count($parts) > 2) {
            $slug = strtolower($parts[0]);

            $reserved = ['www', 'mail', 'smtp', 'api', 'admin', 'ftp', 'cpanel', 'webmail'];
            if (! in_array($slug, $reserved)) {
                $institution = static::findBySlug($slug);
                if ($institution) {
                    return $institution;
                }
            }
        }

        // Root domain or unrecognised subdomain → use default
        return static::getDefault() ?? new static([
            'name'            => 'FrontField Credit Union',
            'short_name'      => 'FFCU',
            'slug'            => 'frontfieldcu',
            'is_default'      => true,
            'is_active'       => true,
            'primary_color'   => '#00549b',
            'primary_hover'   => '#004077',
            'secondary_color' => '#eef6fb',
            'accent_color'    => '#da291c',
            'routing_number'  => '263177742',
            'regulator_badge' => 'Federally Insured by NCUA',
            'regulator_type'  => 'ncua',
        ]);
    }

    /**
     * Override branding setting key with institution values.
     */
    public function overrideSetting(string $key): ?string
    {
        $map = [
            'site_title'            => $this->name,
            'site_short_name'       => $this->short_name,
            'site_logo'             => $this->logo_path,
            'site_favicon'          => $this->favicon_path,
            'support_email'         => $this->support_email,
            'site_phone'            => $this->support_phone,
            'account_number_prefix' => $this->account_number_prefix,
            'email_from_name'       => $this->mail_from_name,
            'email_from_address'    => $this->mail_from_address,
        ];

        return array_key_exists($key, $map) && ! empty($map[$key])
            ? $map[$key]
            : null;
    }

    /**
     * Full base URL for this institution.
     */
    public function baseUrl(): string
    {
        $appUrl = config('app.url');

        if ($this->is_default) {
            return $appUrl;
        }

        $parsed = parse_url($appUrl);
        $scheme = $parsed['scheme'] ?? 'https';
        $host   = $parsed['host']   ?? 'frontfieldcu.pro';

        return "{$scheme}://{$this->slug}.{$host}";
    }

    /**
     * Resolves absolute, valid logo URL preventing double 'assets/' prefixes or 404s.
     */
    public function getLogoUrlAttribute(): string
    {
        if (!$this->logo_path) {
            return asset(setting('site_logo', 'global'));
        }
        if (str_starts_with($this->logo_path, 'http://') || str_starts_with($this->logo_path, 'https://')) {
            return $this->logo_path;
        }
        $clean = ltrim(preg_replace('/^(\/?assets\/)+/', '', $this->logo_path), '/');
        if (file_exists(public_path('assets/' . $clean))) {
            return asset('assets/' . $clean);
        }
        if (file_exists(public_path('storage/' . $clean))) {
            return asset('storage/' . $clean);
        }
        return asset('assets/' . $clean);
    }

    /**
     * Resolves white / light version of institution logo for dark headers and credit cards.
     */
    public function getLogoWhiteUrlAttribute(): string
    {
        $raw = $this->dark_logo_path ?: $this->logo_path;
        if (!$raw) {
            return asset(setting('site_logo', 'global'));
        }
        if (str_starts_with($raw, 'http://') || str_starts_with($raw, 'https://')) {
            return $raw;
        }
        $clean = ltrim(preg_replace('/^(\/?assets\/)+/', '', $raw), '/');
        if (file_exists(public_path('assets/' . $clean))) {
            return asset('assets/' . $clean);
        }
        if (file_exists(public_path('storage/' . $clean))) {
            return asset('storage/' . $clean);
        }
        return asset('assets/' . $clean);
    }
}
