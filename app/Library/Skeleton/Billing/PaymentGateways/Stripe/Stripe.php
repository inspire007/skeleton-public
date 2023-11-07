<?php

namespace App\Library\Skeleton\Billing\PaymentGateways\Stripe;

use Illuminate\Http\Request;
use Auth;
use Session;
use DB;
use LaravelLocalization;
use Stripe\Stripe as StripeSDK;
use Stripe\StripeClient;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Webhook;
use Stripe\Coupon;
use Stripe\WebhookEndpoint;
use Carbon\Carbon;
use App\Library\Skeleton\Models\PaymentsLog;
use App\Library\Skeleton\Models\MembershipPlans;
use App\Library\Skeleton\Models\UserMemberships;
use App\Library\Skeleton\Models\Invoice;

class Stripe
{
	protected $delete_hooks;
	protected $theme;
	
	protected $stripe_client;
	protected $stripe_product_id;
	protected $stripe_price_id;
	protected $stripe_coupon_id;
	
	protected $invoice;
	
	
	public function __construct()
	{
		$this->theme = config('site.theme');
	}
	
	
	protected function serialize_input()
	{
		$invoice_id = request()->input('invoice_id');
		$invoice = Invoice::find($invoice_id);
		if(!$invoice)abort(401);
		
		$plan_id = $invoice->plan_id;
		$plan = MembershipPlans::find($plan_id);
		if(!$plan)abort(401);
		
		$this->invoice = $invoice;
	}
	
	protected function prepare_stripe_client()
	{
		$newCreated = 0;
		$stripe_products = array();
		if(file_exists(__DIR__.'/products.json')){
			$content = file_get_contents(__DIR__.'/products.json');
			$stripe_products = json_decode($content, true);
		}
		
		$stripe = new StripeClient(
		  config('payment-gateways.Stripe.api_secret')
		);
		$this->stripe_client = $stripe;

		if(!empty($stripe_products[$this->invoice->plan_id])){
			$productId = $stripe_products[$this->invoice->plan_id]['product_id'];
		}
		else{
			$newCreated = 1;
			$prod = $stripe->products->create([
			  'name' => config('app.name').'-Plan-'.$this->invoice->plan_id,
			  'metadata' => [
				'plan_id' => $this->invoice->plan_id
			  ]
			]);
			if(empty($prod['id'])){
				return redirect_error(__('Failed to create stripe product. Please contact site administrators.'));
			}
			$productId = $prod['id'];
			$this->stripe_product_id = $productId;
		}
		
		$createPricingId = 0;
		if(!empty($stripe_products[$this->invoice->plan_id])){
			$plans = $stripe_products[$this->invoice->plan_id]['plans'];
			if(!empty($plans[$this->invoice->billing_interval])){
				$pricingId = $plans[$this->invoice->billing_interval];
				$this->stripe_price_id	= $pricingId;
			}
			else $createPricingId = 1;
		}
		else $createPricingId = 1;
		
		if($createPricingId){
			$newCreated = 1;
			$pricing = $stripe->prices->create([
			  'unit_amount' => $this->invoice->billing_price * 100,
			  'currency' => config('site.currency'),
			  'recurring' => ['interval' => 'month', "interval_count" => $this->invoice->billing_interval],
			  'product' => $productId,
			  'metadata' => [
				'plan_id' => $this->invoice->plan_id
			  ]
			]);
			
			if(empty($pricing['id'])){
				return redirect_error(__('Failed to create stripe pricing. Please contact site administrators.'));
			}
			$pricingId = $pricing['id'];
			$this->stripe_price_id	= $pricingId;
		}

		$hook_url = 'https://597afe4a4909.ngrok.io/projects/skeleton/public/payment/ipn/stripe';
		////LaravelLocalization::localizeUrl(route('payment.stripe.ipn'));
		
		$createHook = !$this->get_webhook_pass();
		
		if($createHook){
			$newCreated = 1;
			try{
				$endpoint = $stripe->webhookEndpoints->create([
				  'url' => $hook_url,
				  'enabled_events' => [
					'customer.subscription.created',
					'invoice.payment_succeeded'
				  ],
				]);
				
				$wh_id = $endpoint['id'];
				$wh_pass = $endpoint['secret'];
				if(empty($stripe_products['webhooks']))$stripe_products['webhooks'] = array();
				$stripe_products['webhooks'][] = array('url' => $hook_url, 'pass' => $wh_pass, 'id' => $wh_id);
				
			}catch(\Exception $e){
				return redirect_error(__('Failed to create webhook. Please contact site administrators.'));
			}
		}
		
		if(!empty($this->delete_hooks)){
			foreach($this->delete_hooks as $dh){
				try{
					$stripe->webhookEndpoints->delete($dh);
				}catch(\Exception $e){
				}
			}
		}
		
		if($newCreated){
			if(empty($stripe_products[$this->invoice->plan_id]))$stripe_products[$this->invoice->plan_id] = array('product_id' => $productId);
			if(empty($stripe_products[$this->invoice->plan_id]['plans']))$stripe_products[$this->invoice->plan_id]['plans'] = array();
			$stripe_products[$this->invoice->plan_id]['plans'][$this->invoice->billing_interval] = $pricingId;
			
			file_put_contents(__DIR__.'/products.json', json_encode($stripe_products));
		}

		$coupon_id = null;
		//create coupon if amount is not equal to to_pay
		if($this->invoice->billing_price > $this->invoice->amount_due){
			try{
				$coupon = $stripe->coupons->create([
				  'duration' => 'once',
				  'max_redemptions' => 1,
				  'amount_off' => ($this->invoice->billing_price - $this->invoice->amount_due) * 100,
				  'id' => $coupon_id,
				  'duration' => 'once',
				  'currency' => config('site.currency'),
				  'redeem_by' => Carbon::now()->addHour()->timestamp
				]);
				$coupon_id = $coupon['id'];
				$this->stripe_coupon_id = $coupon_id;
			}
			catch(Exception $e){
				return redirect_error(__('Failed to apply remaining balance on stripe invoice. Please contact site administrators.'));
			}
		}
	}
	
