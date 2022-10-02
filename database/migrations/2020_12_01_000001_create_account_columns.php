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
        Schema::create('stripe_connect_mappings', function (Blueprint $table) {
            $table->string('model');
            $table->unsignedBigInteger('model_id')->nullable()->index();;
            $table->uuid('model_uuid')->nullable()->index();
            $table->string('stripe_account_id')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stripe_connect_mappings');
    }
};
