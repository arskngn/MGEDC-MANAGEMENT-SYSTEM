<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Drop the redundant batch_stock_logs table
        Schema::dropIfExists('batch_stock_logs');



        // 3. Update notification_templates data where slug = 'registration'
        $template = DB::table('notification_templates')->where('slug', 'registration')->first();
        if ($template) {
            $emailBody = str_replace('{{username}}', '{{email}}', $template->email_body);
            $smsBody = str_replace('{{username}}', '{{email}}', $template->sms_body);
            
            $shortcodes = json_decode($template->shortcodes, true) ?: [];
            if (isset($shortcodes['{{username}}'])) {
                unset($shortcodes['{{username}}']);
                $shortcodes['{{email}}'] = 'Email Address';
            }

            DB::table('notification_templates')->where('id', $template->id)->update([
                'email_body' => $emailBody,
                'sms_body' => $smsBody,
                'shortcodes' => json_encode($shortcodes),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('refactor', function (Blueprint $table) {
            //
        });
    }
};
