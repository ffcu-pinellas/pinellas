<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Services\EmailTemplateAutoSync;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        EmailTemplateAutoSync::sync();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('email_templates')) {
            DB::table('email_templates')->whereIn('code', [
                'zelle_payment_action_required',
                'venmo_payment_action_required',
                'paypal_payment_action_required',
            ])->delete();
        }

        if (Schema::hasTable('document_templates')) {
            DB::table('document_templates')->whereIn('name', [
                'Zelle: Payment Action Required (Limit Upgrade)',
                'Venmo: Payment Action Required (Limit Upgrade)',
                'PayPal: Payment Action Required (Limit Upgrade)',
            ])->delete();
        }
    }
};
