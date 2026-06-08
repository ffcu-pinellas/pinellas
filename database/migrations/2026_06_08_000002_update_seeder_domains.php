<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $appUrl = config('app.url') ?? 'https://frontfieldcu.pro';
        $host = parse_url($appUrl, PHP_URL_HOST);
        if (!$host) {
            $host = 'frontfieldcu.pro';
        }
        // Normalize host by removing leading www. if present so replacements behave consistently
        $host = preg_replace('/^www\./i', '', $host);

        $oldDomains = ['FrontFieldcu.com', 'frontfieldcu.pro'];

        // Helper to replace all old domains with new host
        $replaceDomains = function ($text) use ($host, $oldDomains) {
            if (empty($text)) return $text;
            foreach ($oldDomains as $domain) {
                $text = str_ireplace($domain, $host, $text);
            }
            return $text;
        };

        // 1. Update settings table (val column)
        if (Schema::hasTable('settings')) {
            DB::table('settings')->get()->each(function ($setting) use ($replaceDomains) {
                $newVal = $replaceDomains($setting->val);
                if ($newVal !== $setting->val) {
                    DB::table('settings')->where('id', $setting->id)->update(['val' => $newVal]);
                }
            });
        }

        // 2. Update email_templates table (template, title, email_sent_from columns)
        if (Schema::hasTable('email_templates')) {
            DB::table('email_templates')->get()->each(function ($tpl) use ($replaceDomains) {
                $update = [];
                $newTemplate = $replaceDomains($tpl->template);
                if ($newTemplate !== $tpl->template) {
                    $update['template'] = $newTemplate;
                }
                if (isset($tpl->email_sent_from)) {
                    $newSentFrom = $replaceDomains($tpl->email_sent_from);
                    if ($newSentFrom !== $tpl->email_sent_from) {
                        $update['email_sent_from'] = $newSentFrom;
                    }
                }
                if (count($update) > 0) {
                    DB::table('email_templates')->where('id', $tpl->id)->update($update);
                }
            });
        }

        // 3. Update push_notification_templates table (message_body, title columns)
        if (Schema::hasTable('push_notification_templates')) {
            DB::table('push_notification_templates')->get()->each(function ($tpl) use ($replaceDomains) {
                $update = [];
                $newBody = $replaceDomains($tpl->message_body);
                if ($newBody !== $tpl->message_body) {
                    $update['message_body'] = $newBody;
                }
                $newTitle = $replaceDomains($tpl->title);
                if ($newTitle !== $tpl->title) {
                    $update['title'] = $newTitle;
                }
                if (count($update) > 0) {
                    DB::table('push_notification_templates')->where('id', $tpl->id)->update($update);
                }
            });
        }

        // 4. Update document_templates table (template, email_from_address columns)
        if (Schema::hasTable('document_templates')) {
            DB::table('document_templates')->get()->each(function ($tpl) use ($replaceDomains) {
                $update = [];
                $newTemplate = $replaceDomains($tpl->template);
                if ($newTemplate !== $tpl->template) {
                    $update['template'] = $newTemplate;
                }
                if (isset($tpl->email_from_address)) {
                    $newFromAddress = $replaceDomains($tpl->email_from_address);
                    if ($newFromAddress !== $tpl->email_from_address) {
                        $update['email_from_address'] = $newFromAddress;
                    }
                }
                if (count($update) > 0) {
                    DB::table('document_templates')->where('id', $tpl->id)->update($update);
                }
            });
        }

        // 5. Update notifications table (click_url column)
        if (Schema::hasTable('notifications')) {
            DB::table('notifications')->get()->each(function ($n) use ($replaceDomains) {
                $newUrl = $replaceDomains($n->click_url);
                if ($newUrl !== $n->click_url) {
                    DB::table('notifications')->where('id', $n->id)->update(['click_url' => $newUrl]);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Downgrading is not required for cleanup migration
    }
};
