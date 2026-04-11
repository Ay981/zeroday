<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            // 1. Standard B-Tree Indexes (Optimizes Dashboard Dropdowns)
            // Postgres handles these very efficiently.
            $table->index('severity');
            $table->index('status');

            // 2. Foreign Key Indexes (Optimizes Eager Loading/Joins)
            // Although constrained() often creates these, being explicit is safer.
            $table->index('user_id');
            $table->index('program_id');
        });

        // 3. PostgreSQL GIN Index (The Blazing Fast Search)
        // This creates a vector map of the title and description combined.
        // We use raw SQL because Laravel Blueprint doesn't have a native 'GIN' helper.
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("CREATE INDEX reports_search_index ON reports USING GIN (to_tsvector('english', title || ' ' || description))");
        }
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropIndex('reports_severity_index');
            $table->dropIndex('reports_status_index');
            $table->dropIndex('reports_user_id_index');
            $table->dropIndex('reports_program_id_index');
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS reports_search_index');
        }
    }
};
