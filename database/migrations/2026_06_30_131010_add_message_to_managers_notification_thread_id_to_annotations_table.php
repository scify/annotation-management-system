<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void {
        Schema::table('annotations', function (Blueprint $table): void {
            $table->unsignedBigInteger('message_to_managers_notification_thread_id')->nullable()->default(null)->after('flag_notification_thread_id');
        });
    }

    public function down(): void {
        Schema::table('annotations', function (Blueprint $table): void {
            $table->dropColumn('message_to_managers_notification_thread_id');
        });
    }
};
