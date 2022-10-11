<?php

use Illuminate\Support\Facades\Route;
use Lanos\CashierConnect\Controllers;

Route::get('/connectWebhook', [\Lanos\CashierConnect\Http\Controllers\WebhookController::class, 'handleWebhook'])->name('stripeConnect.webhook');
