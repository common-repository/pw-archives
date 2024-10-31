<?php

class PW_Archives_Model extends PW_MultiModel
{
	protected $_title = "PW_Archives";
	protected $_singular_title = "PW_Archive";
	protected $_name = 'pw_archives';

	protected $_help = '
		<p>PW_Archives works just like WordPress menus. If you’re familiar with using menus, PW_Archives will make perfect sense. The idea is that you can create multiple instances of a particular set of options, each with its own name for easy reference later. In other words, you can create two completely different configurations and easily switch between the two without losing all your previous settings.</p>
		<p>For example, let’s say you want to list archives in your sidebar and only show year and month (not individual posts) and only for the most recent year.  However, you also want your archives listed on a dedicated archives page showing all years, all months, and all posts. Here’s how you’d do it:</p>
		<ol>
		<li>Create two PW_Archives instances, name one "Sidebar" and the other something like "Full".</li>
		<li>For the "Sidebar" instance, select "1″ for "Years to Display:" and "Years &gt; Months" for "Archive Depth:". For the "Full" instance, just use the defaults.</li>
		<li>Next, add the PW_Archives widget to your sidebar and select "Sidebar" as the "Instance Name" in the widget form.</li>
		<li>Finally, create a new page, call it "Archives" and add the shortcode [PW_Archives name="Full"] in the content.</li>
		</ol>
		<p><a href="http://philipwalton.com/2011/02/08/pw_archives/">PW_Archives Documentation</a></p>
	';

	protected $_post_types;
	
	
	// I'm overriding this function to handle upgrading from pervious versions of PW_Archives
	public function get_option()
	{
		// If the option is already set in this model, return that
		if ( $this->_option ) {
			return $this->_option;
		} else {
			
			// If the option exists in the database, merge it with the defaults and return, otherwise add it
			if ( $this->_option = get_option($this->_name) ) {
				return $this->_option = $this->merge_with_defaults( $this->_option );
			} else {
				add_option( $this->_name, $this->_option = $this->defaults(), '', $this->_autoload );
			}			
			
			// Still here? That means you need to create a new option with the default values
			// You also need to delete the old PW_Archives_options option, if it exists,
			// and do your best to upgrade the old option to the new format
			if ( $old_option = get_option('PW_Archives_options') ) {
				
				// This option stores whether or not to issue the upgrade admin notice
				update_option('pw_archives_upgrade', 1);
				
				// Upgrade from the previous option and widget settings
				$this->upgrade();
				
			}
			return $this->_option;
		}
	}
	

	public function data()
	{
		global $PW_Archives;
		
		// create an array of post types
		$post_types = get_post_types( array('public'=>true), 'objects');
		foreach($post_types as $name=>$object) {
			$this->_post_types[$name] = $object->labels->name;
		}
		
		return array(	
			'name' => array(
				'label' => 'Instance Name:',
				'default' => '',
				'desc' => 'Used to identify these settings in widgets, shortcode, or function calls'
			),
			'post_types' => array(
				'label' => 'Include Types:',
				'default' => array('post'), // values: int, or all
				'options' => $this->_post_types,
			),
			'depth' => array(
				'label' => 'Archive Depth:',
				'default' => 'POST',
				'options' => array(
					'YEAR' => 'Years only',
					'MONTH' => 'Years &raquo; Months',
					'POST' => 'Years &raquo; Months &raquo; Posts',
				),
			),
			'order' => array(
				'label' => 'Display Order:',
				'default' => 'DESC',
				'options' => array('DESC' => 'Descending', 'ASC' => 'Ascending'),
			),
			'year_count' => array(
				'label' => 'Years to display:',
				'desc' => '(e.g. selecting \'3\' would display only the 3 most recent years)',
				'default' => 'ALL', // values: int, or all
				'options' => array('ALL'=>'All', '1'=>'1', '2'=>'2', '3'=>'3', '4'=>'4', '5'=>'5'),
			),
			'layout' => array(
				'label' => 'Initial Layout:',
				'default' => 'SHOW',
				'desc' => '(Hidden submenus can be expanded if Javascript is enabled. See below.)',
				
				'options' => array(
					'SHOW' => 'Show submenus (months/posts) in all listed years',
					'RECENT' => 'Show submenus only in the most recent year',
					'HIDE' => 'Hide all submenus',
				),
			),
			
			'js' => array(
				'label' => 'Where:',
				'default' => array(),
				'options' => array(
					'YEAR' => 'Enable javascript on <strong>year</strong> items to show/hide their month submenus',
					'MONTH' => 'Enable javascript on <strong>month</strong> items to show/hide their post submenus (only if depth includes posts)'
				),
			),
			'js_event' => array(
				'label' => 'Event Type:',
				'default' => 'CLICK',
				'options' => array(
					'CLICK' => 'Click (mousedown)',
					'HOVER' => 'Hover (mouseover)',
				),
			),
			'js_effect' => array(
				'label' => 'Animation Type:',
				'default' => 'NONE',
				'options' => array(
					'NONE' => 'None',
					'FADE' => 'Fade In/Out',
					'SLIDE' => 'Slide Up/Down',
				),
			),
			'css' => array(
				'label' => 'Style:',
				'default' => 'NO',
				'desc'=> 'Include CSS to add ( <img src="' . (WP_PLUGIN_URL . '/' . $PW_Archives->plugin_dir . '/images/right-arrow.png') . '" /> / <img src="' . (WP_PLUGIN_URL . '/' . $PW_Archives->plugin_dir . '/images/down-arrow.png') . '" /> ) icons before javascript-enabled month/year items?',
			),
			
			'year_format' => array(
				'label' => 'Date:',
				'desc'=>'See <a href="http://codex.wordpress.org/Formatting_Date_and_Time">Formatting Date and Time</a> for details',
				'default' => 'Y',
			),
			'year_template' => array(
				'label' => 'Tempalte:',
				'desc' => 'Variables: <code>%YEAR%</code>, <code>%YEAR_URL%</code>, and <code>%POST_COUNT%</code>',
				'default' => '<a href="%YEAR_URL%">%YEAR%</a> (%POST_COUNT%)',
			),
			'month_format' => array(
				'label' => 'Date:',
				'desc'=>'See <a href="http://codex.wordpress.org/Formatting_Date_and_Time">Formatting Date and Time</a> for details',
				'default' => 'F',
			),
			'month_template' => array(
				'label' => 'Template:',
				'desc' => 'Variables: <code>%MONTH%</code>, <code>%MONTH_URL%</code>, and <code>%POST_COUNT%</code>',
				'default' => '<a href="%MONTH_URL%">%MONTH%</a> (%POST_COUNT%)',
			),
			'post_date_format' => array(
				'label' => 'Date:',
				'desc'=>'See <a href="http://codex.wordpress.org/Formatting_Date_and_Time">Formatting Date and Time</a> for details',
				'default' => 'jS',
			),
			'post_template' => array(
				'label'=>'Template:',
				'desc'=>'Variables: <code>%POST_TITLE%</code>, <code>%POST_DATE%</code>, <code>%POST_URL%</code>, <code>%POST_TYPE%</code>, <code>%COMMENT_COUNT%</code>',
				'default' => '%POST_DATE%: <a href="%POST_URL%">%POST_TITLE%</a> (%COMMENT_COUNT%)',
			),			
		);
	}
	
