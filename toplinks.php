<?php
/**
 * TopLinks Wordpress plugin
 * Copyright (c) 2008 76design/Thornley Fallis Communications
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
*/

/*
Plugin Name: TopLinks
Plugin URI: http://friendsroll.com
Description: TopLinks Wordpress plugin
Author: 76design / Thornley Fallis Communications
Version: 1.2
Author URI: http://76design.com
*/

@session_start();
if (file_exists('../../../wp-config.php'))
{
	require_once('../../../wp-config.php');
}

include_once(dirname(__FILE__) . "/class_toplinkstore.php");
include_once(dirname(__FILE__) . "/toplinks_functions.php");

//ini_set("include_path", ini_get("include_path") . PATH_SEPARATOR . dirname(__FILE__));
global $wpdb;
define("TOPLINKS_DB_TABLE", $wpdb->prefix . "toplinks");

$current_page = '';

if (isset($_GET['tl_getalltoplinks'])) {
	$tl = new TopLinkStore();
	$links = $tl->findAll(0, all, 1);
	ob_start();
	$i = 0;
	foreach($links as $top_link)
	{
		$class = "even";
		if ($i % 2) $class = "odd";
		widget_toplinks_display_link($top_link, $class);
		$i++;
	}
	$content = ob_get_contents();
	ob_end_clean();
	echo $content;
}

if (isset($_GET['tl_getpagetoplinks'])) {
	$current_page = $_GET['tl_getpagetoplinks'];
	$start_num = $current_page * 5;
	$tl = new TopLinkStore();
	$links = $tl->findByPage($start_num, 5);
	ob_start();
	$i = 0;
	foreach($links as $top_link)
	{
		$class = "even";
		if ($i % 2) $class = "odd";
		widget_toplinks_display_link($top_link, $class);
		$i++;
	}
	$content = ob_get_contents();
	ob_end_clean();
	echo $content;
}

if (isset($_GET['tl_toplinksmax'])) {
	$tl = new TopLinkStore();
	$max = $tl->findMaxPage();
	echo $max;
}

function widget_toplinks_init(){
	// Check to see required Widget API functions are defined...
	if ( !function_exists('register_sidebar_widget') || !function_exists('register_widget_control') )
			return; // ...and if not, exit gracefully from the script.

    // This registers the widget. About time.
    register_sidebar_widget('toplinks', 'widget_toplinks');

    // This registers the (optional!) widget control form.
	register_widget_control('toplinks', 'widget_toplinks_control');

    if(!is_admin()) {
    	add_action('wp_head', 'widget_toplinks_head');
    	add_action('wp_print_scripts', 'widget_toplinks_enqueue_script');
    }
}

// Delays plugin execution until Dynamic Sidebar has loaded first.
add_action('plugins_loaded', 'widget_toplinks_init');

// Executes before post is published
add_action('publish_post', 'widget_toplinks_populate_post');

add_action('delete_post', 'widget_toplinks_delete_post');

register_activation_hook('toplinks/toplinks.php', 'widget_toplinks_create_database');

add_action('admin_menu', 'widget_toplinks_admin_init');

register_deactivation_hook('toplinks/toplinks.php', 'widget_toplinks_drop_database');

?>
