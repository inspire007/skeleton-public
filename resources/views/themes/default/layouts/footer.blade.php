@section('footer')
<div class="ui inverted inverted_51 vertical footer segment">
	<div class="ui container">
	  <div class="ui stackable inverted divided equal height stackable grid">
		<div class="three wide column">
		  <h4 class="ui inverted header">{{__('About')}}</h4>
		  <div class="ui inverted link list">
			<a class="item" href="{{LaravelLocalization::localizeUrl(route('home'))}}">{{__('Sitemap')}}</a>
			<a class="item" href="{{LaravelLocalization::localizeUrl(route('home'))}}">{{__('Contact Us')}}</a>
			<a class="item" href="#root">Religious Ceremonies</a>
			<a class="item" href="#root">Gazebo Plans</a>
		  </div>
		</div>
		<div class="three wide column">
		  <h4 class="ui inverted header">{{__('Services')}}</h4>
		  <div class="ui inverted link list">
			<a class="item" href="#root">Banana Pre-Order</a>
			<a class="item" href="#root">DNA FAQ</a>
			<a class="item" href="#root">How To Access</a>
			<a class="item" href="#root">Favorite X-Men</a>
		  </div>
		</div>
		<div class="three wide column">
		  <h4 class="ui inverted header">Services</h4>
		  <div class="ui inverted link list">
			<a class="item" href="#root">Banana Pre-Order</a>
			<a class="item" href="#root">DNA FAQ</a>
			<a class="item" href="#root">How To Access</a>
			<a class="item" href="#root">Favorite X-Men</a>
		  </div>
		</div>
		<div class="four wide column">
		  <h4 class="ui inverted header">Footer Header</h4>
		  <p>
			Extra space for a call to action inside the footer that could
			help re-engage users.
		  </p>
			<div class="ui floating labeled icon dropdown button lang_selector">
			  <i class="globe icon"></i>
			  <span class="text">{{__('Language')}}</span>
			  <div class="menu">
				 @foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
					@if($localeCode == 'en')
						@php $flag = 'uk';@endphp
					@else
						@php $flag = $localeCode;@endphp
					@endif
					
					<div data-href="{{ LaravelLocalization::getLocalizedURL($localeCode, 'setlocale', [], true) }}" class="item" hreflang="{{$localeCode}}">
					  <i class="flag {{$flag}}"></i>
					  {{ $properties['native'] }}
					</div>
				@endforeach
			  </div>
			</div>
	  </div>
	</div>
</div>
@endsection