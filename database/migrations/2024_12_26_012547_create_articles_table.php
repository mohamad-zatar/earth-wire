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
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255)->index();
            $table->text('content')->fulltext();
            $table->string('author', 100)->nullable();
            $table->string('source', 100)->index();
            $table->string('category', 50)->index();
            $table->string('url', 2048)->nullable();
            $table->date('published_at')->index();
            $table->timestamps();

            $table->fullText(['title', 'content']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