	protected function rules() {
		return array(
			array(
				'properties' => 'name, year_format, year_template, month_format, month_template, post_date_format, post_template',
			 	'validator'=> array('PW_Validator', 'required')
			),
			array(
				'properties' => 'name',
				'validator' => array('PW_Validator', 'match'),
				'pattern' => '/^[-\w]+$/',
				'message' => 'The instance name can only contain letters, numbers, dashes, and underscores.',				
			),
		);
	}


	private function upgrade()
	{		
		$option = get_option('PW_Archives_options');
		$option = $this->upgrade_single_instance($option, 'old-defaults');
		
		// save a new instance based on the old option settings
		if ( !$this->save($option) ) {			
			PW_Alerts::add('error', '<p><strong>Error: </strong>Sorry, but there was an error updating your PW_Archives widgets to the new version. Please check to make sure everything is displaying correctly.</p>' );				
		}	
		
		$widgets = get_option('widget_pw_archives');
		foreach($widgets as $key=>$widget)
		{
			if ( count($widget) > 2 ) {

				$widget = $this->upgrade_single_instance($widget, 'old-widget-' . $key);

				// save a new instance based on the widget settings
				if ( $this->save($widget) ) {			
					$widgets[$key] = array();
					$widgets[$key]['title'] = $widget['title'];
					$widgets[$key]['name'] = $widget['name'];
				} else {
					PW_Alerts::add('error', '<p><strong>Error: </strong>Sorry, but there was an error updating your PW_Archives widgets to the new version. Please check to make sure everything is displaying correctly.</p>' );				
				}
				
			}
		}
		update_option('widget_pw_archives', $widgets);
	}
	
	private function upgrade_single_instance($instance, $instance_name)
	{
		$instance['name'] = $instance_name;

		// set the 'depth' property based on 'show_posts' and 'month_expand'
		if ( $instance['show_posts'] == 'YES' ) {
			$instance['depth'] = 'POST';
		} else if ( $instance['month_expand'] == 0 ) {
			$instance['depth'] = 'YEAR';
		} else {
			$instance['depth'] = 'MONTH';
		}

		// set the 'layout' property based on 'month_expand'
		if ( $instance['month_expand'] == 'ALL' ) {
			$instance['layout'] = 'SHOW';
		} else if ( $instance['month_expand'] == 0 ) {
			$instance['layout'] = 'HIDE';
		} else {
			$instance['layout'] = 'RECENT';
		}

		unset($instance['show_posts']);
		unset($instance['month_expand']);
		unset($instance['faster_permalinks']);
		
		return $instance;
	}
	
}