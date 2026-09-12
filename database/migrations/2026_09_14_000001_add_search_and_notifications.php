<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
    public function up(): void {
        Schema::table('hotels', fn (Blueprint $table) => $table->unsignedTinyInteger('star_rating')->nullable());
        // Only explicitly marked fictional properties receive illustrative demo ratings.
        DB::table('hotels')->where('name', 'like', 'Demo Aurelune House %')->update(['star_rating' => 5]);
        DB::table('hotels')->where('name', 'like', 'Demo Veloria Terrace %')->update(['star_rating' => 4]);
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('notifications');
        Schema::table('hotels', fn (Blueprint $table) => $table->dropColumn('star_rating'));
    }
};
