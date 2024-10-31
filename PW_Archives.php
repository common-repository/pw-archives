<?php
/*
Plugin Name: PW_Archives
Plugin URI: http://philipwalton.com/2011/02/08/pw_archives/
Description: A fully-customizable yet light-weight and intuitive archiving plugin. Its features include custom post type support, optional javascript enhancement, shortcodes, and widgets all with only one additional database query.
Version: 2.0.4
Author: Philip Walton
Author URI: http://philipwalton.com
*/

function pw_archives_init()
{
	require_once('PW_Archives_Controller.php');
	require_once('PW_Archives_Model.php');
	require_once('PW_Archives_Widget.php');

	global $PW_Archives;
	
	$PW_Archives = new PW_Archives_Controller();
	$PW_Archives->model = new PW_Archives_Model;
	$PW_Archives->view = dirname(__FILE__) . '/PW_Archives_View.php';
	
	register_widget('PW_Archives_Widget');
	add_shortcode( 'PW_Archives', array($PW_Archives, 'shortcode'));
}
add_action( 'pw_framework_loaded', 'pw_archives_init' );

require_once('PW_Framework/bootstrap.php');

// To call the function directly from php code
function PW_Archives($name)
{
	global $PW_Archives;
	if (isset($PW_Archives)) {
		$PW_Archives->display($name);
	}
}