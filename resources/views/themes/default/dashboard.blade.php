@extends("themes.$theme.layouts.app")

@section('content')


<div class="ui attached menu dash_menu_sidebar_icon">
	<a class="item collapse-main-sidebar">
		<i class="sidebar icon"></i>
		{{__('Menu')}}
	</a>
</div>

<div class="ui attached segment pushable main-sidebar-segment">
  <div class="ui visible left inline vertical sidebar menu main-sidebar">
    <a class="item collapse-main-sidebar">
      <i class="exchange large icon"></i>
	  <span>Collapse</span>
    </a>
	<a class="item">
      <i class="home large icon"></i>
      <span>{{__('Dashboard')}}</span>
    </a>
    <a class="item">
      <i class="calendar alternate outline large icon"></i>
      <span>{{__('Schedules')}}</span>
    </a>
    <a class="item">
      <i class="address book outline large icon"></i>
      <span>{{__('Accounts')}}</span>
    </a>
    <a class="item">
      <i class="file outline large icon"></i>
      <span>{{__('Files')}}</span>
    </a>
	<a class="item">
      <i class="folder outline large icon"></i>
      <span>{{__('Folders')}}</span>
    </a>
	<a class="item">
      <i class="cog large icon"></i>
      <span>{{__('Settings')}}</span>
    </a>
  </div>
  <div class="pusher">
    <div class="ui basic segment">
      <h3 class="ui header">Application Content</h3>
      <p>Hello</p>
      <p></p>
      <p></p>
      <p></p>
    </div>
  </div>
</div>


@endsection