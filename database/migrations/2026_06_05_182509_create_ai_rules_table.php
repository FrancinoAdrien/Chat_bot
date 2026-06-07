<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Nom ou description courte de la règle');
            $table->text('instruction')->comment('La consigne stricte à envoyer à l\'IA');
            $table->enum('target_type', ['all', 'poste', 'user'])->default('all')->comment('Cible de la règle');
            $table->string('target_value')->nullable()->comment('La valeur de la cible (nom du poste ou ID utilisateur)');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_rules');
    }
};
