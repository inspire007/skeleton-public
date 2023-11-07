@extends("themes.$theme.layouts.app")

@section('content')
	<x-centered-one-column-mini-page>
		<h1 class="ui header">{{__('Please confirm your email address')}}</h1>
		@if($errors->any())
			@foreach ($errors->all() as $error)
			   <x-alert type="negative">
					<x-slot name="title">{{__('Action failed')}}</x-slot>
					{{ $error }}
				</x-alert>
				@break
			@endforeach
		@endif
		@if (session('resent'))
			<div class="alert alert-success" role="alert">
				<div class="ui success message">
				  <div class="header">{{__('Action successful')}}</div>
				  <p>{{ __('A fresh verification link has been sent to your email address.') }}</p>
				</div>	
			</div>
		@endif
		
		<div class="ui floating  message">
		  <div class="header">
		  {{__('Email verification required')}}
		  </div>
		  <p>{{ __('Before proceeding, please check your email for a verification link. If you did not receive the email ... ') }}</p>
		</div>
		 <form class="ui form" method="POST" action="{{ LaravelLocalization::localizeUrl(route('verification.resend')) }}">
			@csrf
			<button type="submit" class="ui secondary button fluid">{{ __('Click here to request another') }}</button>
		</form>
	</x-centered-one-column-mini-page>
@endsection
