<?php

declare(strict_types=1);

use App\Enums\ProjectStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('projects', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->foreignId('owner_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('annotation_task_id')->constrained('annotation_tasks')->cascadeOnDelete();
            $table->foreignId('dataset_id')->constrained('datasets')->cascadeOnDelete();
            $table->string('status')->default(ProjectStatusEnum::PENDING->value);
            $table->boolean('restricted_visibility')->default(false);
            $table->json('annotation_task_configuration')->nullable();
            $table->date('scheduled_at')->nullable();
            $table->date('deadline_at')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('projects');
    }
};
