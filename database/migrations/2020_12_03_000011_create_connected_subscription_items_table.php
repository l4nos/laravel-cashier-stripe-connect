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
        Schema::create('connected_subscription_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('connected_subscription_id'); // THE INCREMENTING ID OF THE CONNECTED SUBSCRIPTION
            $table->string('stripe_id'); // THE SI ID IN STRIPE
            $table->string('connected_product'); // THE ID OF THE PRODUCT WITHIN THE CONNECTED ACCOUNT
            $table->string('connected_price'); // THE ID OF THE PRICE WITHIN THE CONNECTED ACCOUNT
            $table->unsignedBigInteger('quantity');
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
        Schema::dropIfExists('connected_subscription_items');
    }
};
