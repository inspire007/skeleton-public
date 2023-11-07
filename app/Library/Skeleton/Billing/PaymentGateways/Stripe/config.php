<?php

return [
	'name' => 'Stripe',
	'class' => App\Library\Skeleton\Billing\PaymentGateways\Stripe\Stripe::class,
	'tagline' => 'Stripe - Major credit cards',
	'path' => 'Stripe',
	'icon' => 'cc stripe',
	'route' => 'payment.stripe.request',
	'is_enabled' => env('STRIPE_ENABLED', 0),
	'api_key' => env('STRIPE_API_KEY', ''),
	'api_secret' => env('STRIPE_API_SECRET', ''),
	'mode' => env('STRIPE_MODE', 'Sandbox')
];