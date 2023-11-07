<?php
declare(strict_types=1);

Route::group(['prefix' => 'payment', 'middleware' => [], 'as' => 'payment.stripe.'] , function() {
	Route::post('/stripe', 'Stripe@index')->middleware(['web', 'auth'])->name('request');
	Route::any('/stripe/success', 'Stripe@subscription_approved')->middleware(['web', 'auth'])->name('approved');
	Route::any('/ipn/stripe', 'Stripe@ipn')->name('ipn');
});
