<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Tools:\n";
echo json_encode(\App\Models\Tool::all()->toArray(), JSON_PRETTY_PRINT);
echo "\nConnections:\n";
echo json_encode(\App\Models\ApiConnection::all()->toArray(), JSON_PRETTY_PRINT);
echo "\nRelations:\n";
echo json_encode(\App\Models\ToolRelation::all()->toArray(), JSON_PRETTY_PRINT);
