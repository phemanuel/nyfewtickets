<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ticket_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->restrictOnDelete();
            $table->string('reference', 80)->unique();
            $table->string('buyer_name', 180);
            $table->string('buyer_email', 190);
            $table->string('buyer_phone', 40)->nullable();
            $table->char('access_hash', 64);
            $table->unsignedBigInteger('amount_kobo');
            $table->char('currency', 3)->default('NGN');
            $table->string('status', 30)->default('pending')->index(); // pending, paid, failed, paid_unfulfilled
            $table->timestamp('expires_at')->index();
            $table->timestamp('paid_at')->nullable();
            $table->string('paystack_transaction_id', 100)->nullable();
            $table->text('payment_issue')->nullable();
            $table->timestamps();
        });

        Schema::create('ticket_order_attendees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_order_id')->constrained('ticket_orders')->cascadeOnDelete();
            $table->foreignId('ticket_category_id')->constrained('ticket_categories')->restrictOnDelete();
            $table->foreignId('ticket_id')->nullable()->unique()->constrained('tickets')->nullOnDelete();
            $table->string('full_name', 180);
            $table->string('email', 190)->nullable();
            $table->string('phone', 40)->nullable();
            $table->unsignedBigInteger('unit_price_kobo');
            $table->timestamps();
            $table->index(['ticket_category_id', 'ticket_order_id']);
        });

        Schema::table('tickets', function (Blueprint $table) {
            // Allows a purchaser to retrieve the QR ticket after Paystack redirects them.
            $table->text('qr_token_encrypted')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('tickets', fn (Blueprint $table) => $table->dropColumn('qr_token_encrypted'));
        Schema::dropIfExists('ticket_order_attendees');
        Schema::dropIfExists('ticket_orders');
    }
};
