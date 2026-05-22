<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('waste_classifications', function (Blueprint $table) {
            $table->id();
            $table->string('original_name');
            $table->string('image_path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('status')->default('processing');
            $table->string('predicted_label')->nullable();
            $table->decimal('confidence', 6, 4)->nullable();
            $table->json('prediction_payload')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('predicted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waste_classifications');
    }
};