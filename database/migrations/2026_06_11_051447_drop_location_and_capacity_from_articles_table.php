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
        $columnsToDrop = array_filter(
            ['location', 'capacity'],
            fn (string $column): bool => Schema::hasColumn('articles', $column),
        );

        if ($columnsToDrop === []) {
            return;
        }

        Schema::table('articles', function (Blueprint $table) use ($columnsToDrop) {
            $table->dropColumn($columnsToDrop);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->string('location')->nullable()->after('ends_at');
            $table->unsignedInteger('capacity')->nullable()->after('location');
        });
    }
};
