<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('dataset_instances', function (Blueprint $table): void {
            $table->id();
            $table->unsignedMediumInteger('index');
            $table->foreignId('dataset_id')->constrained();
            $table->json('content');
            $table->unique(['index', 'dataset_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('dataset_instances');
    }
};
