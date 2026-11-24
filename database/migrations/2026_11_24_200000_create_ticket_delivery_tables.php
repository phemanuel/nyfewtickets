<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ticket_access_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_order_id')->constrained('ticket_orders')->cascadeOnDelete();
            $table->char('token_hash', 64)->unique();
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
            $table->index(['ticket_order_id', 'expires_at']);
        });
        Schema::create('ticket_order_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_order_id')->unique()->constrained('ticket_orders')->cascadeOnDelete();
            $table->timestamp('queued_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('ticket_order_notifications');
        Schema::dropIfExists('ticket_access_links');
    }
};
