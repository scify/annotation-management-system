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
        Schema::table('annotation_tasks', function (Blueprint $table): void {
            $table->string('task_type')->default('dummy')->after('weight');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('annotation_tasks', function (Blueprint $table): void {
            $table->dropColumn('task_type');
        });
    }
};
