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
        Schema::table('users', function (Blueprint $table) {
            // 1. Eliminamos explícitamente la restricción única vieja que está molestando
            $table->dropUnique('users_email_unique');
        });

        Schema::table('users', function (Blueprint $table) {
            // 2. Modificamos la columna para que sea nullable y le volvemos a poner el unique
            $table->string('email')->nullable()->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Para revertir, quitamos el nulo (limpiando nulos previos si existieran)
            $table->string('email')->nullable(false)->change();
        });
    }
};