	public function recurring_payment()
	{
		$this->serialize_input();
		$this->prepare_stripe_client();
		
		$stripe = $this->stripe_client;
		
		$sessionData = [
			  'payment_method_types' => ['card'],
			  'line_items' => [[
				'price' => $this->stripe_price_id,
				'quantity' => 1,
				]],
			  'mode' => 'subscription',
			  'client_reference_id' => Auth::user()->id,
			  'success_url' => LaravelLocalization::localizeUrl(route('payment.stripe.approved')).'?session_id={CHECKOUT_SESSION_ID}',
			  'cancel_url' => LaravelLocalization::localizeUrl(route('payment.cancel')),
			  'subscription_data' => []
			];
			
		
		if($this->stripe_coupon_id){
			$sessionData['subscription_data']['coupon'] = $this->stripe_coupon_id;
		}			
		if($this->invoice->trial_days){
			$sessionData['subscription_data']['trial_period_days'] = $this->invoice->trial_days;
		}
		
		try{
			$session = $stripe->checkout->sessions->create($sessionData);
			Session::put('payment_init', $this->invoice->id);
			
			return view('themes.'.$this->theme.'.payments.striperedir', array('session_id' => $session['id'], 'mini_page' => true));
			
		}catch(\Exception $e){
			return redirect_error(__('Failed to create stripe session. Please contact site administrators.'));
		}
	}
	
	public function onetime_payment()
	{
		$this->serialize_input();
		
		
	}
	
