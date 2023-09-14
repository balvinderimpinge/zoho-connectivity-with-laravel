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
        Schema::create('zoho_accesses', function (Blueprint $table) {
            $table->id();
            $table->string('client_id',500);
            $table->string('client_secret',500);
            $table->string('refresh_token',500);
            $table->string('access_token',500);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('zoho_accesses');
    }
};
