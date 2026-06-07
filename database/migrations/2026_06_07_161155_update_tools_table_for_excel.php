<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tools', function (Blueprint $table) {
            // Drop unique constraint temporarily to modify api_connection_id nullable
            // Since we're in SQLite or MySQL, dropping composite unique keys can be tricky.
            // In MySQL: $table->dropUnique(['api_connection_id', 'name']);
            // Let's wrap it in try-catch in case it doesn't exist or fails.
            
            $table->enum('type', ['api', 'excel'])->default('api')->after('id');
            $table->string('file_path')->nullable()->after('type');
            $table->string('sheet_name')->nullable()->after('file_path');
            
            $table->foreignId('api_connection_id')->nullable()->change();
            $table->string('endpoint')->nullable()->change();
            $table->string('method')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('tools', function (Blueprint $table) {
            $table->dropColumn(['type', 'file_path', 'sheet_name']);
        });
    }
};
