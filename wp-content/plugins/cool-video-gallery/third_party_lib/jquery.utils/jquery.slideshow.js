function cvg_slide_switch(ele) {
	
	var $active = jQuery('#' + ele + ' SPAN.active');

	if ($active.length == 0)
		$active = jQuery('#' + ele + ' SPAN:last');

	// use this to pull the divs in the order they appear in the markup
	var $next = $active.next().length ? $active.next()
			: jQuery('#' + ele +' SPAN:first');

	$active.addClass('last-active');

	$next.css({
	}).addClass('active').animate({
	}, 1000, function() {
		$active.removeClass('active last-active');
	});
}