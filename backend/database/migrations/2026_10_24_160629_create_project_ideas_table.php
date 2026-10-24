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
        Schema::create('project_ideas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('tagline')->nullable();
            $table->text('description')->nullable();
            $table->json('tech_stack')->nullable();
            $table->string('difficulty', 10)->default('medium');
            $table->string('status', 10)->default('spark');
            $table->string('origin', 12)->default('shower');
            $table->unsignedTinyInteger('hype_level')->default(3);
            $table->string('target_event')->nullable();
            $table->date('deadline')->nullable();
            $table->string('repo_url')->nullable();
            $table->boolean('is_pinned')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_ideas');
    }
};
