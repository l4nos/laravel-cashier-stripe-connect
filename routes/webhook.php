<?php

use Illuminate\Support\Facades\Route;
use Lanos\CashierConnect\Http\Controllers;

Route::post('/connectWebhook', [Controllers\WebhookController::class, 'handleWebhook'])->name('stripeConnect.webhook');
