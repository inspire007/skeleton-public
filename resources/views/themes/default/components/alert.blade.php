<div class="ui {{ $type }} message">
  {{ $icon ?? '' }}
  <div class="content">
	<div class="header">{{ $title }}</div>
	<p>{{ $slot }}</p>
  </div>
</div>