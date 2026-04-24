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
        Schema::create('instance_shuffle_mappers', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('new_index');
            $table->foreignId('dataset_instance_id')->constrained('dataset_instances')->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['new_index', 'project_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('instance_shuffle_mappers');
    }
};
