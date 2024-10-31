<?php

class PW_Archives_Widget extends WP_Widget {
	/**
	 * Widget setup.
	 */
	function PW_Archives_Widget()	{
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'PW_Archives', 'description' => 'An easy to use, light-weight, fully-customizable archiving plugin that allows you to display your posts almost any way you like.' );

		/* Create the widget. */
		$this->WP_Widget( 'pw_archives', __('PW_Archives'), $widget_ops );
	}

	/**
	 * How to display the widget on the screen.
	 */
	function widget( $args, $instance )	{
		extract( $args );
		$title = apply_filters('widget_title', $instance['title'] );
		
		/* Before widget (defined by themes). */
		echo $before_widget;

		/* Display the widget title if one was input (before and after defined by themes). */
		if ( $title ) {
			echo $before_title . $title . $after_title;
		}

		global $PW_Archives;
		$PW_Archives->display($instance['name']);

		/* After widget (defined by themes). */
		echo $after_widget;
	}

	/**
	 * Update the widget settings.
	 */
	function update( $new_instance, $old_instance )	{
		$new_instance['title'] = strip_tags($new_instance['title']);
		$new_instance['name'] = strip_tags($new_instance['name']);
		
		return $new_instance;
	}

	/**
	 * Displays the widget settings controls on the widget panel.
	 * Make use of the get_field_id() and get_field_name() function
	 * when creating your form elements. This handles the confusing stuff.
	 */
	function form( $instance )
	{
		global $PW_Archives;
		$names = array();
		foreach( $PW_Archives->model->get_option() as $key=>$model_instance )
		{
			// if a model instance has been saved with a name, add it to the names array
			if ( (int) $key > 0 && isset($model_instance['name'] ) )
				$names[$model_instance['name']] = $model_instance['name'];
		}
		
		$title = isset( $instance['title'] ) ? $instance['title'] : '';
		$name = isset( $instance['name'] ) ? $instance['name'] : '';

		// If no menus exists, direct the user to go and create some.
		if ( !$names ) {
			echo '<p>'. sprintf( __('No PW_Archives have been created yet. <a href="%s">Create some</a>.'), admin_url('nav-menus.php') ) .'</p>';
			return;
		}
		
		printf(
			'<p>%s%s</p><p>%s %s</p>',
			PW_HTML::label("Title", $this->get_field_id('title') ),
			PW_HTML::textfield($this->get_field_name('title'), $title, array('class'=>'widefat') ),
			PW_HTML::label("Instance Name", $this->get_field_id('nme') ),
			PW_HTML::select($this->get_field_name('name'), $names, $name)
		);
		
	}
}


?>