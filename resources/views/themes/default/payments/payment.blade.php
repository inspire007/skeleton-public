@extends("themes.$theme.layouts.app")

@section('no_payment_gateway_error')
	<x-alert type="icon negative">
		<x-slot name="title">{{__('No payment gateway')}}</x-slot>
		<x-slot name="icon"><i class="icon times circle"></i></x-slot>
		{{ __('Sorry, no payment gateway available now') }}
	</x-alert>
@endsection

@section('content')
	<x-centered-one-column-mini-page>
		<h1 class="ui header login_header">{{ $page_title }}</h1>
		@if (session('message'))
			<x-alert type="icon negative">
				<x-slot name="title">{{__('Payment request failed')}}</x-slot>
				<x-slot name="icon"><i class="icon times circle"></i></x-slot>
				{{ session('message') }}
			</x-alert>
		@elseif ( $input_error )
			<x-alert type="icon negative">
				<x-slot name="title">{{__('Payment request failed')}}</x-slot>
				<x-slot name="icon"><i class="icon times circle"></i></x-slot>
				{{ $input_error }}
			</x-alert>
		@elseif($errors->any())
			@foreach ($errors->all() as $error)
				<x-alert type="icon negative">
					<x-slot name="title">{{__('Payment request failed')}}</x-slot>
					<x-slot name="icon"><i class="icon times circle"></i></x-slot>
					{{ $error }}
				</x-alert>
				@break
			@endforeach
		@endif
		
		<div class="ui divider"></div>
		<div>
			<table class="ui striped table payment_summary_table">
			<div class="ui active inverted dimmer payment_summary_table_dimmer" style="display:none">
				<div class="ui loader"></div>
			  </div>
				<tr><th>{{__('Requested Membership')}}</th><td>{{ $requested_plan_details->plan_name }}</td></tr>
				<tr><th>{{__('Current Membership')}}</th><td>{{ $current_plan_details->plan_name }}</td></tr>	
				<tr><th>{{ __('Expires In') }}</th><td>{{ $current_plan_expires_in ? $current_plan_expires_in.__('day(s)') : __('N/A') }}</td></tr>
				<tr><th>{{__('Billing Price')}}</th><td>{{ config('site.currency_symbol') }}{{ $invoice->billing_price }}</td></tr>
				@if($invoice->membership_balance_used)
				<tr><th>{{__('Remaining Balance')}}<br/>[{{__('Previous Membership')}}]</th><td>{{ config('site.currency_symbol') }}{{ $invoice->membership_balance_used }}</td></tr>
				@endif
				<tr><th>{{__('Amount to Pay Now')}}</th><td>{{ config('site.currency_symbol') }}{{{ $invoice->amount_due }}}</td></tr>
				<tr><th>{{__('Billing Period')}}</th><td>{{{ ucwords($billing_period) }}}</td></tr>
				<tr><th>{{__('Trial Period')}}</th><td>{{{ $trial_available ? ( $invoice->trial_days ? sprintf(__('%d days'), $invoice->trial_days ) : __('N/A') ) : __('Already used') }}}</td></tr>
				<tr>
					<th>{{__('Choose Payment Cycle')}}</th>
					<td>
						<div class="ui floating labeled icon dropdown payment_cycle_dropdown">
						  <i class="calendar alternate outline icon"></i>
						  <span class="text">{{__('Recurring')}}</span>
						  <div class="menu">
							<div class="header">
							  <i class="tags icon"></i>
							  {{__('Choose payment cycle')}}
							</div>
							<div class="divider"></div>
							<div class="item" data-value="recurring">
							{{__('Recurring')}}
							</div>
							<div class="item" data-value="onetime">
							  {{__('One Time')}}
							</div>
						  </div>
						</div>
					</td>
				</tr>
			</table>
		</div>
		<div class="ui divider"></div>
		
		@if($invoice->invoice_action_require == 'DOWNGRADE')
		<div class="ui warning message">
			<p>{{ __('If you downgrade, your new membership will be activated after the expiration of current membership plan.') }}</p>
		</div>
		<div class="ui divider"></div>
		@endif
		
		<form class="ui form payment_method_submit_form" method="POST" action="">
			<input type="hidden" name="payment_cycle" value="recurring"/>
			<input type="hidden" name="plan_id" value="{{ $requested_plan_details->id }}"/>
			<input type="hidden" name="billing_interval" value="{{{ $billing_interval }}}"/>
			<input type="hidden" name="invoice_id" value="{{ $invoice->id }}"/>
			<input type="hidden" name="process_membership" value="1"/>
			@csrf
			@if($invoice->amount_due)
				<h4 class="ui header">{{__('Available payment options')}}</h4>
				@if(!$payment_gateways)
					@yield('no_payment_gateway_error')
				@else
					@php $disp = 0;@endphp
					@foreach($payment_gateways as $gt)
						@if(!$gt['is_enabled'])
							@continue;
						@endif
						@php
							$disp = 1;
						@endphp
					<p><input data-url="{{ LaravelLocalization::localizeUrl(route( 'membership.payment' )) }}" class="ui input" type="radio" name="payment_gateway" value="{{ $gt['name'] }}">&nbsp; &nbsp; <i class="icon huge payment_method_selector {{ $gt['icon'] }}"></i> <a class="ui blue basic label payment_method_selector">{{ $gt['tagline'] }}</a></p>
					
					@endforeach
					@if(!$disp && $amount)
						@yield('no_payment_gateway_error')
					@endif
					<p><button class="ui button fluid secondary continue_with_payment">{{__('Continue with payment >>')}}</button></p>
				@endif
			@else 
				<p><button class="ui button fluid secondary continue_with_membership">{{__('Continue with membership >>')}}</button></p>
			@endif
		</form>
		<p></p>

	<x-slot name="back"><x-go-back/></x-slot>
</x-centered-one-column-mini-page>	
@endsection