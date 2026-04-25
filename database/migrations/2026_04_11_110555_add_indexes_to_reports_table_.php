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
        // Create it only on PostgreSQL where GIN is supported.
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("CREATE INDEX IF NOT EXISTS reports_search_index ON reports USING GIN (to_tsvector('english', title || ' ' || description))");
        }
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropIndex(['severity']);
            $table->dropIndex(['status']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['program_id']);
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS reports_search_index');
        }
    }
};
