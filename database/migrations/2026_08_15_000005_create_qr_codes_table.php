<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qr_codes', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('folder_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('campaign_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('custom_domain_id')->nullable()->constrained()->nullOnDelete();

            $table->string('name');
            $table->text('description')->nullable();
            $table->string('qr_type', 16);
            $table->string('content_type', 32)->nullable();
            $table->string('slug', 64)->nullable();
            $table->text('destination_url')->nullable();
            $table->json('static_payload')->nullable();
            $table->text('encoded_payload');
            $table->string('status', 32)->default('active');
            $table->boolean('tracking_enabled')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->unsignedInteger('max_scans')->nullable();
            $table->string('password_hash')->nullable();
            $table->text('fallback_url')->nullable();
            $table->string('expired_behavior', 32)->default('page');
            $table->json('utm_parameters')->nullable();
            $table->json('design_config')->nullable();
            $table->unsignedBigInteger('total_scans')->default(0);
            $table->unsignedBigInteger('human_scans')->default(0);
            $table->unsignedBigInteger('bot_scans')->default(0);
            $table->unsignedBigInteger('estimated_unique_scans')->default(0);
            $table->timestamp('last_scanned_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique('slug');
            $table->index(['workspace_id', 'qr_type']);
            $table->index(['workspace_id', 'status']);
            $table->index(['workspace_id', 'campaign_id']);
            $table->index(['workspace_id', 'folder_id']);
            $table->index(['qr_type', 'status']);
        });

        Schema::create('qr_code_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qr_code_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['qr_code_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qr_code_tag');
        Schema::dropIfExists('qr_codes');
    }
};
