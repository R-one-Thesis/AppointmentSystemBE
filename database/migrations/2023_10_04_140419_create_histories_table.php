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
        Schema::create('histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id');
            $table->json('physician_data')->nullable();
            $table->json('hospitalizations_data')->nullable();
            $table->json('surgery_data')->nullable();
            $table->json('illness_disease')->nullable();
            $table->json('medication')->nullable();
            $table->json('allergies')->nullable();
            $table->json('pregnancy')->nullable();
            $table->json('menstrual_data')->nullable();
            $table->timestamps();

            $table->foreign('patient_id')->references('id')->on('patients');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('histories');
    }
};
