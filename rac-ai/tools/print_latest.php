<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$r = App\Models\WasteClassification::latest()->first();
echo json_encode($r ? $r->toArray() : null, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