	/**
	 * Receives payment request by POSTDATA = plan_id, billing_period (monthly/quarterly/yearly)
	 * Creates stripe product for the plan_id, price for the plan price, creates webhook if does not exist or deletes webhook if exists but does not match with current webhook
	 * Sets payment_init session and redirects to stripe
	 */
	public function payment()
	{
		
		
		if($billing_period == 'monthly')$billing_interval = 1;
		else if($billing_period == 'quarterly')$billing_interval = 3;
		else $billing_interval = 12;
		
		$newCreated = 0;
		$wh_pass = null;
		
		if(!$plan)abort(401);
		
		$amount = MembershipPlans::get_billing_price($plan, $billing_period);
		$trial = $plan->trial_duration;
		$is_recurring = (request()->input('payment_cycle') == 'recurring' ? 'recurring' : 'onetime') ;
		$trial_used = MembershipPlans::user_trial_used();
		
		$user_current_plan = Auth::user()->membership;
		$remaining_balance = $user_current_plan->getBalance();
		$remaining_days = $user_current_plan->getDaysLeft();
		
		$to_pay = get_pay_now_price($amount, $remaining_balance, $user_current_plan->plan_id, $plan_id);
		
		//MembershipPlans::
		
		$payments_log = new PaymentsLog();
		$payments_log->user_id = Auth::user()->id;
		$payments_log->plan_id = $plan_id;
		$payments_log->request_origin = 'cart';
		$payments_log->amount = $to_pay;
		$payments_log->billing_amount = $amount;
		$payments_log->billing_interval = $billing_interval;
		$payments_log->currency = config('site.currency');
		$payments_log->has_trial = $trial && !$trial_used;
		$payments_log->payment_method = 'Stripe';
		$payments_log->created_at = now();
		
		$stripe_products = array();
		if(file_exists(__DIR__.'/products.json')){
			$content = file_get_contents(__DIR__.'/products.json');
			$stripe_products = json_decode($content, true);
		}
		
		$stripe = new StripeClient(
		  config('payment-gateways.Stripe.api_secret')
		);

		if(!empty($stripe_products[$plan_id])){
			$productId = $stripe_products[$plan_id]['product_id'];
		}
		else{
			$newCreated = 1;
			$prod = $stripe->products->create([
			  'name' => config('app.name').'-'.$plan->plan_name.'-'.$plan_id,
			  'metadata' => [
				'plan_id' => $plan_id
			  ]
			]);
			if(empty($prod['id'])){
				return redirect_error(__('Failed to create stripe product. Please contact site administrators.'));
			}
			$productId = $prod['id'];
		}
		
		$createPricingId = 0;
		if(!empty($stripe_products[$plan_id])){
			$plans = $stripe_products[$plan_id]['plans'];
			if(!empty($plans[$billing_interval]))$pricingId = $plans[$billing_interval];
			else $createPricingId = 1;
		}
		else $createPricingId = 1;
		
		if($createPricingId){
			$newCreated = 1;
			$pricing = $stripe->prices->create([
			  'unit_amount' => $amount * 100,
			  'currency' => config('site.currency'),
			  'recurring' => ['interval' => 'month', "interval_count" => $billing_interval],
			  'product' => $productId,
			  'metadata' => [
				'plan_id' => $plan_id
			  ]
			]);
			
			if(empty($pricing['id'])){
				return redirect_error(__('Failed to create stripe pricing. Please contact site administrators.'));
			}
			$pricingId = $pricing['id'];
		}

		$hook_url = 'https://597afe4a4909.ngrok.io/projects/skeleton/public/payment/ipn/stripe';
		////LaravelLocalization::localizeUrl(route('stripe_ipn_request'));
		
		$createHook = !$this->get_webhook_pass();
		
		if($createHook){
			$newCreated = 1;
			try{
				$endpoint = $stripe->webhookEndpoints->create([
				  'url' => $hook_url,
				  'enabled_events' => [
					'customer.subscription.created',
					'invoice.payment_succeeded'
				  ],
				]);
				
				$wh_id = $endpoint['id'];
				$wh_pass = $endpoint['secret'];
				if(empty($stripe_products['webhooks']))$stripe_products['webhooks'] = array();
				$stripe_products['webhooks'][] = array('url' => $hook_url, 'pass' => $wh_pass, 'id' => $wh_id);
				
			}catch(\Exception $e){
				return redirect_error(__('Failed to create webhook. Please contact site administrators.'));
			}
		}
		
		if(!empty($this->delete_hooks)){
			foreach($this->delete_hooks as $dh){
				try{
					$stripe->webhookEndpoints->delete($dh);
				}catch(\Exception $e){
				}
			}
		}
		
		if($newCreated){
			if(empty($stripe_products[$plan_id]))$stripe_products[$plan_id] = array('product_id' => $productId);
			if(empty($stripe_products[$plan_id]['plans']))$stripe_products[$plan_id]['plans'] = array();
			$stripe_products[$plan_id]['plans'][$billing_interval] = $pricingId;
			
			file_put_contents(__DIR__.'/products.json', json_encode($stripe_products));
		}

		StripeSDK::setApiKey(config('payment-gateways.Stripe.api_secret'));
		
		$coupon_id = null;
		//create coupon if amount is not equal to to_pay
		if($amount != $to_pay && ($amount >= $to_pay)){
			try{
				$coupon = Coupon::create([
				  'duration' => 'once',
				  'max_redemptions' => 1,
				  'amount_off' => ($amount - $to_pay) * 100,
				  'id' => $coupon_id,
				  'duration' => 'once',
				  'currency' => config('site.currency'),
				  'redeem_by' => now()->addHour()->timestamp
				]);
				$coupon_id = $coupon['id'];
			}
			catch(Exception $e){
				return redirect_error(__('Failed to apply remaining balance on stripe invoice. Please contact site administrators.'));
			}
		}
		
		$sessionData = [
			  'payment_method_types' => ['card'],
			  'line_items' => [[
				'price' => $pricingId,
				'quantity' => 1,
				]],
			  'mode' => 'subscription',
			  'client_reference_id' => Auth::user()->id,
			  'success_url' => LaravelLocalization::localizeUrl(route('stripe_approval_redir_url')).'?session_id={CHECKOUT_SESSION_ID}',
			  'cancel_url' => LaravelLocalization::localizeUrl(route('payment_cancel')),
			  'subscription_data' => []
			];
			
		
		if($coupon_id){
			$sessionData['subscription_data']['coupon'] = $coupon_id;
			$payments_log->coupon_code = $coupon_id;
		}			
		if($trial && !$trial_used){
			$sessionData['subscription_data']['trial_period_days'] = ($trial && !$trial_used) ? $trial : 0;
		}
		
		if($user_current_plan && $amount < $user_current_plan->next_payment_amount){
			$sessionData['subscription_data']['trial_period_days'] = $remaining_days;
		}
		
		try{
			$session = StripeSession::create($sessionData);
			
			$payments_log->checkout_session_id = $session['id'];
			$payments_log->save();
			
			Session::put('payment_init', $payments_log->id);
			
			return view('themes.'.$this->theme.'.payments.striperedir', array('session_id' => $session['id']));
			
		}catch(Exception $e){
			return redirect_error(__('Failed to create stripe session. Please contact site administrators.'));
		}
	}
	
