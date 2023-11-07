@extends("themes.$theme.layouts.app")

@section('content')
	<x-centered-one-column-mini-page>
		<h1 class="ui header">{{__('Reset your password')}}</h1>
		@if($errors->any())
			@foreach ($errors->all() as $error)
				<x-alert type="negative">
					<x-slot name="title">{{__('Password reset failed')}}</x-slot>
					{{ $error }}
				</x-alert>
				@break
			@endforeach
		@endif
		<form  class="ui form" method="POST" action="{{ LaravelLocalization::localizeUrl(route('password.update')) }}">
			@csrf

			<input type="hidden" name="token" value="{{ $token }}">

			<div class="field">
				<label for="email">{{ __('E-Mail Address') }}</label>

				<div class="ui left icon input">
					<i class="envelope icon"></i>
					<input id="email" type="email" name="email" value="{{ $email ?? old('email') }}" required autocomplete="email" autofocus placeholder="{{ __('Email address') }}">
				</div>
			</div>

			<div class="field">
				<label for="password">{{ __('Password') }}</label>

				<div class="ui left icon input">
					<i class="lock icon"></i>
					<input id="password" type="password" name="password" required autocomplete="new-password" placeholder="{{ __('New password') }}">
				</div>
			</div>

			<div class="field">
				<label for="password-confirm">{{ __('Confirm Password') }}</label>
				<div class="ui left icon input">
					<i class="lock icon"></i>		
					<input id="password-confirm" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="{{ __('Confirm new password') }}">
				</div>
			</div>

			 <button type="submit" class="ui button">
						{{ __('Reset Password') }}
			 </button>
			   
		</form>
		
		<x-slot name="back"><x-go-back-home/></x-slot>
	</x-centered-one-column-mini-page>	
@endsection
