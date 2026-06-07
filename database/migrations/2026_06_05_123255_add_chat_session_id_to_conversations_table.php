<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->foreignId('chat_session_id')
                ->nullable()
                ->after('id')
                ->constrained('chat_sessions')
                ->nullOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->after('chat_session_id')
                ->constrained('users')
                ->nullOnDelete();

            $table->index(['chat_session_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropForeign(['chat_session_id']);
            $table->dropForeign(['user_id']);
            $table->dropColumn(['chat_session_id', 'user_id']);
        });
    }
};
