<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void {
        Schema::table('project_managers', function (Blueprint $table): void {
            $table->boolean('request_to_leave')->default(false)->after('accepted');
            $table->boolean('proposed_to_become_owner')->default(false)->after('request_to_leave');
        });
    }

    public function down(): void {
        Schema::table('project_managers', function (Blueprint $table): void {
            $table->dropColumn(['request_to_leave', 'proposed_to_become_owner']);
        });
    }
};
