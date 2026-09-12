<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('room_inventories', function (Blueprint $table) {
            $table->unsignedBigInteger('version')->default(1);
        });
        Schema::table('bookings', function (Blueprint $table) {
            $table->index(['status', 'locked_until', 'id'], 'bookings_expiry_scan_index');
        });
        // Existing reservations receive a rollout grace period, not immediate expiry.
        DB::table('bookings')->where('status', 'pending')->whereNull('locked_until')
            ->update(['locked_until' => now()->addMinutes(30)]);
        Schema::create('demo_seed_runs', function (Blueprint $table) {
            $table->string('name')->primary();
            $table->date('anchor_date');
            $table->json('summary')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demo_seed_runs');
        Schema::table('bookings', fn (Blueprint $table) => $table->dropIndex('bookings_expiry_scan_index'));
        Schema::table('room_inventories', fn (Blueprint $table) => $table->dropColumn('version'));
        // Do not erase real booking deadlines on rollback.
    }
};
