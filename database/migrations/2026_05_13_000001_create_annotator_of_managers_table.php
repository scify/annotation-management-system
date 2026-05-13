<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void {
        Schema::create('annotator_of_managers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('manager_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('annotator_id')->constrained('users')->cascadeOnDelete();
            $table->unique(['manager_id', 'annotator_id']);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('annotator_of_managers');
    }
};
