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
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('middle_name')->nullable();
            $table->string('extension_name')->nullable();
            $table->date('birthday');
            $table->string('sex');
            $table->string('religion')->nullable();
            $table->string('home_address');
            $table->string('home_phone_number')->nullable();
            $table->string('office_address')->nullable();
            $table->string('work_phone_number')->nullable();
            $table->string('mobile_number')->nullable();
            $table->string('marital_status');
            $table->string('spouse')->nullable();
            $table->string('person_responsible_for_the_account')->nullable();
            $table->string('person_responsible_mobile_number')->nullable();
            $table->string('relationship')->nullable();
            $table->string('referal_person')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('user');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('patients');
    }
};
