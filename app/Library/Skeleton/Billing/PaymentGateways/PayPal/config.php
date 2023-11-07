<?php

return [
	'name' => 'PayPal',
	'class' => 'App\Library\Skeleton\PaymentGateways\PayPal\PayPal',
	'tagline' => 'PayPal - Balance or credit cards',
	'path' => 'PayPal',
	'icon' => 'cc paypal',
	'route' => 'paypal_payment_request',
	'is_enabled' => env('PAYPAL_ENABLED'),
	'api_key' => env('PAYPAL_API_KEY'),
	'api_secret' => env('PAYPAL_API_SECRET'),
	'mode' => env('PAYPAL_MODE')
];