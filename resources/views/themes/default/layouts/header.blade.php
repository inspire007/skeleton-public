@section('sidebar')
<div class="ui vertical inverted sidebar menu main_menu_sidebar">
  <a href="{{LaravelLocalization::localizeUrl(route('home'))}}" class="nav_item_home item">{{__('Home')}}</a> 
  <a href="{{LaravelLocalization::localizeUrl(route('about'))}}" class="nav_item_about item">{{__('About')}}</a>
  <a href="{{LaravelLocalization::localizeUrl(route('pricing'))}}" class="nav_item_pricing item">{{__('Pricing')}}</a> 
  <a href="{{LaravelLocalization::localizeUrl(route('features'))}}" class="nav_item_features item">{{__('Features')}}</a>
  <a href="{{LaravelLocalization::localizeUrl(route('support'))}}" class="nav_item_support item">{{__('Support')}}</a>
  @guest
	  <a href="{{LaravelLocalization::localizeUrl(route('login'))}}" class="nav_item_login item">{{__('Log In')}}</a> 
	  @if(Route::has('register'))	  
	  <a href="{{LaravelLocalization::localizeUrl(route('register'))}}" class="nav_item_register item">{{__('Sign Up')}}</a>
	  @endif
  @else
	  <a href="{{LaravelLocalization::localizeUrl(route('user_dashboard'))}}" class="nav_item_dashboard item">{{__('Dashboard')}}</a>
	  <a href="{{ LaravelLocalization::localizeUrl(route('logout')) }}" class="item" onclick="event.preventDefault(); document.getElementById('frm-logout').submit();">{{__('Logout')}}</a>
  @endguest
</div>
@endsection

@section('topmenu')
<div class="ui vertical inverted segment top_menu">
	<div class="ui {{ empty($dashboard_page) ?  'container' : 'dashboard_container'}}">
		<a class="item" href="{{LaravelLocalization::localizeUrl(route('home'))}}"><img class="top_menu_logo" src="{{url("assets/$theme/images/logo.png")}}"></a>
		<div class="top_menu_icons">
			@if(!empty(config('site.facebook_url')) && empty($dashboard_page))  
				<a class="item" target="_blank" href="{{ config('site.facebook_url') }}"><i class="icon inverted big facebook top_menu_social_icon"></i></a>
			@endif
			@if(!empty(config('site.twitter_url')) && empty($dashboard_page))  
				<a class="item" target="_blank" href="{{ config('site.twitter_url') }}"><i class="icon inverted big twitter top_menu_social_icon"></i></a>
			@endif
			@if(!empty(config('site.reddit_url')) && empty($dashboard_page))  
				<a class="item" target="_blank" href="{{ config('site.reddit_url') }}"><i class="icon inverted big reddit square top_menu_social_icon"></i></a>
			@endif
			@if(!empty(config('site.instagram_url')) && empty($dashboard_page))  
				<a class="item" target="_blank" href="{{ config('site.instagram_url') }}"><i class="icon inverted big instagram top_menu_social_icon"></i></a>
			@endif
				
			<!-- lang selector -->
			<div class="ui dropdown item lang_selector">
				<a class="item"><i class="icon inverted big big_c language"></i></a>
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
			@auth
				<a class="item top_menu_auth_dropdown_spacer {{!empty($dashboard_page) ? '' : 'hidden'}}">&nbsp;&nbsp;</a>
				<div class="ui dropdown item top_menu_auth_dropdown {{!empty($dashboard_page) ? '' : 'hidden'}}">
					<img class="ui avatar image" src="{{ url("assets/$theme/images/pp-black.png") }}">
					<span class="mini_menu_username">{{Auth::user()->name}}</span>
					<!--
					<a class="item"><i class="icon inverted big user circle"></i></a>
					-->
					<div class="menu">
						<a href="{{LaravelLocalization::localizeUrl(route('user_dashboard'))}}" class="item nav_item_dashboard">{{__('Dashboard')}}</a>
						<a class="item" href="{{ LaravelLocalization::localizeUrl(route('logout')) }}" onclick="event.preventDefault(); document.getElementById('frm-logout').submit();">{{__('Logout')}}</a>
					</div>
				</div>
			@endauth

		</div>
	</div>
</div>
<form id="change-loc" action="" method="POST" style="display: none;">
	{{ csrf_field() }}
</form>
<form id="frm-logout" action="{{ LaravelLocalization::localizeUrl(route('logout')) }}" method="POST" style="display: none;">
	{{ csrf_field() }}
</form>
@endsection

@section('nav')
<div class="ui inverted inverted_51 vertical masthead center aligned segment {{Route::currentRouteName() != 'home' ? 'inverted_51_small' : ''}}">
	<div class="ui container">
	  <div class="ui large secondary inverted pointing menu">
		<a class="toc item"><i class="sidebar icon main_menu_sidebar_icon"></i></a>
		<a href="{{LaravelLocalization::localizeUrl(route('home'))}}" class="nav_item_home item">{{__('Home')}}</a> 
		<a href="{{LaravelLocalization::localizeUrl(route('about'))}}" class="nav_item_about item">{{__('About')}}</a>
		<a href="{{LaravelLocalization::localizeUrl(route('pricing'))}}" class="nav_item_pricing item">{{__('Pricing')}}</a> 
		<a href="{{LaravelLocalization::localizeUrl(route('features'))}}" class="nav_item_features item">{{__('Features')}}</a>
		<a href="{{LaravelLocalization::localizeUrl(route('support'))}}" class="nav_item_support item">{{__('Support')}}</a>
		<div class="right item">
		  @guest
			<a href="{{LaravelLocalization::localizeUrl(route('login'))}}" class="nav_item_login ui inverted button">{{__('Log In')}}</a>
			@if(Route::has('register'))
			<a href="{{LaravelLocalization::localizeUrl(route('register'))}}" class="nav_item_register ui inverted button">{{__('Sign Up')}}</a>
			@endif
		  @else
			<div class="ui dropdown item">
			  {{ __('Welcome').' '.Auth::user()->name }} <i class="dropdown icon"></i>
			  <div class="menu">
				<a href="{{LaravelLocalization::localizeUrl(route('user_dashboard'))}}" class="item nav_item_dashboard">{{__('Dashboard')}}</a>
				<a class="item" href="{{ LaravelLocalization::localizeUrl(route('logout')) }}" onclick="event.preventDefault(); document.getElementById('frm-logout').submit();">{{__('Logout')}}</a>  
			  </div>
			</div>
		  @endguest
		</div>
	  </div>
	</div>
	@if( Route::currentRouteName() == 'home' )
	<div class="ui text container">
	  <h1 class="ui inverted header">Imagine-a-Company</h1>
	  <h2>Do whatever you want when you want to.</h2>
	  <div class="ui huge primary button">
		Get Started <i class="right arrow icon"></i>
	  </div>
	</div>
	@endif
</div>
@endsection