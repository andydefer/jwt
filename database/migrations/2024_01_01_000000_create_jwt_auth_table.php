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
        Schema::create('jwt_auth', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('jwt_token', 255)->unique();
            $table->boolean('is_jwt_auth')->default(true);
            $table->timestamp('jwt_issued_at')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->text('public_key');
            $table->text('private_key');
            $table->string('device_id')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jwt_auth');
    }
};
