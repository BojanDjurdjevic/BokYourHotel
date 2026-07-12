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
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['room_id']);
            $table->dropColumn('room_id');

            $table->dropColumn('total_price');

            $table->decimal('subtotal', 10,2)->default(0);
            $table->decimal('discount', 10,2)->default(0);
            $table->decimal('tax', 10,2)->default(0);
            $table->decimal('total', 10,2)->default(0);

            $table->string('currency', 3)->default('EUR');

            $table->foreignId('hotel_id')
            ->after('id')
            ->constrained()
            ->cascadeOnDelete();

            $table->string('booking_number')
            ->unique();

            $table->string('guest_name')->nullable();

            $table->string('guest_email')->nullable();

            $table->string('guest_phone')
            ->nullable();

            $table->text('notes')
            ->nullable();

            $table->index([
                'hotel_id',
                'status'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            //
        });
    }
};
