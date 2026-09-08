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
        Schema::create('views', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->morphs('viewable');
            $table->string('visitor_hash', 64);
            $table->date('viewed_on');
            $table->timestamps();

            $table->unique(
                ['viewable_type', 'viewable_id', 'visitor_hash', 'viewed_on'],
                'views_viewable_visitor_day_unique',
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('views');
    }
};
