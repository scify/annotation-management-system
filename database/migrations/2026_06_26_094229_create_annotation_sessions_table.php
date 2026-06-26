<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void {
        Schema::create('annotation_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('annotation_assignment_id')->constrained()->cascadeOnDelete();
            $table->timestamp('started_timestamp');
            $table->timestamp('ended_timestamp')->nullable();
            $table->unsignedInteger('session_annotations_count')->default(0);
            $table->foreignId('next_annotation_id')->nullable()->constrained('annotations')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('annotation_sessions');
    }
};
