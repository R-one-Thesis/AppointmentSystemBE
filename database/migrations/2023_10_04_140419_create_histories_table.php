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
            $table->string('physician')->nullable();
            $table->string('physaddress')->nullable();
            $table->string('reason')->nullable();
            $table->string('hospitalization_reason')->nullable();
            $table->json('conditions')->nullable();
            $table->json('medication')->nullable();
            $table->json('allergies')->nullable();
            $table->boolean('pregnant')->nullable();
            $table->date('expected_date')->nullable();
            $table->string('mens_problems')->nullable();
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
