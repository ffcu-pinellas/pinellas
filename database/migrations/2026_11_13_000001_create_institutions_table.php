<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('institutions')) {
            Schema::create('institutions', function (Blueprint $table) {
                $table->id();

                // Identity & Routing
                $table->string('name');                          // "Cleveland State Bank"
                $table->string('short_name');                    // "CSB"
                $table->string('slug')->unique();                // "clevelandstatebank" (matches subdomain)
                $table->string('custom_domain')->nullable();     // future: custom domain support

                // Status
                $table->boolean('is_default')->default(false);  // only one row is true (FrontField)
                $table->boolean('is_active')->default(true);

                // Branding
                $table->string('logo_path')->nullable();
                $table->string('dark_logo_path')->nullable();
                $table->string('favicon_path')->nullable();
                $table->string('primary_color')->default('#00549b');
                $table->string('primary_hover')->default('#004077');
                $table->string('secondary_color')->default('#eef6fb');
                $table->string('accent_color')->nullable();      // e.g. button red
                $table->text('custom_css')->nullable();

                // Banking Identity
                $table->string('routing_number')->nullable();
                $table->string('account_number_prefix')->nullable();
                $table->string('regulator_badge')->default('Federally Insured by NCUA'); // or "Member FDIC"
                $table->string('regulator_type')->default('ncua'); // ncua | fdic

                // Contact & Address
                $table->string('support_email')->nullable();
                $table->string('support_phone')->nullable();
                $table->string('mailing_address')->nullable();
                $table->string('mailing_city_state_zip')->nullable();

                // Email Communications
                $table->string('mail_from_name')->nullable();
                $table->string('mail_from_address')->nullable();

                // Homepage Content (JSON for flexibility)
                $table->json('homepage_config')->nullable();     // headlines, slides, promos, footer text

                // Zelle / Transfer Limits (optional overrides per institution)
                $table->decimal('zelle_daily_limit', 20, 2)->nullable();
                $table->decimal('wire_daily_limit', 20, 2)->nullable();

                $table->timestamps();
            });
        }

        // Seed FrontField as the default institution immediately
        if (\DB::table('institutions')->count() === 0) {
            \DB::table('institutions')->insert([
                'name'                   => 'FrontField Credit Union',
                'short_name'             => 'FFCU',
                'slug'                   => 'frontfieldcu',
                'is_default'             => true,
                'is_active'              => true,
                'primary_color'          => '#00549b',
                'primary_hover'          => '#004077',
                'secondary_color'        => '#eef6fb',
                'accent_color'           => '#da291c',
                'routing_number'         => '263177742',
                'account_number_prefix'  => setting('account_number_prefix', 'global') ?? '',
                'regulator_badge'        => 'Federally Insured by NCUA',
                'regulator_type'         => 'ncua',
                'support_email'          => setting('support_email', 'global') ?? '',
                'support_phone'          => setting('site_phone', 'global') ?? '',
                'mailing_address'        => 'P.O. Box 2300',
                'mailing_city_state_zip' => 'Cleveland, OH 44166',
                'mail_from_name'         => setting('email_from_name', 'mail') ?? 'FrontField Credit Union',
                'mail_from_address'      => setting('email_from_address', 'mail') ?? '',
                'created_at'             => now(),
                'updated_at'             => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('institutions');
    }
};

