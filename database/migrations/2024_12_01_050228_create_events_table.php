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
            $table->string('name');
            $table->string('type');
            $table->text('description');
            $table->json('tags')->nullable();
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->string('location_link');
            $table->integer('attendance_capacity');
            $table->string('ticket_pricing');
            $table->double('ticket_price')->nullable();
            $table->string('status')->nullable();
            $table->string('event_url')->nullable();
            $table->boolean('draft')->nullable();
            $table->double("revenue")->nullable();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->timestamps();
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
