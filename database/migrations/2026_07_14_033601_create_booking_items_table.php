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
        Schema::create('booking_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('board_type_id')->constrained()->cascadeOnDelete();

            $table->string('room_name');
            $table->string('board_name');

            $table->unsignedInteger('quantity')->default(1);

            $table->unsignedTinyInteger('adults');
            $table->unsignedTinyInteger('children');

            $table->decimal('price_per_night', 10,2);
            $table->decimal('subtotal', 10,2);

            $table->unsignedSmallInteger('nights');
            $table->date('check_in');
            $table->date('check_out');

            $table->string('currency', 3)->default('EUR');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_items');
    }
};
