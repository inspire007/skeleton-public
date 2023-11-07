<?php

return [
	'theme' => env('SITE_THEME', 'default'),
	'lang' => env('SITE_LANG', 'en'),
	'registration_enabled' => env('REGISTRATION_ENABLED', 0),
	'email_verify' => env('EMAIL_VERIFY', 0),
	'currency' => env('SITE_CURRENCY', 'USD'),
	'currency_symbol' => env('SITE_CURRENCY_SYMBOL', '$'),
	'fb_login_enabled' => env('FB_LOGIN_ENABLED', 0),
	'fb_api_key' => env('FB_API_KEY', ''),
	'fb_api_secret' => env('FB_API_SECRET', ''),
	'google_login_enabled' => env('GOOGLE_LOGIN_ENABLED', 0),
	'google_api_key' => env('GOOGLE_API_KEY', ''),
	'google_api_secret' => env('GOOGLE_API_SECRET', ''),
	'facebook_url' => env('FACEBOOK_URL', ''),
	'instagram_url' => env('INSTAGRAM_URL', ''),
	'twitter_url' => env('TWITTER_URL', ''),
	'reddit_url' => env('REDDIT_URL', ''),
	'description' => env('SITE_DESCRIPTION', ''),
	'meta_keywords' => env('SITE_METAKEYWORDS', ''),
	'author' => env('SITE_AUTHOR', ''),
	'license_key' => env('LICENSE_KEY', '')
]

?>