<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ticket_guests', function (Blueprint $table) {
            $table->id();
            $table->string('full_name', 180);
            $table->string('email', 190)->nullable()->index();
            $table->string('phone', 40)->nullable()->index();
            $table->string('organisation', 180)->nullable();
            $table->string('photo_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->restrictOnDelete();
            $table->foreignId('ticket_category_id')->constrained('ticket_categories')->restrictOnDelete();
            $table->foreignId('ticket_guest_id')->constrained('ticket_guests')->restrictOnDelete();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('number', 50)->unique();
            $table->char('token_hash', 64)->unique();
            $table->string('kind', 30)->default('invitation');
            $table->string('status', 25)->default('issued');
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
            $table->index(['ticket_category_id','status']);
            $table->index(['event_id','status']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('tickets');
        Schema::dropIfExists('ticket_guests');
    }
};
