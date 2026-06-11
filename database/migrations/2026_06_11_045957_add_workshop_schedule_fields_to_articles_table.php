<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->timestamp('starts_at')->nullable()->after('published_at');
            $table->timestamp('ends_at')->nullable()->after('starts_at');

            $table->index(['type', 'starts_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropIndex(['type', 'starts_at']);
            $table->dropColumn(['starts_at', 'ends_at']);
        });
    }
};
