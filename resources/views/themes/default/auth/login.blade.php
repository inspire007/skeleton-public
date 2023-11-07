@extends("themes.$theme.layouts.app")

@section('content')
	<x-centered-one-column-mini-page>
		<h1 class="ui header login_header">{{__('Login to account')}}</h1>
		@if (session('message'))
			<x-alert type="negative">
				<x-slot name="title">{{__('Login failed')}}</x-slot>
				{{ session('message') }}
			</x-alert>
		@elseif($errors->any())
			@foreach ($errors->all() as $error)
			   <x-alert type="negative">
					<x-slot name="title">{{__('Login failed')}}</x-slot>
					{{ $error }}
				</x-alert>
				@break
			@endforeach
		@endif
		<form class="ui form" method="POST" action="{{ LaravelLocalization::localizeUrl(route('login')) }}">
			@csrf
			<div class="field">
				<label for="email">{{ __('E-Mail Address') }}</label>
				<div class="ui left icon input">
					<i class="envelope icon"></i>
					<input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="{{ __('Email address') }}">
				</div>
				
			</div>

			<div class="field">
				<label for="password">{{ __('Password') }}</label>
				<div class="ui left icon input">
					<i class="lock icon"></i>
					<input id="password" type="password" name="password" required autocomplete="current-password"  placeholder="{{ __('Password') }}">
				</div>
			</div>

			<div class="field">
				<div class="ui toggle checkbox">
					<input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>

					<label for="remember">
						{{ __('Remember Me') }}
					</label>
				</div>
			</div>
			
			@php $and = Route::has('password.request') && Route::has('register');@endphp
			
			<button class="ui button {{ $and ? 'secondary fluid' : ''}}">{{ __('Login') }}</button>
			<p></p>
			@if($and)
			<div class="ui fluid buttons">
				@if (Route::has('password.request'))
				<a class="ui dimgray black button" href="{{ LaravelLocalization::localizeUrl(route('password.request')) }}">
					{{ __('Forgot Password?') }}
				</a>
				@endif
				@if($and)
					<div class="or"></div>
				@endif
				@if (Route::has('register'))
				<a class="ui teal button" href="{{ LaravelLocalization::localizeUrl(route('register')) }}">
					{{ __('Create an account') }}
				</a>
				@endif
			</div>
			@else
				@if (Route::has('password.request'))
				<a href="{{ LaravelLocalization::localizeUrl(route('password.request')) }}">
					{{ __('Forgot your password?') }}
				</a>
				@endif
				@if (Route::has('register'))
				<a href="{{ LaravelLocalization::localizeUrl(route('register')) }}">
					{{ __('Create a new account') }}
				</a>
				@endif
			@endif
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
