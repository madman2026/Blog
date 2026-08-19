<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();

            $table->string('title');

            $table->string('slug')->unique();

            $table->text('summary')->nullable();

            $table->longText('body');

            $table->string('image')->nullable();

            $table
                ->boolean('published')
                ->default(false)
                ->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
