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
        Schema::create('connected_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('stripe_id');
            $table->string('stripe_status');
            $table->string('connected_price_id');
            $table->unsignedBigInteger('quantity')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
            $table->string('stripe_customer_id')->index();
            $table->string('stripe_account_id')->index()->nullable(); // FOR RELATING A CONNECTED CUSTOMER MODEL TO A CONNECTED ACCOUNT
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('connected_subscriptions');
    }
};
