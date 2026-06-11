<?php

use App\Enums\ArticleModerationStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table): void {
            $table->unsignedTinyInteger('moderation_status')
                ->default(ArticleModerationStatus::Pending->value)
                ->after('status');
            $table->text('moderation_note')->nullable()->after('moderation_status');
            $table->timestamp('submitted_at')->nullable()->after('moderation_note');
        });

        DB::table('articles')->update([
            'moderation_status' => ArticleModerationStatus::Approved->value,
        ]);
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table): void {
            $table->dropColumn(['moderation_status', 'moderation_note', 'submitted_at']);
        });
    }
};
