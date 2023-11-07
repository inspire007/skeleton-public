<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Library\Skeleton\Models\UserMemberships;
use App\Library\Skeleton\Models\MembershipPlans;
use App\Library\Skeleton\Billing\PaymentRequest;
use Auth;
use App;
use LaravelLocalization;

/**
 * MembershipRequest Controller
 * Handles membership requests and invoke payment method classes
 */
class MembershipRequest extends Controller
{
	/**
	 * Handles membership requests and offer payment methods available
	 */
    public function index(Request $request)
	{
		if($request->isMethod('get')){
			return redirect(LaravelLocalization::localizeUrl(route('pricing')));
		}
		
		$error = null;
		$plan_id = request()->input('plan_id');
		$is_recurring = request()->input('is_recurring') ? 1 : 0;
		$billing_interval = request()->input('billing_interval') ?: 1;
		
		$current_plan = Auth::user()->membership;
		$current_plan_details = $current_plan->plan;
		$requested_plan_details = MembershipPlans::find($plan_id);
		
		$trial_available = $current_plan->isTrialEligible();
		$expires_in = $current_plan->getDaysLeft();
		
		$payment_request = new PaymentRequest();
		$invoice = $payment_request->generate_invoice($current_plan, $current_plan_details, $requested_plan_details, $billing_interval, $is_recurring);
		
		$data = array(
			'page_title' => __('Purchase Membership'),
			'invoice' => $invoice,
			'current_plan' => $current_plan,
			'current_plan_details' => $current_plan_details,
			'current_plan_expires_in' => $expires_in,
			'requested_plan_details' => $requested_plan_details,
			'billing_interval' => $billing_interval,
			'billing_period' => MembershipPlans::get_bililing_period_from_interval($billing_interval),
			'is_recurring' => $is_recurring,
			'trial_available' => $trial_available,
			'input_error' => $error,
			'payment_gateways' => config('payment-gateways'),
			'mini_page' => true
		);
		
		return view('themes.'.$this->theme.'.payments.payment', $data);
	}
	
	/**
	 * Invokes payment method classes to process payment requests
	 */
	public function payment()
	{
		$gateway = request()->input('payment_gateway');
		$payment_cycle = request()->input('payment_cycle');
		
		$enabled = 0;
		$configs = config('payment-gateways');
		
		foreach( $configs as $gt ){
			if(!empty($configs[$gateway])){
				if($configs[$gateway]['is_enabled']){
					$enabled = 1;
				}
			}
		}
		
		if(!$enabled){
			return redirect_error(__('Sorry! This payment gateway is disabled.'));
		}
		
		try{
			$gatewayApp = App::make($configs[$gateway]['class']);
			if($payment_cycle == 'recurring'){
				return $gatewayApp->recurring_payment();
			}
			else return $gatewayApp->onetime_payment();
		}
		catch(Exception $e){
			return redirect_error( $e->getMessage() ); 
		}
	}
}
