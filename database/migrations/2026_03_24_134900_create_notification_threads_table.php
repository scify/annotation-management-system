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
        Schema::create('notification_threads', function (Blueprint $table): void {
            $table->id();
            $table->string('type');
            $table->string('title')->nullable()->default(null);
            $table->timestamps();
        });

        Schema::table('annotations', function (Blueprint $table): void {
            $table->foreign('flag_notification_thread_id')
                ->references('id')
                ->on('notification_threads')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::table('annotations', function (Blueprint $table): void {
            $table->dropForeign(['flag_notification_thread_id']);
        });

        Schema::dropIfExists('notification_threads');
    }
};
