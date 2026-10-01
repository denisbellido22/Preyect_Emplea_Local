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
        Schema::create('oportunidades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('titulo');
            // beca | charla | capacitacion | curso | evento | otra
            $table->string('tipo');
            $table->text('descripcion');
            $table->string('institucion')->nullable();
            $table->date('fecha')->nullable();
            $table->string('lugar')->nullable();
            $table->string('enlace')->nullable();
            $table->string('imagen')->nullable();
            // publicado | borrador
            $table->string('estado')->default('publicado');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oportunidades');
    }
};
