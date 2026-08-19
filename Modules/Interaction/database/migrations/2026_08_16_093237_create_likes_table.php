<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\User\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('likes', function (Blueprint $table) {
            $table->id();
            $table->ipAddress('ip_address');
            $table->foreignIdFor(User::class)->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->morphs('likeable');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('likes');
    }
};
