<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('room_inventory', 'room_inventories');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
