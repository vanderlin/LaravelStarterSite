@extends('slate::admin.layouts.default')

{{-- Web site Title --}}
@section('title')
	{{Config::get('slate::site-name')}} | {{ucfirst($link->title)}}
@stop

{{-- Content --}}
@section('content')
	
	@if($link)
		@include($link->view)
	@endif
	{{--
	@if ($page == 'settings')
		@include('slate::admin.settings')	
	@elseif ($page == 'users')
		@include('slate::admin.users')		
	@elseif ($page == 'roles')
		@include('slate::admin.roles.index')		
	@elseif ($page == 'themes')
		@include('slate::admin.themes')	
	@elseif ($page == 'assets')
		@include('slate::admin.assets')		
	@else 
		@include('slate::admin.settings')
	@endif
	--}}
@stop