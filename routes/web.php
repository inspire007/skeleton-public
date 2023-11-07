<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::post('{locale}/setlocale', ['uses' => 'SetLocale@setlang']);

Route::group(['prefix' => LaravelLocalization::setLocale(), 'middleware' => ['localizationRedirect', 'localeSessionRedirect']] , function() {
	
	$auth_options = array('verify' => true);
	
	if(!env('REGISTRATION_ENABLED')){
		$auth_options['register'] = false;
	}
	
	Auth::routes($auth_options);
	
	Route::get('/', 'HomeController@index')->name('home');
	Route::get('/about', 'HomeController@about')->name('about');
	Route::get('/features', 'HomeController@features')->name('features');
	Route::get('/pricing', 'HomeController@pricing')->name('pricing');
	Route::get('/support', 'HomeController@support')->name('support');
	Route::get('/contact', 'HomeController@contact')->name('contact');
	Route::get('/sitemap', 'HomeController@sitemap')->name('sitemap');
	Route::get('/error', 'HomeController@error')->name('error');
	Route::get('/success', 'HomeController@success')->name('success');
	Route::get('/warning', 'HomeController@warning')->name('warning');
	Route::post('/login/social', 'Auth\LoginController@social')->name('social_login');
	
	Route::group(['prefix' => 'sudo', 'middleware' => ['auth', 'verified', 'superadmin']] , function() {
		Route::get('/dashboard', 'SuperAdminController@dashboard')->name('superadmin_dashboard');
	});
	
	Route::group(['prefix' => 'admin', 'middleware' => ['auth', 'verified', 'admin']] , function() {
		Route::get('/dashboard', 'AdminController@dashboard')->name('admin_dashboard');
	});
	
	Route::group(['prefix' => 'user', 'middleware' => ['auth', 'verified']] , function() {
		Route::get('/dashboard', 'UserController@dashboard')->name('user_dashboard');
	});
	
	Route::group(['prefix' => 'payment', 'middleware' => ['auth'], 'as' => 'payment.'] , function() {
		Route::get('/thankyou', 'PaymentRequestHandler@thankyou')->name('thankyou');
		Route::get('/cancel', 'PaymentRequestHandler@cancel')->name('cancel');
	});
	
	Route::group(['prefix' => 'membership', 'as' => 'membership.', 'middleware' => ['auth', 'throttle:30,1']] , function() {
		Route::any('/', 'MembershipRequest@index')->name('request');
		Route::post('/payment', 'MembershipRequest@payment')->name('payment');
		Route::post('/zero', 'MembershipRequest@zero')->name('zero');
	});

});