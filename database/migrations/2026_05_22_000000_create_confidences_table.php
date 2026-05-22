<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void {
        Schema::create('confidences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('annotation_id')->constrained('annotations')->cascadeOnDelete();
            $table->string('value');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('confidences');
    }
};
