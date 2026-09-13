<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $historyKeys = [
        ['hotels', 'supplier_id', 'users'],
        ['bookings', 'hotel_id', 'hotels'],
        ['booking_items', 'booking_id', 'bookings'],
        ['booking_items', 'room_id', 'rooms'],
        ['booking_items', 'board_type_id', 'board_types'],
    ];

    public function up(): void
    {
        foreach (['hotels', 'rooms', 'board_types'] as $name) {
            Schema::table($name, fn (Blueprint $table) => $table->timestamp('archived_at')->nullable()->index());
        }
        Schema::table('users', fn (Blueprint $table) => $table->timestamp('supplier_deactivated_at')->nullable());
        $this->changeHistoryKeys('restrict');
    }

    public function down(): void
    {
        $this->changeHistoryKeys('cascade');
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('supplier_deactivated_at'));
        foreach (['hotels', 'rooms', 'board_types'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->dropIndex(['archived_at']);
                $table->dropColumn('archived_at');
            });
        }
    }

    private function changeHistoryKeys(string $action): void
    {
        foreach ($this->historyKeys as [$name, $column, $parent]) {
            Schema::table($name, function (Blueprint $table) use ($column, $parent, $action) {
                $table->dropForeign([$column]);
                $table->foreign($column)->references('id')->on($parent)->onDelete($action);
            });
        }
    }
};
