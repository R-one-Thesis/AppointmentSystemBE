<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('doctors_id');
            $table->json('services')->nullable();
            $table->date('date');
            $table->time('time_start');
            $table->decimal('duration', 8, 2)->nullable();
            $table->decimal('price', 10, 2);
            $table->boolean('booked')->default(false);
            $table->timestamps();

            $table->foreign('doctors_id')->references('id')->on('doctors');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('schedules');
    }
};
