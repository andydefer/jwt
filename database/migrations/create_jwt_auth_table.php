<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('jwt_auth', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('jwt_token')->unique();
            $table->string('device_id');
            $table->string('ip_address');
            $table->text('user_agent');
            $table->boolean('is_jwt_auth')->default(true);
            $table->timestamp('jwt_issued_at');
            $table->timestamp('last_used_at');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('jwt_auth');
    }
};
