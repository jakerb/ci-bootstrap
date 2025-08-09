<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Stripe Configuration
| -------------------------------------------------------------------------
| This file stores the credentials and settings used for interacting
| with the Stripe API.  To begin accepting subscriptions you will
| need to create an account at https://dashboard.stripe.com/ and
| obtain an API secret key and a price ID for the subscription
| product you wish to sell.  For security you can expose these
| secrets via environment variables rather than hard‑coding them.
|
| stripe_secret_key   Your secret API key.  Used for server side
|                     requests to Stripe.  Do not share this in
|                     frontend code.
| stripe_public_key   The publishable key.  Can be exposed to
|                     clients if you decide to implement client side
|                     checkout flows.
| stripe_price_id     The price ID representing your subscription
|                     plan.  Create this in the Stripe dashboard.
| stripe_success_path The path that Stripe should redirect back to
|                     once the checkout session completes successfully.
| stripe_cancel_path  The path that Stripe should redirect to when
|                     a user cancels or fails to complete checkout.
*/

$config['stripe_secret_key']   = defined('STRIPE_SECRET_KEY') ? constant('STRIPE_SECRET_KEY') : '';
$config['stripe_public_key']   = defined('STRIPE_PUBLIC_KEY') ? constant('STRIPE_PUBLIC_KEY') : '';

/*
| -------------------------------------------------------------------------
| Multiple subscription tiers
| -------------------------------------------------------------------------
| To support multiple subscription tiers (e.g. basic, standard and pro)
| you can specify separate price IDs for each tier below.  These IDs
| correspond to the prices you configure in your Stripe dashboard.  You
| should set the environment variables STRIPE_PRICE_BASIC,
| STRIPE_PRICE_STANDARD and STRIPE_PRICE_PRO to override the defaults.
| The `stripe_default_plan` option controls which tier is used when no
| specific plan is requested.
*/
$config['stripe_price_basic']    = defined('STRIPE_PRICE_BASIC')    ? constant('STRIPE_PRICE_BASIC')    : 'PRICE_BASIC_ID_HERE';
$config['stripe_price_standard'] = defined('STRIPE_PRICE_STANDARD') ? constant('STRIPE_PRICE_STANDARD') : 'PRICE_STANDARD_ID_HERE';
$config['stripe_price_pro']      = defined('STRIPE_PRICE_PRO')      ? constant('STRIPE_PRICE_PRO')      : 'PRICE_PRO_ID_HERE';
$config['stripe_default_plan']   = defined('STRIPE_DEFAULT_PLAN')   ? constant('STRIPE_DEFAULT_PLAN')   : 'basic';
$config['stripe_success_path'] = 'subscription/success';
$config['stripe_cancel_path']  = 'subscription/cancel';

$config['stripe_success_path'] = 'subscription/success';
$config['stripe_cancel_path']  = 'subscription/cancel';