	/*
	 * Stripe ipn only uses invoice succeeded webhook to process membership requests. 
	 * When paid amount is zero it will assume it is trial attempt and validate if current user is eligible for trial
	 * When paid amount is not zero it will validate if paid amount is equal to membership plan amount and upgrade/extend membership as requested
	 * Important: Because we cannot pass userid into webhook, during subscription user will go to the thankyou page and we capture userid and corresponding subscriptionid from thankyou page
	 * Then when ipn results are delivered we match the subscriptionid with the userid stored into database from thankyou page
	 */
	public function ipn()
	{
		$pass = $this->get_webhook_pass();
		if(empty($pass))abort(401);
		
		StripeSDK::setApiKey(config('payment-gateways.Stripe.api_secret'));

		$payload = request()->getContent();
		$sig_header = request()->header('stripe_signature');
		$event = null;
		try {
			$event = Webhook::constructEvent(
				$payload, $sig_header, $pass
			);
		} catch(\Exception $e) {
			abort(401);
		} 
		
		switch ($event->type) {
			case 'invoice.payment_succeeded':
				
				$sub_id = $event->data->object->subscription;
				$invoice_id = $event->data->object->id;
				$invoice_status = $event->data->object->status;
				$s_plan = $event->data->object->lines->data[0]->plan;
				
				$init_payment_log = PaymentsLog::where('ref_id', $sub_id)->get()->first();
				if(!$init_payment_log)return;
				
				$invoice_exists = PaymentsLog::where('txn_id', $invoice_id)->get()->first();
				if($invoice_exists)return;
				
				$plan_id = $s_plan->metadata->plan_id;
				$plan = MembershipPlans::find($plan_id);
				if(!$plan){
					try{
						self::cancel_subscription($sub_id);
					}
					catch(\Exception $e){}
					return;
				}
				
				$to_pay = $init_payment_log->billing_amount;
				$to_pay_currency = $init_payment_log->currency;
				
				$payments_log = new PaymentsLog();
				
				$payments_log->user_id = $init_payment_log->user_id;
				$payments_log->plan_id = $plan_id;
				$payments_log->billing_amount = $init_payment_log->billing_amount;
				
				$payments_log->amount = $payments_log->paid_amount = $event->data->object->amount_paid / 100;
				$payments_log->currency = $payments_log->paid_currency = strtoupper($event->data->object->currency);
				
				$payments_log->payment_id = $event->data->object->customer_email;
				$payments_log->billing_interval = $s_plan->interval_count;
				
				$payments_log->payment_period_start = Carbon::createFromTimestamp($event->data->object->lines->data[0]->period->start)->toDateTimeString();
				$payments_log->payment_period_end = Carbon::createFromTimestamp($event->data->object->lines->data[0]->period->end)->toDateTimeString();
				
				$payments_log->raw_data = json_encode($event);
				$payments_log->txn_id = $invoice_id;
				$payments_log->ref_id = $sub_id;
				$payments_log->has_trial = 0;
				$payments_log->payment_method = 'Stripe';
				
				$invoice_duration_days = ($event->data->object->lines->data[0]->period->end - $event->data->object->lines->data[0]->period->start)/(3600*24);
								
				
				//custom validation only for stripe
				if($s_plan->interval != 'month'){
					$payments_log->reason = 'Payment interval is not month '.$s_plan->interval;
					$payments_log->txn_status = 'FAIL';
					$payments_log->save();
					try{		
						self::cancel_subscription($sub_id);
					}
					catch(\Exception $e){}
					return;
				}
				if($invoice_status != 'paid'){
					$payments_log->reason = 'Invoice is not paid';
					$payments_log->txn_status = 'FAIL';
					$payments_log->save();
					try{
						self::cancel_subscription($sub_id);
					}
					catch(\Exception $e){}
					return;
				}
				
				try{
					$this->validate_invoice($event->data->object, $to_pay*100, $to_pay_currency);
				}
				catch(\Exception $e){
					$error = $e->getMessage().':'.$e->getLine();
					$payments_log->reason = $error;
					$payments_log->txn_status = 'FAIL';
					$payments_log->save();
					try{
						self::cancel_subscription($sub_id);
					}
					catch(\Exception $e){}
					return;
				}
				
				
				try{
					$data = json_decode($payments_log->toJson(), true);
					$data['next_amount'] = $payments_log->billing_amount;
					MembershipPlans::upgrade_membership($plan_id, $payments_log->user_id, $invoice_duration_days, null, null,  $data);
					$payments_log->txn_comments = 'MEMBERSHIP_PROCESSED';
					
				}catch(\Exception $e){
					$error = $e->getMessage().':'.$e->getLine();
					$payments_log->txn_status = 'FAIL';
					$payments_log->txn_comments = 'FAILED_MEMBERSHIP_PROCESS';
					$payments_log->reason = $error;
					$payments_log->save();
					try{
						self::cancel_subscription($sub_id);
					}
					catch(\Exception $e){}
					return;
				}
				
				$payments_log->txn_status = 'SUCCESS';
				$payments_log->save();
				
				break;
				
			case 'customer.subscription.created':
				//nothing do to here yet
				//used invoice create webhook for payment processing
				break;
				
			default:
				return;
		}
		
	}
	
