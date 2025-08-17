<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('date');
            $table->string('location')->nullable();
            $table->string('event_type');
            $table->boolean('is_recurring')->default(false);
            $table->string('recurrence_pattern')->nullable();
            $table->integer('reminder')->nullable(); // minutes
            $table->timestamps();

            $table->index(['user_id', 'date']);
            $table->index(['user_id', 'event_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
