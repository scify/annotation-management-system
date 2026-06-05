<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void {
        Schema::create('annotator_password_policies', function (Blueprint $table): void {
            $table->id();
            $table->unsignedSmallInteger('min_length')->default(8);
            $table->string('composition_mode', 40)->default('letters_and_numbers');
            $table->boolean('mixed_case_required')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('annotator_password_policies');
    }
};
