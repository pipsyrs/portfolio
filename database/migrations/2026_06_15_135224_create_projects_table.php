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
        // Create Project Table
        Schema::create('projects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('url')->nullable();
            $table->string('github_link')->nullable();
            $table->timestamps();
        });

        // Pivot tidak memakai kolom id sendiri: barisnya diidentifikasi oleh
        // pasangan foreign key, dan sync() memang tidak mengisi kolom id.
        Schema::create('project_tech_stack', function (Blueprint $table) {
            $table->foreignUuid('project_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('tech_stack_id')->constrained()->onDelete('cascade');

            $table->primary(['project_id', 'tech_stack_id']);
        });

        Schema::create('project_specialization', function (Blueprint $table) {
            $table->foreignUuid('project_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('specialization_id')->constrained()->onDelete('cascade');

            $table->primary(['project_id', 'specialization_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
        Schema::dropIfExists('project_tech_stack');
        Schema::dropIfExists('project_specialization');
    }
};
