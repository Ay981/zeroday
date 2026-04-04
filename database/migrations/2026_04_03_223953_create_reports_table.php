<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('reports', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        
        $table->string('title');
        
        // Use ENUM to restrict allowed values (Mid-Level Strategy)
        $table->enum('severity', ['Low', 'Medium', 'High', 'Critical']);
        
        $table->text('description');
        
        // Give it a default value so it's never empty
        $table->string('status')->default('Open'); 
        
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
