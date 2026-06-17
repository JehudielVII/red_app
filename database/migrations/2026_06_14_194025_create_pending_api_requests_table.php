<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pending_api_requests', function (Blueprint $table) {
            $table->id();
            $table->string('method'); // POST, PUT, DELETE
            $table->string('endpoint');
            $table->json('payload')->nullable();
            $table->integer('attempts')->default(0);
            $table->text('last_error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pending_api_requests');
    }
};
