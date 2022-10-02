<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the Stripe Account columns for the user.
 */
return new class extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stripe_connect_mappings', function (Blueprint $table) {
            $table->json('future_requirements')->nullable();
            $table->boolean('charges_enabled')->default(false);
            $table->boolean('first_onboarding_done')->default(false);
            $table->json('requirements')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stripe_connect_mappings', function (Blueprint $table) {
            $table->dropColumn('future_requirements');
            $table->dropColumn('charges_enabled');
            $table->dropColumn('first_onboarding_done');
            $table->dropColumn('requirements');
        });
    }
};
