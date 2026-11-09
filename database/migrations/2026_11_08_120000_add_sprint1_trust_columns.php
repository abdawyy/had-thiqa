<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('due_at')->nullable()->after('due_label');
            $table->foreignId('referrer_id')->nullable()->after('referral_code')->constrained('users')->nullOnDelete();
            $table->timestamp('partner_approved_at')->nullable()->after('partner_name');
            $table->text('partner_note')->nullable()->after('partner_approved_at');
        });

        Schema::table('skills', function (Blueprint $table) {
            $table->timestamp('reviewed_at')->nullable()->after('status');
        });

        Schema::table('tickets', function (Blueprint $table) {
            $table->string('status')->default('open')->after('body');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('skills', function (Blueprint $table) {
            $table->dropColumn('reviewed_at');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('referrer_id');
            $table->dropColumn(['due_at', 'partner_approved_at', 'partner_note']);
        });
    }
};
