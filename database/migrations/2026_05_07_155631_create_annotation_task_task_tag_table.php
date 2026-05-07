<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void {
        Schema::create('annotation_task_task_tag', function (Blueprint $table): void {
            $table->foreignId('annotation_task_id')->constrained('annotation_tasks')->cascadeOnDelete();
            $table->foreignId('task_tag_id')->constrained('task_tags')->cascadeOnDelete();
            $table->primary(['annotation_task_id', 'task_tag_id']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('annotation_task_task_tag');
    }
};
