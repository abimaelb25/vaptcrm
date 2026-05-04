<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('saas_usage_cycle_summaries')) {
            Schema::create('saas_usage_cycle_summaries', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('loja_id')->constrained('lojas')->cascadeOnDelete();
                $table->foreignId('assinatura_id')->nullable()->constrained('saas_assinaturas')->nullOnDelete();
                $table->date('cycle_start');
                $table->date('cycle_end');
                $table->string('metric_key', 120);
                $table->unsignedBigInteger('consumed')->default(0);
                $table->unsignedBigInteger('limit_value')->nullable();
                $table->unsignedBigInteger('overage')->default(0);
                $table->decimal('unit_price', 10, 4)->default(0);
                $table->decimal('subtotal', 12, 2)->default(0);
                $table->json('metadata')->nullable();
                $table->timestamp('consolidated_at')->useCurrent();
                $table->timestamps();

                $table->unique(['loja_id', 'cycle_start', 'cycle_end', 'metric_key'], 'saas_usage_cycle_unique');
                $table->index(['cycle_start', 'cycle_end'], 'saas_usage_cycle_period_idx');
            });
        }

        if (! Schema::hasTable('saas_usage_charge_previews')) {
            Schema::create('saas_usage_charge_previews', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('loja_id')->constrained('lojas')->cascadeOnDelete();
                $table->foreignId('assinatura_id')->nullable()->constrained('saas_assinaturas')->nullOnDelete();
                $table->date('cycle_start');
                $table->date('cycle_end');
                $table->string('currency', 10)->default('BRL');
                $table->decimal('total_amount', 12, 2)->default(0);
                $table->string('status', 30)->default('draft');
                $table->json('breakdown')->nullable();
                $table->timestamp('generated_at')->useCurrent();
                $table->timestamps();

                $table->unique(['loja_id', 'cycle_start', 'cycle_end'], 'saas_usage_preview_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('saas_usage_charge_previews');
        Schema::dropIfExists('saas_usage_cycle_summaries');
    }
};
