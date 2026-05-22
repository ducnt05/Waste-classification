<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WasteClassification extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'original_name',
        'image_path',
        'mime_type',
        'file_size',
        'status',
        'predicted_label',
        'confidence',
        'prediction_payload',
        'error_message',
        'predicted_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'confidence' => 'float',
        'prediction_payload' => 'array',
        'predicted_at' => 'datetime',
    ];

    public function getImageUrlAttribute(): string
    {
        return asset('storage/' . ltrim($this->image_path, '/'));
    }
}