	public function subscription_approved()
	{
		//if(!Session::has('payment_init'))abort(401);
		//Session::forget('payment_init');
		
		$sess_id = request()->input('session_id');
		
		$warning = null;
		$payments_log = PaymentsLog::where('checkout_session_id', $sess_id)->where('txn_status', null)->get()->first();
		if(!$payments_log){
			abort(401);
		}
		
		$stripe = new StripeClient(
		  config('payment-gateways.Stripe.api_secret')
		);
		
		try{
			$session = $stripe->checkout->sessions->retrieve(
				$sess_id,
				[
					'expand' => ['subscription', 'customer']
				]
			);
		}
		catch(\Exception $e){
			$error = $e->getMessage().':'.$e->getLine();
			$payments_log->reason = $error;
			$payments_log->txn_status = 'FAIL';
			$payments_log->save();
			return redirect_error(__('Failed to capture stripe session. Please contact site administrators.').' '.$error);
		}
		
		$sess_data = json_decode($session->toJSON());
		
		$sub_id = $sess_data->subscription->id;
		$user_id = $payments_log->user_id;
		$plan_id = $payments_log->plan_id;
		
		$to_pay = $payments_log->amount;
		$to_pay_currency = $payments_log->currency;
		
		$invoice_id = $sess_data->subscription->latest_invoice;

		$plan = MembershipPlans::find($plan_id);
		if(!$plan){
			$payments_log->reason = 'Membership plan not found';
			$payments_log->txn_status = 'FAIL';
			$payments_log->save();
			try{
				self::cancel_subscription($sub_id, $stripe);
			}
			catch(\Exception $e){
				$warning = __('Failed to cancel your previous membership plan. Please cancel it from payment gateway.').' '.$e->getMessage().':'.$e->getLine();
			}
			return redirect_error(__('The requested membership plan was not found.'), $warning);
		}
		
		try{
			$invoice = $stripe->invoices->retrieve(
			  $invoice_id,
			  []
			);
		}
		catch(\Exception $e){
			$error = $e->getMessage().':'.$e->getLine();
			$payments_log->reason = $error;
			$payments_log->txn_status = 'FAIL';
			$payments_log->save();
			try{
				self::cancel_subscription($sub_id, $stripe);
			}
			catch(\Exception $e){
				$warning = __('Failed to cancel your previous membership plan. Please cancel it from payment gateway.').' '.$e->getMessage().':'.$e->getLine();
			}
			return redirect_error(__('Failed to retrieve stripe invoice. Please contact site administrators.').' '.$error, $warning);
		}
		
		$user_current_plan = Auth::user()->membership;
		$remaining_days = $user_current_plan->getDaysLeft();
		
		$payments_log->billing_amount = MembershipPlans::get_billing_price($plan, null, $payments_log->billing_interval);
		$payments_log->paid_amount = $invoice->amount_paid;
		$payments_log->paid_currency = strtoupper($invoice->currency);
		$payments_log->payment_id = $invoice->customer_email;
		$payments_log->ref_id = $sub_id;
		$payments_log->txn_id = $invoice_id;
		$payments_log->raw_data = json_encode($invoice);
		
		$subscription_cancellation_needed = UserMemberships::subscription_cancellation_needed($payments_log, $user_current_plan);
		
		if($subscription_cancellation_needed){
			try{
				UserMemberships::cancel_subscription($user_current_plan->payment_ref, 'Stripe');
			}
			catch(\Exception $e){
				$warning = __('Failed to cancel your previous membership plan. Please cancel it from payment gateway.').' '.$e->getMessage().':'.$e->getLine();
			}
		}
		
		try{
			$this->validate_invoice($invoice, $to_pay*100, $to_pay_currency, $subscription_cancellation_needed);
		}
		catch(\Exception $e){
			$error = $e->getMessage().':'.$e->getLine();
			$payments_log->reason = $error;
			$payments_log->txn_status = 'FAIL';
			$payments_log->save();
			try{
				self::cancel_subscription($sub_id, $stripe);
			}
			catch(\Exception $e){
				$warning = __('Failed to cancel your previous membership plan. Please cancel it from payment gateway.').' '.$e->getMessage().':'.$e->getLine();
			}
			return redirect_error(__('Failed to validate stripe invoice. Please contact site administrators.').' '.$error, $warning);
		}
		
		$payments_log->payment_period_start = Carbon::createFromTimestamp($invoice->lines->data[0]->period->start)->toDateTimeString();
		$payments_log->payment_period_end = Carbon::createFromTimestamp($invoice->lines->data[0]->period->end)->toDateTimeString();
		$invoice_duration_days = ($invoice->lines->data[0]->period->end - $invoice->lines->data[0]->period->start) / (3600*24);
		
		if(!$payments_log->txn_comments){
			$data = json_decode($payments_log->toJson(), true);
			$data['next_amount'] = $payments_log->billing_amount;
			try{
				MembershipPlans::upgrade_membership($plan_id, $user_id, $invoice_duration_days, $payments_log->has_trial ? $plan->trial_duration : null, 
				$subscription_cancellation_needed ? true : null,  $data);
			}
			catch(\Exception $e){
				$error = $e->getMessage().':'.$e->getLine();
				$payments_log->reason = $error;
				$payments_log->txn_comments = 'FAILED_MEMBERSHIP_PROCESS';
				$payments_log->txn_status = 'FAIL';
				$payments_log->save();
				try{
					self::cancel_subscription($sub_id, $stripe);
				}
				catch(\Exception $e){
					$warning = __('Failed to cancel your previous membership plan. Please cancel it from payment gateway.').' '.$e->getMessage().':'.$e->getLine();
				}
				return redirect_error(__('Failed to validate stripe invoice. Please contact site administrators.').' '.$error, $warning);
			}
		}
		
		if($invoice->discount){
			$coupon_id = $invoice->discount->coupon->id;
			try{
				$stripe->coupons->delete(
				  $coupon_id,
				  []
				);
			}catch(\Exception $e){}
		}
		
		$payments_log->txn_comments = 'MEMBERSHIP_PROCESSED';
		$payments_log->txn_status = 'SUCCESS';
		$payments_log->save();
		
		$title = __('Payment Successful');
		return view('themes.'.$this->theme.'.payments.thankyou', array('page_title' => $title, 'warning' => $warning, 'mini_page' => true));
	}
	
