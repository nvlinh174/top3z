<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('comment_reactions', 'session_token')) {
            return;
        }

        Schema::table('comment_reactions', function (Blueprint $table): void {
            $table->dropForeign(['comment_id']);
        });

        if ($this->userIdForeignKeyExists()) {
            Schema::table('comment_reactions', function (Blueprint $table): void {
                $table->dropForeign(['user_id']);
            });
        } elseif ($this->indexExists('comment_reactions_user_id_foreign')) {
            Schema::table('comment_reactions', function (Blueprint $table): void {
                $table->dropIndex('comment_reactions_user_id_foreign');
            });
        }

        Schema::table('comment_reactions', function (Blueprint $table): void {
            $table->dropUnique(['comment_id', 'user_id']);
        });

        Schema::table('comment_reactions', function (Blueprint $table): void {
            $table->foreignId('user_id')->nullable()->change();
            $table->string('session_token', 64)->nullable()->after('user_id');
            $table->string('ip_hash', 64)->nullable()->after('session_token');
        });

        DB::table('comment_reactions')->orderBy('id')->lazy()->each(function (object $row): void {
            DB::table('comment_reactions')
                ->where('id', $row->id)
                ->update([
                    'session_token' => hash('sha256', 'legacy:'.$row->id),
                ]);
        });

        Schema::table('comment_reactions', function (Blueprint $table): void {
            $table->string('session_token', 64)->nullable(false)->change();
            $table->foreign('comment_id')->references('id')->on('comments')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique(['comment_id', 'session_token']);
            $table->unique(['comment_id', 'user_id']);
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('comment_reactions', 'session_token')) {
            return;
        }

        Schema::table('comment_reactions', function (Blueprint $table): void {
            $table->dropForeign(['comment_id']);
            $table->dropForeign(['user_id']);
        });

        Schema::table('comment_reactions', function (Blueprint $table): void {
            $table->dropUnique(['comment_id', 'session_token']);
            $table->dropUnique(['comment_id', 'user_id']);
        });

        Schema::table('comment_reactions', function (Blueprint $table): void {
            $table->dropColumn(['session_token', 'ip_hash']);
            $table->foreignId('user_id')->nullable(false)->change();
        });

        Schema::table('comment_reactions', function (Blueprint $table): void {
            $table->foreign('comment_id')->references('id')->on('comments')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique(['comment_id', 'user_id']);
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
            ['comment_reactions', 'FOREIGN KEY', 'comment_reactions_user_id_foreign']
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
            ['comment_reactions', $indexName]
        );

        return $index !== null;
    }
};
