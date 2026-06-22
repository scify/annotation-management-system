<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void {
        Schema::table('notification_threads', function (Blueprint $table): void {
            $table->foreignId('project_id')->nullable()->after('type')->constrained('projects')->nullOnDelete();
            $table->foreignId('target_user_id')->nullable()->after('project_id')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void {
        Schema::table('notification_threads', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('project_id');
            $table->dropConstrainedForeignId('target_user_id');
        });
    }
};
