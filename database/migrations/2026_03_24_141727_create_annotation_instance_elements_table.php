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
        Schema::create('annotation_instance_elements', function (Blueprint $table): void {
            $table->id();
            $table->unsignedMediumInteger('index');
            $table->string('key');
            $table->string('value');
            $table->foreignId('annotation_instance_id')->constrained();
            $table->unique(['index', 'annotation_instance_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('annotation_instance_elements');
    }
};
