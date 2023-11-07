@extends("themes.$theme.layouts.app")

@section('content')
	<x-centered-one-column-mini-page>
		<h1 class="ui header login_header">{{__('Register an account')}}</h1>
		@if($errors->any())
			@foreach ($errors->all() as $error)
			   <x-alert type="negative">
					<x-slot name="title">{{__('Registration failed')}}</x-slot>
					{{ $error }}
				</x-alert>
				@break
			@endforeach
		@endif
			<form class="ui form" method="POST" action="{{ LaravelLocalization::localizeUrl(route('register')) }}">
				@csrf

				<div class="field">
					<label for="name">{{ __('Username') }}</label>
					<div class="ui left icon input">
						<i class="icon user"></i>
						<input id="name" type="text" name="name" value="{{ old('name') }}" required autocomplete="name" autofocus placeholder="{{ __('Choose a username of max 20 chars') }}">
					</div>
				</div>

				<div class="field">
					<label for="email">{{ __('E-Mail Address') }}</label>

					<div class="ui left icon input">
						<i class="icon envelope"></i>
						<input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="{{ __('Email address') }}">
					</div>
				</div>

				<div class="field">
					<label for="password">{{ __('Password') }}</label>

					<div class="ui left icon input">
						<i class="icon lock"></i>
						<input id="password" type="password" name="password" required autocomplete="new-password" placeholder="{{ __('Choose a password of min 8 chars') }}">
					</div>
				</div>

				<div class="field">
					<label for="password-confirm">{{ __('Confirm Password') }}</label>
					<div class="ui left icon input">
						<i class="icon lock"></i>
						<input id="password-confirm" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="{{ __('Confirm your password') }}">
					</div>
				</div>
				
				<div class="field">
				<div class="ui toggle checkbox">
					<input type="checkbox" name="tos_accept" id="tos_accept" {{ old('tos_accept') ? 'checked' : '' }}>

					<label for="tos_accept">
						{{ __('I accept the terms of usage') }}
					</label>
				</div>
			</div>

			<button type="submit" class="ui fluid secondary button">{{ __('Register') }}</button>
			<p></p>
			
			<a class="ui button black dimgray fluid" href="{{ LaravelLocalization::localizeUrl(route('login')) }}">
				{{ __('Already a user? Login here') }}
			</a>
			
			</form>
			<p></p>
			@if(!empty(config('site.fb_login_enabled'))) 
			<button class="ui fluid facebook button" id="fbSignInBtn">
			  <i class="facebook icon"></i>
			  {{__('Login with Facebook')}}
			</button>
			<div id="fb-root"></div>
			<script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v7.0&autoLogAppEvents=1" nonce="AoIJEtpH"></script>
			@endif
			@if(!empty(config('site.google_login_enabled'))) 
			<p></p>
			<button class="ui fluid google plus button" id="gooSignInBtn">
			  <i class="google icon"></i>
			  {{__('Login with Google')}}
			</button>
			<script src="https://apis.google.com/js/platform.js?onload=gooLoginonLoad" async defer></script>
			@endif
					
		<x-slot name="back"><x-go-back-home/></x-slot>
	</x-centered-one-column-mini-page>
@endsection
