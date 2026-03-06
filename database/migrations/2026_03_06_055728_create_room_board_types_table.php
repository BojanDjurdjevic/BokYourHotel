<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_board_types', function (Blueprint $table) {
            $table->id();

            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('board_type_id')->constrained()->cascadeOnDelete();
            $table->decimal('price', 10, 2);

            $table->timestamps();

            $table->unique(['room_id', 'board_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_board_types');
    }
};