	protected function validate_invoice($invoice, $to_pay, $to_pay_currency, $downgrade)
	{
		if($invoice->status != 'paid'){
			throw new \ErrorException(__('Invoice is not paid.'));
		}
		
		if(!$downgrade && $invoice->amount_paid != $to_pay){
			throw new \ErrorException(__('Invoice amount does not match with billed amount.'));
		}
		
		if(strtoupper($invoice->currency) != $to_pay_currency){
			throw new \ErrorException(__('Invoice currency does not match with billed currency.'));
		}
		
		if($invoice->amount_due != $invoice->amount_paid){
			throw new \ErrorException(__('Invoice not paid fully.'));
		}
		
		return true;
	}
	
	protected function get_webhook_pass()
	{
		$this->delete_hooks = array();
		$stripe_products = array();
		if(file_exists(__DIR__.'/products.json')){
			$content = file_get_contents(__DIR__.'/products.json');
			$stripe_products = json_decode($content, true);
		}
		
		//$hook_url = 'https://github.com/stripe/stripe-cli';//LaravelLocalization::localizeUrl(route('stripe_ipn_request'));
		
		$hook_url = 'https://597afe4a4909.ngrok.io/projects/skeleton/public/payment/ipn/stripe';
		$pass = null;
		
		if(!empty($stripe_products['webhooks'])){
			foreach($stripe_products['webhooks'] as $wh){
				if($wh['url'] == $hook_url)$pass = $wh['pass'];
				else $this->delete_hooks[] = $wh['id'];
			}
		}
		
		if($pass)return $pass;
		return false;
	}	
	
	public static function cancel_subscription($sub_id, $stripe = null)
	{
		if(!$stripe)
			$stripe = new StripeClient(
			  config('payment-gateways.Stripe.api_secret')
			);
			
		$stripe->subscriptions->cancel(
		  $sub_id,
		  []
		);
		
		return true;
	}
	
	protected function display_error($error, $warning = null)
	{
		$title = __('Payment Error');
		return view('themes.'.$this->theme.'.payments.error', array('error' => $error, 'page_title' => $title, 'warning' => $warning, 'mini_page' => true) );
	}
}