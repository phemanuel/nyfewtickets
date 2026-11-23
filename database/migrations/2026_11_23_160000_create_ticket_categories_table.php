<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ticket_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('slug', 150);
            $table->text('description')->nullable();
            $table->string('type', 20)->default('paid'); // paid | complimentary
            $table->unsignedBigInteger('price_kobo')->default(0);
            $table->unsignedInteger('capacity');
            $table->dateTime('sales_start_at')->nullable();
            $table->dateTime('sales_end_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['event_id','slug']);
            $table->index(['event_id','is_active']);
        });
    }
    public function down(): void { Schema::dropIfExists('ticket_categories'); }
};
