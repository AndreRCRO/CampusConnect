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
        Schema::create('student_requests', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_code')->unique();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('institutional_resource_id')->nullable()->constrained('institutional_resources')->onDelete('set null');
            $table->string('category'); // mantenimiento, soporte_tecnologico, infraestructura, equipamiento, otro
            $table->string('title');
            $table->text('description');
            $table->string('priority')->default('media'); // baja, media, alta, urgente
            $table->string('status')->default('pendiente'); // pendiente, en_proceso, resuelto, cerrado, rechazado
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_requests');
    }
};
