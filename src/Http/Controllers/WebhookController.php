<?php

namespace Lanos\CashierConnect\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Lanos\CashierConnect\Events\ConnectWebhookHandled;
use Lanos\CashierConnect\Events\ConnectWebhookReceived;
use Lanos\CashierConnect\Http\Middleware\VerifyConnectWebhook;
use Stripe\Stripe;
use Symfony\Component\HttpFoundation\Response;

class WebhookController extends Controller
{

    /**
     * Create a new WebhookController instance.
     *
     * @return void
     */
    public function __construct()
    {
        if (config('cashier.webhook.secret') || config('cashierconnect.webhook.secret')) {
            $this->middleware(VerifyConnectWebhook::class);
        }
    }

    /**
     * Handle a Stripe webhook call.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handleWebhook(Request $request)
    {
        $payload = json_decode($request->getContent(), true);
        $method = 'handle'.Str::studly(str_replace('.', '_', $payload['type']));

        ConnectWebhookReceived::dispatch($payload);

        if (method_exists($this, $method)) {
            $this->setMaxNetworkRetries();

            $response = $this->{$method}($payload);

            ConnectWebhookHandled::dispatch($payload);

            return $response;
        }

        return $this->missingMethod($payload);
    }

    /**
     * Handle successful calls on the controller.
     *
     * @param  array  $parameters
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function successMethod($parameters = [])
    {
        return new Response('Webhook Handled', 200);
    }

    /**
     * Handle calls to missing methods on the controller.
     *
     * @param  array  $parameters
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function missingMethod($parameters = [])
    {
        return new Response;
    }

    /**
     * Set the number of automatic retries due to an object lock timeout from Stripe.
     *
     * @param  int  $retries
     * @return void
     */
    protected function setMaxNetworkRetries($retries = 3)
    {
        Stripe::setMaxNetworkRetries($retries);
    }

}