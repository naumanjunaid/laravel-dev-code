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
        Schema::create('tokens', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('token', 64)->unique();
            $table->json('scopes')->nullable();   // scopes like ["read","write"]
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tokens');
    }
};
