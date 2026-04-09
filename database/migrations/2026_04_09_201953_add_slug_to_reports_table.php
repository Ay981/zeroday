<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('title');
        });

        DB::table('reports')
            ->select(['id', 'title'])
            ->orderBy('id')
            ->get()
            ->each(function (object $report): void {
                $baseSlug = Str::slug($report->title);
                $slug = $baseSlug !== '' ? $baseSlug : 'report';

                DB::table('reports')
                    ->where('id', $report->id)
                    ->update(['slug' => $slug.'-'.$report->id]);
            });

        Schema::table('reports', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->change();
            $table->unique('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
