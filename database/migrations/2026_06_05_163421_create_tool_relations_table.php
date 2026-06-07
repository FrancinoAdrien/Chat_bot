<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tool_relations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('api_connection_id')->constrained('api_connections')->cascadeOnDelete();
            $table->foreignId('primary_tool_id')->constrained('tools')->cascadeOnDelete();
            $table->string('primary_field');
            $table->foreignId('foreign_tool_id')->constrained('tools')->cascadeOnDelete();
            $table->string('foreign_field');
            $table->timestamps();

            $table->unique(['primary_tool_id', 'primary_field', 'foreign_tool_id', 'foreign_field'], 'tool_rel_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tool_relations');
    }
};
