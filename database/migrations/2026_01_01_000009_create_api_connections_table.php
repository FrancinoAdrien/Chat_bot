<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_connections', function (Blueprint $table) {
            $table->id();
            $table->string('name');                    // "Caisse Principale", "Site E-commerce"
            $table->string('base_url');                // "http://localhost:8000"
            $table->string('login_url')->nullable();   // "/api/login" — optionnel
            $table->text('auth_token')->nullable();    // Bearer token stocké après auth
            $table->boolean('is_authenticated')->default(false);
            $table->timestamp('authenticated_at')->nullable();
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_connections');
    }
};
