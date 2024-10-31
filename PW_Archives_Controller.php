<?php

class PW_Archives_Controller extends PW_MultiModelController
{	
	protected $js_options = array();
	protected $instance;
	
	public function __construct()
	{		
		// these two properties need to be set before parent::__construct() is called
		$this->_plugin_dir = plugin_basename( dirname(__FILE__) );
		$this->_plugin_file = plugin_basename( dirname(__FILE__) . '/PW_Archives.php' );

		parent::__construct();
		
		// If get variable 'pw_hide_upgrade_notice' is set, delete the upgrade notice option
		if (isset($_GET['pw_hide_upgrade_notice'])) {
			delete_option('pw_archives_upgrade');
			// redirect the page and remove _instance' and 'delete_instance' from the URL
			wp_redirect( remove_query_arg( 'pw_hide_upgrade_notice', wp_get_referer() ) );
			exit();
		}
		
		add_action( 'pre_get_posts', array($this, 'add_post_types_to_archive') );
		add_action( 'wp_footer', array($this, 'javascript_options'), 9, 0);
	}
	
	public function on_public_page()
	{
		parent::on_public_page();

		$this->_styles[] = array( 'PW_Archives_CSS', WP_PLUGIN_URL . '/' . $this->_plugin_dir . '/pw-archives.css' );
		$this->_scripts[] = array( 'PW_Archives_JS', WP_PLUGIN_URL . '/' . $this->_plugin_dir . '/pw-archives.js', array('jquery', 'json2'), false, true );
	}
	
	public function on_admin_page()
	{
		parent::on_admin_page();
		
		// see if the option 'pw_archives_uprade' exists in cache, but don't query the database
		$alloptions = wp_load_alloptions();
		if ( isset($alloptions['pw_archives_upgrade']) ) {
			add_action( 'admin_notices', array($this, 'upgrade_notice') );		
		}
	}
	
	public function upgrade_notice()
	{
		?>
			<div class="updated" style="padding-bottom:5px;">
				<p><strong>Important:</strong> You've just upgraded <a href="options-general.php?page=<?php echo $this->_model->name; ?>">PW_Archives</a> to version 2.0, which contains many great new features, but as a result some things aren't backwards compatible (specifically, how shortcodes are handled). Your old settings have been converted as best as possible, but just to be safe, please take a moment to make sure everything is displaying as expected. If you need help, don't hesitate to contact <a href="mailto:philip@philipwalton.com">philip@philipwalton.com</a></p>
				<p><a class="button" href="<?php echo add_query_arg('pw_hide_upgrade_notice', '1'); ?>">Hide Warning</a></p>
			</div>
		<?php
	}
	
	public function javascript_options()
	{
		// javascript
		if ($this->js_options) {			
			echo "<script type='text/javascript'>\n";
			echo "/* <![CDATA[ */\n";
			echo "PW_Archives_JS = " . json_encode( $this->js_options ) . "\n";
			echo "/* ]]> */\n";
			echo "</script>\n";
		} else {
			wp_dequeue_script( 'PW_Archives_JS' );
		}
	}
	
	// Adds the shortcode [PW_Archives]. Called via add_shortcode(). 
	public function shortcode( $atts = array() ) 
	{
		if ( !empty($atts['name']) ) {	
			return $this->display($atts['name'], false);
		} else {
			return $this->display(null, false);
		}
	}
	
	// Allows for query vars like ?post_type=post,page,book in archive pages
	// This is used for generating year and month links if custom post types are specified
	function add_post_types_to_archive($query)
	{
		global $wp_the_query;
		
		// make sure we're on an archive page, make sure the post_types $_GET var is passed, and make sure we're on the main query
	    if ( is_archive() && isset($_GET['post_types']) && $wp_the_query == $query ) {
			$types = explode(',', $_GET['post_types']);
			$query->set('post_type', $types);
	    }
	}
	
