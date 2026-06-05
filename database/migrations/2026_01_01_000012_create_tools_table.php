<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('api_connection_id')->constrained('api_connections')->cascadeOnDelete();
            $table->string('name');           // e.g. getSalesToday
            $table->string('label');          // e.g. "Ventes du jour"
            $table->string('endpoint');       // e.g. /api/ai/sales/today
            $table->text('description');      // Description de l'outil
            $table->json('keywords');         // ["ventes", "aujourd'hui", "today"]
            $table->string('method')->default('GET');
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['api_connection_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tools');
    }
};
