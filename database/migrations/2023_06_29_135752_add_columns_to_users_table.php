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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone',20)->after('remember_token')->nullable();
            $table->string('type',20)->after('phone')->nullable(); // individual or business
            $table->string('business_name',100)->after('type')->nullable();
            $table->string('closest_metro',100)->after('business_name')->nullable();
            $table->string('bio_desc',500)->after('closest_metro')->nullable();
            $table->string('zoho_crm_contact_id',100)->after('bio_desc')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('phone');
            $table->dropColumn('type');
            $table->dropColumn('business_name');
            $table->dropColumn('closest_metro');
            $table->dropColumn('bio_desc');
            $table->dropColumn('zoho_crm_contact_id');
        });
    }
};
