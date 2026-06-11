<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('article_reactions', 'session_token')) {
            return;
        }

        Schema::table('article_reactions', function (Blueprint $table): void {
            $table->dropForeign(['article_id']);
        });

        if ($this->userIdForeignKeyExists()) {
            Schema::table('article_reactions', function (Blueprint $table): void {
                $table->dropForeign(['user_id']);
            });
        } elseif ($this->indexExists('article_reactions_user_id_foreign')) {
            Schema::table('article_reactions', function (Blueprint $table): void {
                $table->dropIndex('article_reactions_user_id_foreign');
            });
        }

        Schema::table('article_reactions', function (Blueprint $table): void {
            $table->dropUnique(['article_id', 'user_id', 'type']);
        });

        Schema::table('article_reactions', function (Blueprint $table): void {
            $table->foreignId('user_id')->nullable()->change();
            $table->string('session_token', 64)->nullable()->after('user_id');
            $table->string('ip_hash', 64)->nullable()->after('session_token');
        });

        DB::table('article_reactions')->orderBy('id')->lazy()->each(function (object $row): void {
            DB::table('article_reactions')
                ->where('id', $row->id)
                ->update([
                    'session_token' => hash('sha256', 'legacy:'.$row->id),
                ]);
        });

        Schema::table('article_reactions', function (Blueprint $table): void {
            $table->string('session_token', 64)->nullable(false)->change();
            $table->foreign('article_id')->references('id')->on('articles')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique(['article_id', 'session_token', 'type']);
            $table->unique(['article_id', 'user_id', 'type']);
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('article_reactions', 'session_token')) {
            return;
        }

        Schema::table('article_reactions', function (Blueprint $table): void {
            $table->dropForeign(['article_id']);
            $table->dropForeign(['user_id']);
        });

        Schema::table('article_reactions', function (Blueprint $table): void {
            $table->dropUnique(['article_id', 'session_token', 'type']);
            $table->dropUnique(['article_id', 'user_id', 'type']);
        });

        Schema::table('article_reactions', function (Blueprint $table): void {
            $table->dropColumn(['session_token', 'ip_hash']);
            $table->foreignId('user_id')->nullable(false)->change();
        });

        Schema::table('article_reactions', function (Blueprint $table): void {
            $table->foreign('article_id')->references('id')->on('articles')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique(['article_id', 'user_id', 'type']);
        });
    }

    private function userIdForeignKeyExists(): bool
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return true;
        }

        $constraint = DB::selectOne(
            'SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
             WHERE TABLE_SCHEMA = DATABASE()
             AND TABLE_NAME = ?
             AND CONSTRAINT_TYPE = ?
             AND CONSTRAINT_NAME = ?',
            ['article_reactions', 'FOREIGN KEY', 'article_reactions_user_id_foreign']
        );

        return $constraint !== null;
    }

    private function indexExists(string $indexName): bool
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return false;
        }

        $index = DB::selectOne(
            'SELECT INDEX_NAME FROM information_schema.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE()
             AND TABLE_NAME = ?
             AND INDEX_NAME = ?
             LIMIT 1',
            ['article_reactions', $indexName]
        );

        return $index !== null;
    }
};
