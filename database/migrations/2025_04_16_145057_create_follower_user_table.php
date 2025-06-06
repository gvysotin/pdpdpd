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
        Schema::create('follower_user', function (Blueprint $table) {
            // $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // тот на которого подписываемся
            $table->foreignId('follower_id')->constrained('users')->cascadeOnDelete(); // тот кто подписывается            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('follower_user');
    }
};
