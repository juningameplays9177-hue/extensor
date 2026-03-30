<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rentals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('container_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('depot_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('street');
            $table->string('number');
            $table->string('complement')->nullable();
            $table->timestamp('allocated_at');
            $table->timestamp('removed_at')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rentals');
    }
};
