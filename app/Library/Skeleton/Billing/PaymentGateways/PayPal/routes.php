<?php
declare(strict_types=1);

Route::group(['prefix' => 'payment', 'middleware' => []] , function() {
	Route::post('/paypal', 'PayPal@index')->middleware(['web', 'auth'])->name('paypal_payment_request');
	Route::any('/paypal/success', 'PayPal@subscription_approved')->middleware(['web', 'auth'])->name('paypal_approval_redir_url');
	Route::any('/ipn/paypal', 'PayPal@ipn')->name('paypal_ipn_request');
});
