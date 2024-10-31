jQuery(document).ready(function($) {
	
	var objects, filter, name, data;

	// gather the data passed from wp_localize_script()
	for (var key in PW_Archives_JS)
	{		
		instance = PW_Archives_JS[key];
		name = instance['name'];

		objects = $('.PW_Archives.' + name);
		objects.data('js_effect', instance['js_effect']);
		
		// build the set of DOM elements to apply the events to		
		if (instance['js'].length == 2) {
			filter = 'li.expandable';
		} else if (instance['js'][0] == 'MONTH') {
			filter = 'li.month.expandable';
		} else {
			filter = 'li.year.expandable';
		}
		
		if ( instance['js_event'] == 'CLICK' ) {
			objects.find(filter).click( function(e) {
				e.stopPropagation();
				if ($(this).hasClass('hide')) {
					show($(this), instance['js_effect']);
				} else {
					hide($(this), instance['js_effect']);
				}
			});
		}
		
		if ( instance['js_event'] == 'HOVER' ) {
			objects.find(filter).hover(
				function(e) {
					show($(this), instance['js_effect']);
				},
				function(e) {					
					hide($(this), instance['js_effect']);
				}
			);
		}
		
		// show the submenu based on which animation type is specified
		function show(element)
		{
			var effect = element.closest('.PW_Archives').data('js_effect');
			switch ( effect ) {
				case 'SLIDE' :					
					element.removeClass('hide').children("ul").eq(0).slideDown(333);
					break;
				case 'FADE' :
					element.removeClass('hide').children("ul").eq(0).fadeIn(333);
					break;
				default :
					element.removeClass('hide').children("ul").eq(0).show();
			}
		}
		
		// hide the submenu based on which animation type is specified
		function hide(element)
		{ 
			var effect = element.closest('.PW_Archives').data('js_effect');
			switch ( effect ) {
				case 'SLIDE' :				
					element.children("ul").eq(0).slideUp(333, function() {
						element.addClass('hide');
					});
					break;
				case 'FADE' :
					element.children("ul").eq(0).fadeOut(333, function() {
						element.addClass('hide');
					});
					break;
				default :
					element.addClass('hide').children("ul").eq(0).hide();
			}
		}
	}

});

