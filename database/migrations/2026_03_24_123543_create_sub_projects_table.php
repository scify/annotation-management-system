<?php

declare(strict_types=1);

use App\Enums\ProjectStatusEnum;
use App\Enums\SubProjectPriorityEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('sub_projects', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('name');
            $table->string('status')->default(ProjectStatusEnum::PENDING->value);
            $table->string('priority')->default(SubProjectPriorityEnum::MEDIUM->value);
            $table->boolean('flexible')->default(false);
            $table->boolean('auto_submission')->default(true);
            $table->integer('minimum_annotators')->default(0);
            $table->integer('first_instance_index');
            $table->integer('last_instance_index');
            $table->date('scheduled_at')->nullable();
            $table->date('deadline_at')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('sub_projects');
    }
};
