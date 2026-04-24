<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds meter columns for Cashier 16.x metered billing support.
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
        Schema::table('connected_subscription_items', function (Blueprint $table) {
            $table->string('meter_event_name')->nullable()->after('quantity');
            $table->string('meter_id')->nullable()->after('meter_event_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('connected_subscription_items', function (Blueprint $table) {
            $table->dropColumn(['meter_event_name', 'meter_id']);
        });
    }
};
