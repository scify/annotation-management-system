<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void {
        if (Schema::hasTable('annotation_task_task_tag')) {
            Schema::rename('annotation_task_task_tag', 'annotation_task_tag');
        }
    }

    public function down(): void {
        if (Schema::hasTable('annotation_task_tag')) {
            Schema::rename('annotation_task_tag', 'annotation_task_task_tag');
        }
    }
};
