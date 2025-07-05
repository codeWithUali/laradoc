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
        Schema::create('laradoc_documentation', function (Blueprint $table) {
            $table->id();
            $table->string('module');
            $table->string('title');
            $table->text('content');
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['module']);
            $table->fullText(['title', 'content']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laradoc_documentation');
    }
}; 