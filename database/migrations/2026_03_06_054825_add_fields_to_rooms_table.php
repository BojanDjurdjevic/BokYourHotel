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
        Schema::table('rooms', function (Blueprint $table) {
            $table->foreignId('room_type_id')
                  ->after('hotel_id')
                  ->constrained()
                  ->restrictOnDelete();

            $table->foreignId('bed_type_id')
                  ->after('room_type_id')
                  ->constrained()
                  ->restrictOnDelete();

            
            $table->text('description')
                  ->nullable()
                  ->after('name');

            
            $table->boolean('has_balcony')
                  ->default(false)
                  ->after('total_units');

            $table->boolean('has_minibar')
                  ->default(false)
                  ->after('has_balcony');

            $table->boolean('has_air_condition')
                  ->default(false)
                  ->after('has_minibar');

            $table->boolean('has_tv')
                  ->default(false)
                  ->after('has_air_condition');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropForeign(['room_type_id']);
            $table->dropForeign(['bed_type_id']);

            $table->dropColumn([
                'room_type_id',
                'bed_type_id',
                'description',
                'has_balcony',
                'has_minibar',
                'has_air_condition',
                'has_tv'
            ]);
        });
    }
};
