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
            $table->foreignId('user_id')->constrained();
            $table->unique(['annotation_assignment_id', 'user_id']);
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
