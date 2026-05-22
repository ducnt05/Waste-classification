<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\WasteClassification;
use Illuminate\Support\Str;

$source = storage_path('app/tmp_test.jpg');
if (! file_exists($source)) {
    echo "Missing test image: $source\n";
    exit(1);
}

$storedName = (string) Str::uuid() . '.jpg';
$storageDir = storage_path('app');
@mkdir($storageDir . '/waste-uploads', 0777, true);
$dest = $storageDir . '/waste-uploads/' . $storedName;
copy($source, $dest);

$record = WasteClassification::create([
    'original_name' => basename($source),
    'image_path' => 'waste-uploads/' . $storedName,
    'mime_type' => 'image/jpeg',
    'file_size' => filesize($dest),
    'status' => 'processing',
]);

// Use same process selection as controller
$scriptPath = base_path('scripts/predict_waste.py');
$modelDir = storage_path('app/model');
$candidates = [
    base_path('.venv\\Scripts\\python.exe'),
    base_path('..\\.venv\\Scripts\\python.exe'),
    base_path('venv\\Scripts\\python.exe'),
    '/usr/bin/python3',
    'python',
];
$python = null;
foreach ($candidates as $c) {
    if (is_string($c) && file_exists($c)) { $python = $c; break; }
}
if ($python === null) $python = 'python';

echo "Running predictor with: $python\n";
$process = new Symfony\Component\Process\Process([$python, $scriptPath, '--image', storage_path('app/' . $record->image_path), '--model-dir', $modelDir]);
$process->setTimeout(120);
$process->run();

if (! $process->isSuccessful()) {
    $err = trim($process->getErrorOutput() ?: $process->getOutput());
    $record->update([
        'status' => 'failed',
        'prediction_payload' => ['error' => $err],
        'error_message' => $err,
        'predicted_at' => now(),
    ]);
    echo "Prediction failed:\n" . $err . "\n";
    exit(1);
}

$out = json_decode($process->getOutput(), true);
if (($out['status'] ?? null) !== 'ok') {
    $err = $out['error'] ?? 'Unknown';
    $record->update(['status' => 'failed', 'prediction_payload' => ['error' => $err], 'error_message' => $err, 'predicted_at' => now()]);
    echo "Prediction returned error: $err\n";
    exit(1);
}

$record->update([
    'status' => 'completed',
    'predicted_label' => $out['label'] ?? null,
    'confidence' => $out['confidence'] ?? null,
    'prediction_payload' => $out,
    'predicted_at' => now(),
]);

echo "Prediction success: " . json_encode($out) . "\n";