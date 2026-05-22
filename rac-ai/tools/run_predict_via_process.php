<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

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
    if (is_string($c) && file_exists($c)) {
        $python = $c;
        break;
    }
}
if ($python === null) $python = 'python';

echo "Using python: $python\n";

if (! file_exists($scriptPath)) {
    echo "Missing script: $scriptPath\n";
    exit(1);
}

if (! is_dir($modelDir)) {
    echo "Missing model dir: $modelDir\n";
    exit(1);
}

$image = storage_path('app/tmp_test.jpg');
echo "Running: $python $scriptPath --image $image --model-dir $modelDir\n";
$process = new Symfony\Component\Process\Process([$python, $scriptPath, '--image', $image, '--model-dir', $modelDir]);
$process->setTimeout(120);
$process->run();

echo "Exit: " . $process->getExitCode() . "\n";
echo "Output:\n" . $process->getOutput() . "\n";
echo "Error:\n" . $process->getErrorOutput() . "\n";
