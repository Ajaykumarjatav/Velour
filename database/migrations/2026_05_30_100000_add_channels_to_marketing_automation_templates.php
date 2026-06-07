<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketing_automation_templates', function (Blueprint $table) {
            $table->boolean('channel_email')->default(true)->after('is_active');
            $table->boolean('channel_sms')->default(false)->after('channel_email');
            $table->boolean('channel_whatsapp')->default(false)->after('channel_sms');
            $table->text('whatsapp_body')->nullable()->after('email_body');
        });

        if (Schema::hasTable('marketing_automation_templates')) {
            DB::table('marketing_automation_templates')
                ->where('template_key', 'booking_confirmation')
                ->update([
                    'channels_label'   => 'Email + WhatsApp',
                    'channel_email'    => true,
                    'channel_whatsapp' => true,
                    'channel_sms'      => false,
                    'whatsapp_body'    => "Hi {{client_first_name}}, your appointment at {{salon_name}} is confirmed for {{appointment_date}} at {{appointment_time}}.\nServices: {{service_names}}\nRef: {{reference}}",
                ]);

            DB::table('marketing_automation_templates')
                ->where('template_key', 'appointment_reminder')
                ->update([
                    'channel_email' => true,
                    'channel_sms'   => true,
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('marketing_automation_templates', function (Blueprint $table) {
            $table->dropColumn(['channel_email', 'channel_sms', 'channel_whatsapp', 'whatsapp_body']);
        });
    }
};
