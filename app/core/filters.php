<?php 

// ------------------------------------------------------------------------
Route::filter('siteprotection', function() {
	if(Config::get('slate::use_site_login') && Session::has('siteprotection') == false) {
		return View::make('slate::site.site-login');
	}
});



View::composer('slate::admin.index', function($view) {
	$menu = Config::get('slate::admin.side-bar');
	$path = Request::path();
	$link = array_search($path, array_column($menu, 'url'));
	$link = $link!=false? (object)$menu[$link]:$menu[0];
	
    $view->with('link', (object)$link);
});
