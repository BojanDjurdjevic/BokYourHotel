<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('room_inventories', fn (Blueprint $table) => $table->index(['date','room_id'], 'inventory_search_dates_index'));
    }
    public function down(): void {
        Schema::table('room_inventories', fn (Blueprint $table) => $table->dropIndex('inventory_search_dates_index'));
    }
};
