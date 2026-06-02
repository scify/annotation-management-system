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
        Schema::create('annotations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('annotation_assignment_id')->constrained();
            $table->foreignId('dataset_instance_id')->constrained();
            // Index of the instance as ordered by the Project (reflects dataset-level shuffle if enabled)
            $table->unsignedMediumInteger('project_instance_index');
            // Index of the instance as ordered for this annotator (equals project_instance_index
            // unless per-annotator shuffle is active on the annotation_assignment)
            $table->unsignedMediumInteger('annotator_instance_index');
            $table->unique(['annotation_assignment_id', 'dataset_instance_id']);
            $table->json('annotations')->nullable()->default(null);
            $table->boolean('pending')->default(false);
            $table->boolean('is_flagged')->default(false);
            $table->string('confidence')->nullable();
            $table->foreignId('last_edited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('annotations');
    }
};