	public function display($name, $echo = true)
	{
		// destroy any previously assigned values to $this->instance
		$this->instance = null;
		
		// if there is no name passed, use the defaults, otherwise find the instance
		if (!$name) {
			$this->instance = $this->model->option[0];
		} else {
			foreach( $this->model->option as $key=>$model_instance )
			{
				// if a model instance has been saved with a name, add it to the names array
				if ( (int) $key > 0 && $model_instance['name'] == $name ) {
					$this->instance = $model_instance;
					break;
				}
			}

			// if there is no instance by the passed name, do nothing
			if (!$this->instance || !$name) {
				return false;
			}
		}

		// add the javascript instructions to the $js_options variable
		if ( $this->instance['js'] ) {
			// $this->js_options[$name] = json_encode( array('name'=>$name, 'js'=>$this->instance['js'], 'js_event'=>$this->instance['js_event'], 'js_effect'=>$this->instance['js_effect']) );
			$this->js_options[$name] = array('name'=>$name, 'js'=>$this->instance['js'], 'js_event'=>$this->instance['js_event'], 'js_effect'=>$this->instance['js_effect']);
			
		}
		
		$archives = $this->getArchives();
		$archives = $this->organizeArchivesInTree($archives);
		$archives = $this->trimArchivesTree($archives);

		// set $most_recent_year to the first value of the $archives->years array
		$most_recent_year = max( array_keys($archives->years) );


		// Loop through each of the years
		$years_output = "";
		foreach($archives->years as $year)
		{
			// Determine if this year should be expandable based on the depth and javascript options
			$year_expandable = in_array($this->instance['depth'], array('MONTH', 'POST')) && in_array('YEAR', $this->instance['js']) ? true : false;
			
			// Determine if this year should be hidden based on the javascript options
			$collapse =
				($this->instance['layout'] == 'HIDE')
				|| ( $this->instance['layout'] == 'RECENT' && $year->post_year != $most_recent_year )
				? true : false
			;
			
			// if there are months, display them
			$month_output = '';
			if ($year->months) {

				// open the months list, then loop through each month, within this year
				foreach($year->months as $month)
				{					
					// Determine if this month should be expandable based on the depth and javascript options
					$month_expandable = $this->instance['depth'] == 'POST' && in_array('MONTH', $this->instance['js']) ? true : false;

					// Determine if this month should be hidden based on the javascript options
					// $collapse_month = $this->instance['onload'] == 'POST' && $year->post_year == $most_recent_year ? false : true;
					
					// if there are posts, display them
					$post_output = '';
					if ($month->posts) {

						// the loop through each post
						foreach($month->posts as $post)
						{
							$post_output .= ZC::r('li.post.' . ($post->post_type != 'post' ? " {$post->post_type}" : ''), $this->listPost($post) );	
						}
						$post_output = ZC::r('ul.posts{%1}', $collapse ? array('style'=>'display:none;') : array(), $post_output);
					}
					
					
					$month_output .= ZC::r(
						'li.month' . ($collapse ? '.hide' : '') . ($month_expandable ? '.expandable' : ''),
						$this->listMonth($month->post_month, $month->post_year, $month->month_count) . $post_output
					);
					
				}
				$month_output = ZC::r('ul.months{%1}', $collapse ? array('style'=>'display:none;') : array(), $month_output);
			}
			
			$years_output .= ZC::r(
				'li.year' . ($collapse ? '.hide' : '') . ($year_expandable ? '.expandable' : ''),
				$this->listYear($year->post_year, $year->year_count) . $month_output
			);
		}		
		$years_output = ZC::r(
			'ul.PW_Archives.years.' . $this->instance['name'] . ($this->instance['css'] == 'YES' && $this->instance['js'] ? '.css' : ''),
			$years_output
		);
		
		// return or echo the output
		if ($echo) {
			echo $years_output;
		} else {
			return $years_output;
		}

	}
		
	protected function getArchives()
	{
		global $wpdb, $blog_id;
		
		// set the variables uses in the query
		$blog_id = isset($blog_id) ? $blog_id : ""; // in case the blog is not in multisite mode.
		$posts_table = $wpdb->get_blog_prefix($blog_id) . "posts";
		$post_types = "('" . implode( "','", $this->instance['post_types'] ) .  "')";
		
		// join months and years adding columns for counts
		$monthAndYearCountQuery = "SELECT y.post_year AS `post_year`, year_count, m.post_month AS `post_month`, month_count
		FROM (
			SELECT YEAR( post_date ) AS post_year, count( ID ) AS year_count
			FROM $posts_table
			WHERE post_status = 'publish'
			AND post_type IN $post_types
			GROUP BY YEAR( post_date )
			ORDER BY post_date DESC
		) AS y
		NATURAL JOIN (
			SELECT YEAR( post_date ) AS post_year, MONTH( post_date ) AS post_month, count( ID ) AS month_count
			FROM $posts_table
			WHERE post_status = 'publish'
			AND post_type IN $post_types
			GROUP BY YEAR( post_date ) , MONTH( post_date )
			ORDER BY post_date DESC
		) AS m";

		// all published posts joined with added columns in each row showing month and year counts
		$allPostsWithMonthAndYearCountQuery = "SELECT ID, post_date, post_name, post_title, post_author, comment_count, y.post_year AS `post_year`, year_count, m.post_month AS `post_month`, month_count, post_type, post_status, post_parent
		FROM (
			SELECT YEAR( post_date ) AS post_year, count( ID ) AS year_count
			FROM $posts_table
			WHERE post_status = 'publish'
			AND post_type IN $post_types
			GROUP BY YEAR( post_date )
			ORDER BY post_date DESC
		) AS y
		NATURAL JOIN (
			SELECT YEAR( post_date ) AS post_year, MONTH( post_date ) AS post_month, count( ID ) AS month_count
			FROM $posts_table
			WHERE post_status = 'publish'
			AND post_type IN $post_types
			GROUP BY YEAR( post_date ) , MONTH( post_date )
			ORDER BY post_date DESC
		) AS m
	 	NATURAL JOIN (
			SELECT ID, post_date, MONTH( post_date ) AS post_month, YEAR( post_date ) AS post_year, post_name, post_title, post_author, post_type, post_status, post_parent, comment_count
			FROM $posts_table
			WHERE post_status = 'publish'
			AND post_type IN $post_types
			ORDER BY post_date DESC
		) AS p";
		
		if ( 'POST' == $this->instance['depth'] ) {
			return $wpdb->get_results($allPostsWithMonthAndYearCountQuery);
		} else {
			return $wpdb->get_results($monthAndYearCountQuery);
		}
	}
	
