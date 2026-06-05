<?php

namespace Database\Seeders;

use App\Models\ApiConnection;
use App\Models\Tool;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $connection = ApiConnection::create([
            'name'             => 'Mon ERP',
            'base_url'         => 'http://localhost:8000',
            'login_url'        => '/api/login',
            'auth_token'       => 'sk-demo-1234567890abcdef',
            'is_authenticated' => true,
            'description'      => 'ERP Interne pour tester le chatbot',
            'active'           => true,
        ]);

        Tool::create([
            'api_connection_id' => $connection->id,
            'name'              => 'getSalesToday',
            'label'             => 'Ventes du Jour',
            'endpoint'          => '/api/ai/sales/today',
            'description'       => 'Récupère le nombre total de ventes et le chiffre d\'affaires d\'aujourd\'hui.',
            'keywords'          => ['ventes aujourd\'hui', 'chiffre d\'affaires', 'sales today', 'today'],
            'method'            => 'GET',
            'active'            => true,
        ]);

        Tool::create([
            'api_connection_id' => $connection->id,
            'name'              => 'getTopProducts',
            'label'             => 'Top Produits',
            'endpoint'          => '/api/ai/top-products',
            'description'       => 'Récupère les produits les plus vendus actuellement.',
            'keywords'          => ['top produits', 'meilleurs produits', 'best sellers'],
            'method'            => 'GET',
            'active'            => true,
        ]);
    }
}
