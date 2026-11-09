<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('letter', 8)->default('؟');
            $table->string('role')->default('worker')->index();
            $table->string('tier')->default('beginner');
            $table->unsignedTinyInteger('level')->default(1);
            $table->string('level_name')->default('ابتداء');
            $table->unsignedInteger('points')->default(0);
            $table->decimal('rating', 3, 1)->nullable();
            $table->string('jobs_text')->nullable();
            $table->boolean('is_available')->default(true);
            $table->boolean('is_new')->default(false);
            $table->unsignedInteger('balance')->default(0);
            $table->unsignedInteger('withdrawable')->default(0);
            $table->unsignedInteger('pending')->default(0);
            $table->unsignedInteger('jobs_done')->default(0);
            $table->unsignedInteger('jobs_clean')->default(0);
            $table->boolean('speed_badge')->default(false);
            $table->unsignedInteger('streak')->default(0);
            $table->string('referral_code')->nullable();
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('sample');
            $table->unsignedInteger('xp');
            $table->unsignedInteger('price');
            $table->string('duration');
            $table->unsignedTinyInteger('min_level')->default(1);
            $table->boolean('available')->default(true);
            $table->timestamps();
        });

        Schema::create('skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('level_label');
            $table->unsignedTinyInteger('progress')->default(0);
            $table->string('status')->default('pending');
            $table->timestamps();
        });

        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('client_name');
            $table->string('meta');
            $table->string('duration');
            $table->string('due_label');
            $table->unsignedInteger('xp');
            $table->unsignedTinyInteger('min_level')->default(1);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('number')->unique();
            $table->foreignId('service_id')->constrained();
            $table->foreignId('worker_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('client_name');
            $table->string('title');
            $table->text('description');
            $table->string('speed')->default('normal');
            $table->string('detail_level')->default('pro');
            $table->unsignedInteger('base_price');
            $table->unsignedTinyInteger('discount_percent')->default(0);
            $table->unsignedInteger('tracking_fee')->default(0);
            $table->unsignedInteger('total');
            $table->string('duration');
            $table->string('due_label');
            $table->unsignedInteger('xp');
            $table->unsignedTinyInteger('pipeline_stage')->default(0);
            $table->string('payment_method')->nullable();
            $table->string('payment_status')->default('unpaid');
            $table->string('source');
            $table->string('partner_name');
            $table->boolean('voice_note')->default(false);
            $table->json('photos')->nullable();
            $table->string('referral_code')->nullable();
            $table->json('quality_checks')->nullable();
            $table->boolean('on_time')->default(true);
            $table->timestamps();
        });

        Schema::create('order_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('position');
            $table->string('kind');
            $table->string('title');
            $table->text('description');
            $table->string('cta')->nullable();
            $table->string('done_label');
            $table->json('preview')->nullable();
            $table->json('result')->nullable();
            $table->text('note')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('quality');
            $table->unsignedTinyInteger('price_score');
            $table->unsignedTinyInteger('dealing');
            $table->text('note')->nullable();
            $table->string('author_name');
            $table->timestamps();
        });

        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('topic');
            $table->text('body');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
        Schema::dropIfExists('ratings');
        Schema::dropIfExists('order_steps');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('offers');
        Schema::dropIfExists('skills');
        Schema::dropIfExists('services');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'letter', 'role', 'tier', 'level', 'level_name', 'points', 'rating',
                'jobs_text', 'is_available', 'is_new', 'balance', 'withdrawable',
                'pending', 'jobs_done', 'jobs_clean', 'speed_badge', 'streak',
                'referral_code',
            ]);
        });
    }
};