	protected function organizeArchivesInTree($queryResults)
	{
		// organize the query results into an archive tree: years > months > posts
		$archives = (object) null;
		$archives->years = array();
		foreach($queryResults as $row)
		{
			// if no year object for this row exists, populate it
			if (!isset($archives->years[$row->post_year])) {
				$year = (object) null;
				$year->post_year = $row->post_year;
				$year->year_count = $row->year_count;
				$year->months = array();
				$archives->years[$row->post_year] = $year;
			}
			
			// if no month object for this row exists, populate it
			if (!isset($archives->years[$row->post_year]->months[$row->post_month])) {
				$month = (object) null;
				$month->post_month = $row->post_month;
				$month->post_year = $row->post_year;
				$month->month_count = $row->month_count;
				$month->posts = array();
				$archives->years[$row->post_year]->months[$row->post_month] = $month;
			}

			// if posts were included in the query populate the posts object
			if (isset($row->ID)) {
				$row->post_filter = 'sample';
				$archives->years[$row->post_year]->months[$row->post_month]->posts[] = $row;	
			}
		}
		return $archives;		
	}

	// Trim the tree based on what the plugin options of how many years/months/posts should be shown
	protected function trimArchivesTree($archives)
	{
		// remove any years that shouldn't be here
		if ($this->instance['year_count'] != 'ALL') {
			$count = 0;
			foreach($archives->years as $key=>$value)
			{
				if ( ++$count > (int) $this->instance['year_count'] ) {
					unset( $archives->years[$key] );
				}
			}
		}

		// If not all years should be expanded, trim the ones that won't be
		if ($this->instance['layout'] != 'SHOW' && !$this->instance['js'] )
		{			
			$count = 0;
			foreach($archives->years as $year)
			{							
				$count++;
				
				// If the count is 1 and layout is to only show recent years, continue
				if ( $this->instance['layout'] == 'RECENT' && $count <= 1 ) {				
					continue;
				}
				// remove the months from the year object
				$year->months = array();
			}	
		}
		
		// This needs to be last in the function, otherwise the ordering will be messed up
		if ($this->instance['order'] == "ASC") {
			$archives = $this->reverseArchiveOrder($archives);
		}
		
		return $archives;
	}
	
	protected function reverseArchiveOrder($archives)
	{
		$archives->years = array_reverse($archives->years, true);
		foreach($archives->years as $year) {			
			$year->months = array_reverse($year->months, true);
			foreach($year->months as $month) {
				$month->posts = array_reverse($month->posts, true);
			}
		}
		return $archives;
	}

	protected function listYear($year, $count)
	{
		$year_url = $this->instance['post_types'] == array('post') ? get_year_link($year) : add_query_arg('post_types', implode(',', $this->instance['post_types']), get_year_link($year));		
		$output = str_replace(
			array("%YEAR%", "%YEAR_URL%", "%POST_COUNT%"),
			array(date_i18n($this->instance['year_format'], mktime(0,0,0,1,1,$year)), $year_url, $count),
			$this->instance['year_template']
		);
		return $output;
	}
	
	protected function listMonth($month, $year, $count)
	{
		$month_url = $this->instance['post_types'] == array('post') ? get_month_link($year, $month) : add_query_arg('post_types', implode(',', $this->instance['post_types']), get_month_link($year, $month));		
		$output = str_replace(
			array("%MONTH%", "%MONTH_URL%", "%POST_COUNT%"),
			array(date_i18n($this->instance['month_format'], mktime(0,0,0,$month,1,$year)), $month_url, $count),
			$this->instance['month_template']
		);
		return $output;
	}
	
	protected function listPost($post)
	{
		$post_type_obj = reset( get_post_types( array('name' => $post->post_type), 'objects') );
		$post_type_name = $post_type_obj->labels->singular_name;

		$output = str_replace(
			array('%POST_TITLE%', '%POST_DATE%', '%POST_URL%', '%POST_TYPE%', '%COMMENT_COUNT%'),
			//array($title, get_permalink($this->posts[$id]), (int) $comment_count),
			array($post->post_title, date_i18n($this->instance['post_date_format'], strtotime($post->post_date)), get_permalink($post), $post_type_name, (int) $post->comment_count),
			$this->instance['post_template']
		);
		return $output;
	}
	
}