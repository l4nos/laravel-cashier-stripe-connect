<?php

use Illuminate\Support\Facades\Route;
use Lanos\CashierConnect\Controllers;

Route::post('/connectWebhook', [\Lanos\CashierConnect\Http\Controllers\WebhookController::class, 'handleWebhook'])->name('stripeConnect.webhook');
