<?php

namespace App\Http\Controllers;

use App\Models\WasteClassification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;

class WasteClassificationController extends Controller
{
    private const MODEL_FILE_SETS = [
        ['best_ml_model.pkl', 'best_ml_scaler.pkl', 'label_encoder.pkl'],
        ['svm_model.pkl', 'scaler.pkl', 'label_encoder.pkl'],
    ];

    public function index(Request $request)
    {
        $resultId = $request->integer('result');

        return view('waste-classifier', [
            'latestResult' => $resultId ? WasteClassification::find($resultId) : null,
            'recentScans' => WasteClassification::query()
                ->latest()
                ->limit(12)
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'image' => ['required', 'image', 'max:10240'],
        ]);

        $file = $request->file('image');
        if ($file === null) {
            return back()->withErrors(['image' => 'Vui lòng chọn một file ảnh hợp lệ.']);
        }

        $storedName = (string) Str::uuid() . '.' . $file->getClientOriginalExtension();
        $storedPath = $file->storeAs('waste-uploads', $storedName, 'public');

        $classification = WasteClassification::create([
            'original_name' => $file->getClientOriginalName(),
            'image_path' => $storedPath,
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'status' => 'processing',
        ]);

        try {
            $prediction = $this->predict($storedPath);
            $safePrediction = $this->normalizeForStorage($prediction);

            $classification->update([
                'status' => 'completed',
                'predicted_label' => $this->normalizeScalar($safePrediction['label'] ?? null),
                'confidence' => $safePrediction['confidence'] ?? null,
                'prediction_payload' => $safePrediction,
                'predicted_at' => now(),
                'error_message' => null,
            ]);

            return redirect()
                ->route('home', ['result' => $classification->id])
                ->with('success', 'Đã phân loại xong ảnh vừa tải lên.');
        } catch (Throwable $throwable) {
            $errorMessage = $this->normalizeScalar($throwable->getMessage()) ?? 'Không xác định';
            $classification->update([
                'status' => 'failed',
                'prediction_payload' => $this->normalizeForStorage(['error' => $errorMessage]),
                'error_message' => $errorMessage,
                'predicted_at' => now(),
            ]);

            return redirect()
                ->route('home', ['result' => $classification->id])
                ->withErrors(['image' => 'Không thể phân loại ảnh: ' . $errorMessage]);
        }
    }

    public function destroy(Request $request, $id)
    {
        $classification = WasteClassification::find($id);
        if (! $classification) {
            return back()->withErrors(['delete' => 'Bản ghi không tồn tại.']);
        }

        try {
            // delete stored file if exists
            if ($classification->image_path && Storage::disk('public')->exists($classification->image_path)) {
                Storage::disk('public')->delete($classification->image_path);
            }

            $classification->delete();

            return back()->with('success', 'Đã xóa bản ghi.');
        } catch (Throwable $e) {
            return back()->withErrors(['delete' => 'Không thể xóa bản ghi: ' . $e->getMessage()]);
        }
    }

    private function predict(string $storedPath): array
    {
        $imagePath = Storage::disk('public')->path($storedPath);
        $scriptPath = base_path('scripts/predict_waste.py');
        $modelDir = storage_path('app/model');

        if (! file_exists($scriptPath)) {
            throw new RuntimeException('Không tìm thấy script dự đoán tại scripts/predict_waste.py.');
        }

        if (! $this->hasRequiredModelFiles($modelDir)) {
            throw new RuntimeException(
                'Thiếu file model trong storage/app/model. Cần một trong hai bộ: [best_ml_model.pkl, best_ml_scaler.pkl, label_encoder.pkl] hoặc [svm_model.pkl, scaler.pkl, label_encoder.pkl].'
            );
        }

        // Prefer project virtualenv python if available to avoid sklearn version mismatches.
        $pythonCandidates = [
            base_path('.venv\\Scripts\\python.exe'),
            base_path('..\\.venv\\Scripts\\python.exe'),
            base_path('venv\\Scripts\\python.exe'),
            '/usr/bin/python3',
            'python',
        ];

        $python = null;
        foreach ($pythonCandidates as $candidate) {
            if (is_string($candidate) && file_exists($candidate)) {
                $python = $candidate;
                break;
            }
        }

        if ($python === null) {
            $python = 'python';
        }

        $process = new Process([
            $python,
            $scriptPath,
            '--image',
            $imagePath,
            '--model-dir',
            $modelDir,
        ]);
        $process->setTimeout(120);
        $process->run();

        if (! $process->isSuccessful()) {
            $errorOutput = trim($process->getErrorOutput() ?: $process->getOutput());
            throw new RuntimeException($errorOutput !== '' ? $errorOutput : 'Python process failed.');
        }

        $rawOutput = trim($process->getOutput());
        $decoded = json_decode($rawOutput, true, 512, JSON_THROW_ON_ERROR);

        if (($decoded['status'] ?? null) !== 'ok') {
            $message = $decoded['error'] ?? 'Dự đoán thất bại.';
            throw new RuntimeException($message);
        }

        return $decoded;
    }

    private function hasRequiredModelFiles(string $modelDir): bool
    {
        foreach (self::MODEL_FILE_SETS as $fileSet) {
            $allFilesExist = true;

            foreach ($fileSet as $fileName) {
                if (! file_exists($modelDir . DIRECTORY_SEPARATOR . $fileName)) {
                    $allFilesExist = false;
                    break;
                }
            }

            if ($allFilesExist) {
                return true;
            }
        }

        return false;
    }

    private function normalizeForStorage(mixed $value): mixed
    {
        if (is_array($value)) {
            $normalized = [];

            foreach ($value as $key => $item) {
                $normalized[$this->normalizeScalar($key) ?? $key] = $this->normalizeForStorage($item);
            }

            return $normalized;
        }

        if (is_string($value)) {
            return $this->normalizeString($value);
        }

        if (is_object($value)) {
            return $this->normalizeForStorage(get_object_vars($value));
        }

        return $value;
    }

    private function normalizeScalar(mixed $value): mixed
    {
        if (is_string($value)) {
            return $this->normalizeString($value);
        }

        return $value;
    }

    private function normalizeString(string $value): string
    {
        if ($value === '') {
            return $value;
        }

        if (function_exists('mb_check_encoding') && mb_check_encoding($value, 'UTF-8')) {
            return $value;
        }

        $converted = @iconv('UTF-8', 'UTF-8//IGNORE', $value);

        if ($converted !== false && $converted !== '') {
            return $converted;
        }

        return preg_replace('/[^\x09\x0A\x0D\x20-\x7E]/', '', $value) ?? '';
    }
}