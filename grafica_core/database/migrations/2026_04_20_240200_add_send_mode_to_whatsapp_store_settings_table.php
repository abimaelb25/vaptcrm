<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('whatsapp_store_settings', function (Blueprint $table) {
            $table->string('send_mode', 30)
                ->default('official_api')
                ->after('catalog_link');
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_store_settings', function (Blueprint $table) {
            $table->dropColumn('send_mode');
        });
    }
};
