<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->index(['user_id', 'status'], 'projects_user_status_index');
            $table->index(['user_id', 'deadline'], 'projects_user_deadline_index');
        });

        Schema::table('tasks', function (Blueprint $table): void {
            $table->index(['project_id', 'status'], 'tasks_project_status_index');
            $table->index(['project_id', 'priority'], 'tasks_project_priority_index');
        });

        Schema::table('milestones', function (Blueprint $table): void {
            $table->index(['project_id', 'due_date'], 'milestones_project_due_date_index');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table): void {
            $table->dropIndex('projects_user_status_index');
            $table->dropIndex('projects_user_deadline_index');
        });

        Schema::table('tasks', function (Blueprint $table): void {
            $table->dropIndex('tasks_project_status_index');
            $table->dropIndex('tasks_project_priority_index');
        });

        Schema::table('milestones', function (Blueprint $table): void {
            $table->dropIndex('milestones_project_due_date_index');
        });
    }
};
