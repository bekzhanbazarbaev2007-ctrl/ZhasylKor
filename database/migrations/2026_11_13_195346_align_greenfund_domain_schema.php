<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::rename('environmental_reports', 'reports');
        Schema::table('reports', function (Blueprint $table): void {
            $table->renameColumn('code', 'report_number');
            $table->renameColumn('reporter_id', 'user_id');
            $table->renameColumn('location_label', 'address');
            $table->renameColumn('assignee_id', 'assigned_to');
        });

        Schema::rename('environmental_tasks', 'tasks');
        Schema::table('tasks', function (Blueprint $table): void {
            $table->renameColumn('code', 'task_number');
            $table->renameColumn('location_label', 'address');
            $table->renameColumn('assignee_id', 'assigned_to');
            $table->renameColumn('due_at', 'due_date');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
        });

        Schema::table('green_assets', function (Blueprint $table): void {
            $table->renameColumn('code', 'asset_number');
            $table->renameColumn('location_label', 'address');
            $table->renameColumn('registered_at', 'registration_date');
            $table->string('species')->nullable();
            $table->text('description')->nullable();
        });

        Schema::rename('maintenance_schedules', 'maintenance_records');
        Schema::table('maintenance_records', function (Blueprint $table): void {
            $table->renameColumn('kind', 'maintenance_type');
            $table->renameColumn('assignee_id', 'assigned_to');
            $table->renameColumn('notes', 'description');
            $table->foreignId('task_id')->nullable()->constrained('tasks')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
        });

        if (! Schema::hasColumn('users', 'phone')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->string('phone')->nullable();
            });
        }

        Schema::create('report_photos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('report_id')->constrained('reports')->cascadeOnDelete();
            $table->string('file_path');
            $table->string('photo_type');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['report_id', 'photo_type']);
        });

        DB::table('reports')
            ->whereNotNull('photo_path')
            ->orderBy('id')
            ->get(['id', 'photo_path', 'user_id', 'created_at', 'updated_at'])
            ->each(function (object $report): void {
                DB::table('report_photos')->insert([
                    'report_id' => $report->id,
                    'file_path' => $report->photo_path,
                    'photo_type' => 'before',
                    'uploaded_by' => $report->user_id,
                    'created_at' => $report->created_at,
                    'updated_at' => $report->updated_at,
                ]);
            });

        DB::table('reports')->where('category', 'Illegal dumping')->update(['category' => 'illegal_dumping']);
        DB::table('reports')->where('category', 'Damaged tree')->update(['category' => 'damaged_tree']);
        DB::table('reports')->where('category', 'Air pollution')->update(['category' => 'air_pollution']);
        DB::table('reports')->where('category', 'Water pollution')->update(['category' => 'water_pollution']);
        DB::table('reports')->whereIn('category', ['Green space', 'Watering needed'])->update(['category' => 'green_space']);
        DB::table('reports')->where('category', 'Other environmental issue')->update(['category' => 'other']);
        DB::table('green_assets')->where('type', 'green area')->update(['type' => 'green_area']);

        Schema::table('reports', function (Blueprint $table): void {
            $table->dropColumn('photo_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table): void {
            $table->string('photo_path')->nullable();
        });

        DB::table('report_photos')
            ->where('photo_type', 'before')
            ->orderBy('id')
            ->get(['report_id', 'file_path'])
            ->each(function (object $photo): void {
                DB::table('reports')
                    ->where('id', $photo->report_id)
                    ->whereNull('photo_path')
                    ->update(['photo_path' => $photo->file_path]);
            });

        Schema::dropIfExists('report_photos');

        DB::table('reports')->where('category', 'illegal_dumping')->update(['category' => 'Illegal dumping']);
        DB::table('reports')->where('category', 'damaged_tree')->update(['category' => 'Damaged tree']);
        DB::table('reports')->where('category', 'air_pollution')->update(['category' => 'Air pollution']);
        DB::table('reports')->where('category', 'water_pollution')->update(['category' => 'Water pollution']);
        DB::table('reports')->where('category', 'green_space')->update(['category' => 'Green space']);
        DB::table('reports')->where('category', 'other')->update(['category' => 'Other environmental issue']);
        DB::table('green_assets')->where('type', 'green_area')->update(['type' => 'green area']);

        Schema::table('maintenance_records', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('task_id');
            $table->dropColumn('completed_at');
            $table->renameColumn('description', 'notes');
            $table->renameColumn('assigned_to', 'assignee_id');
            $table->renameColumn('maintenance_type', 'kind');
        });
        Schema::rename('maintenance_records', 'maintenance_schedules');

        Schema::table('green_assets', function (Blueprint $table): void {
            $table->dropColumn(['species', 'description']);
            $table->renameColumn('registration_date', 'registered_at');
            $table->renameColumn('address', 'location_label');
            $table->renameColumn('asset_number', 'code');
        });

        Schema::table('tasks', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('created_by');
            $table->renameColumn('due_date', 'due_at');
            $table->renameColumn('assigned_to', 'assignee_id');
            $table->renameColumn('address', 'location_label');
            $table->renameColumn('task_number', 'code');
        });
        Schema::rename('tasks', 'environmental_tasks');

        Schema::table('reports', function (Blueprint $table): void {
            $table->renameColumn('assigned_to', 'assignee_id');
            $table->renameColumn('address', 'location_label');
            $table->renameColumn('user_id', 'reporter_id');
            $table->renameColumn('report_number', 'code');
        });
        Schema::rename('reports', 'environmental_reports');

        if (Schema::hasColumn('users', 'phone')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropColumn('phone');
            });
        }
    }
};
