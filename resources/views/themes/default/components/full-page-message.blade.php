<div class="ui container stripe vertical">
	<div class="ui center aligned very padded stacked {{ $type == 'error' ? 'red' : ( $type == 'warning' ? 'orange' : 'green') }} segment">
	  <div class="ui icon header">
		<i class="{{ $type == 'error' ? 'times' : ( $type == 'warning' ? 'question' : 'check' ) }} circle outline icon"></i>
		<p></p>
		{{ $slot }}
	  </div>
	  <div class="inline">
		<a class="ui primary button" href="{{ url()->previous() }}">{{ __('Go Back') }}</a>
		@guest
		<a class="ui secondary button" href="{{ LaravelLocalization::localizeUrl(route('home')) }}">{{ __('Go to Homepage') }}</a>
		@endguest
		@auth
		<a class="ui secondary button" href="{{ LaravelLocalization::localizeUrl(route('user_dashboard')) }}">{{ __('Go to Dashboard') }}</a>
		@endauth
	  </div>
	</div>
</div>