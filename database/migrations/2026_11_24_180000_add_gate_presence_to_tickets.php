<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('gate_state', 20)->nullable()->after('checked_in_at');
            $table->timestamp('last_entered_at')->nullable()->after('gate_state');
            $table->timestamp('last_exited_at')->nullable()->after('last_entered_at');
            $table->index(['event_id', 'gate_state']);
        });
        // Preserve historic first entry while setting current occupancy for older tickets.
        DB::table('tickets')->whereNotNull('checked_in_at')->update([
            'gate_state' => 'inside',
            'last_entered_at' => DB::raw('checked_in_at'),
        ]);
        DB::table('tickets')->whereNull('checked_in_at')->update(['gate_state' => 'not_arrived']);
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex(['event_id', 'gate_state']);
            $table->dropColumn(['gate_state', 'last_entered_at', 'last_exited_at']);
        });
    }
};
