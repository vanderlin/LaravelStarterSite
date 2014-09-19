<div class="collapse navbar-collapse navbar-ex1-collapse">
    <ul class="nav navbar-nav side-nav">

        @if (Auth::user()->hasRole("Admin"))
	        @foreach (Config::get('slate::admin.side-bar') as $meun_link)
	        	<?php $meun_link = (object)$meun_link; ?>
	        	
	        	<li {{ ($link->title==$meun_link->title) ?'class="active"':'' }}><a href="{{ URL::to($meun_link->url) }}">{{ $meun_link->title }}</a></li>    
	        @endforeach
        @endif
    
    </ul>
</div>