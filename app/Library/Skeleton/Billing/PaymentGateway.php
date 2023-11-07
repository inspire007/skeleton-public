<?php

namespace App\Library\Skeleton\Billing;

use App;

/**
 * PaymentGateway Model
 * Bridge between payment gateway and user-memberships
 * @package App\Library\Skeleton\Billing
 */
class PaymentGateway
{
	protected $gateway;
	
	/**
     * @param $name payment gateway name e.g. Stipe|PayPal|Braintree
     */
	public function __construct($name)
	{
		$this->gateway = $name;
	}
	
	/**
     * @return App\Library\Skeleton\Billing\PaymentGateways\{gateway-name}
	 * returns an object of payment gateway class
     */
	public function make()
	{
		return App::make( config('payment-gateways.'.$this->gateway.'.class') );
	}
	
}