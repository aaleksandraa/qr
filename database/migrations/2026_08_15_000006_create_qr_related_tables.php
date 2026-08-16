<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qr_destination_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qr_code_id')->constrained()->cascadeOnDelete();
            $table->text('old_url')->nullable();
            $table->text('new_url');
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['qr_code_id', 'created_at']);
        });

        Schema::create('qr_redirect_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qr_code_id')->constrained()->cascadeOnDelete();
            $table->string('type', 32);
            $table->string('operator', 32)->default('equals');
            $table->json('configuration');
            $table->text('destination_url')->nullable();
            $table->unsignedInteger('priority')->default(100);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['qr_code_id', 'is_active', 'priority']);
        });

        Schema::create('qr_scans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qr_code_id')->constrained()->cascadeOnDelete();
            $table->timestamp('scanned_at')->useCurrent();
            $table->string('visitor_hash', 64)->nullable();
            $table->string('country_code', 2)->nullable();
            $table->string('country_name', 100)->nullable();
            $table->string('region', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('device_type', 32)->nullable();
            $table->string('os', 32)->nullable();
            $table->string('browser', 32)->nullable();
            $table->string('referrer', 512)->nullable();
            $table->string('user_agent_summary', 255)->nullable();
            $table->boolean('is_bot')->default(false);
            $table->string('ab_variant', 32)->nullable();
            $table->ulid('request_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['qr_code_id', 'scanned_at']);
            $table->index(['qr_code_id', 'is_bot']);
            $table->index(['qr_code_id', 'country_code']);
            $table->index(['qr_code_id', 'visitor_hash']);
        });

        Schema::create('qr_scan_daily_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qr_code_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->unsignedInteger('total_scans')->default(0);
            $table->unsignedInteger('human_scans')->default(0);
            $table->unsignedInteger('bot_scans')->default(0);
            $table->unsignedInteger('unique_scans')->default(0);
            $table->timestamps();

            $table->unique(['qr_code_id', 'date']);
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('auditable_type');
            $table->unsignedBigInteger('auditable_id');
            $table->string('action', 64);
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->timestamps();

            $table->index(['auditable_type', 'auditable_id']);
            $table->index(['workspace_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('qr_scan_daily_stats');
        Schema::dropIfExists('qr_scans');
        Schema::dropIfExists('qr_redirect_rules');
        Schema::dropIfExists('qr_destination_history');
    }
};
