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


function widget_toplinks_admin_init(){
	include_once("admin-functions.php");
	add_submenu_page('edit.php','My TopLinks', 'top<strong>links</strong>', 1, 'toplinks', 'widget_toplinks_admin');
}

function widget_toplinks_create_database(){
	global $wpdb;

	$table = TOPLINKS_DB_TABLE;
 
  	//if (!preg_match('/'.$wpdb->prefix.'/', $table)) $table = $wpdb->prefix . "toplinks"; 
	
	if ($wpdb->get_var("SHOW TABLES LIKE '" . $table . "'") != $table)
   	{

   		$query = "
   			CREATE TABLE IF NOT EXISTS " . $table . " (
   				id int(11) unsigned not null auto_increment,
 				post_id int(11) unsigned not null,
 				url_name varchar(255) not null,
	   			url varchar(255) not null,
	   			frequency int(11) unsigned not null,
	   			show_me bool not null default '1',
	   			created datetime not null,
	   			modified datetime not null,
	   			PRIMARY KEY (id)
	   		)
	   	";
	   		
	   	require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
	   	dbDelta($query);
	}
	
	widget_toplinks_populate_all();
	   		
}
	
function widget_toplinks_drop_database(){
	global $wpdb;
	
	$table = TOPLINKS_DB_TABLE;
 
  //	if (!preg_match('/'.$wpdb->prefix.'/', $table)) $table = $wpdb->prefix . "toplinks"; 
	
	if ($wpdb->get_var("SHOW TABLES LIKE '" . $table . "'") == $table){
   		$query = "DROP TABLE ". $table;
	
		$results = $wpdb->query($query);
   	}
}

function widget_toplinks_admin() {
	$tls = new TopLinkStore;
	$options = get_option('widget_toplinks');
	//update[$top_link->_post_id][]
    
	if ($_POST['toplinks_updated']==1){
		$update = $_POST['update'];

		foreach ($update as $post=>$data){
			$tl_update = $tls->find((int)$post);
			$tl_update->_url_name = $data[0];
			if($data[1]=='on') {$tl_update->_show_me = 1;} else { $tl_update->_show_me = 0; }
			$tls->update($tl_update);
		}
		
		if($_POST['tl_toggle_favicon']=="on"){ 
			$options['toggle_favicon'] = 1;
		} else{
			$options['toggle_favicon'] = 0;
		}
		update_option('widget_toplinks', $options);
	}

	$top_links = $tls->findAll(0, all, 0);
	?>    
	<div class="wrap">
		<h2>my<em>links</em></h2>
			<form action='' method='POST'>
			<p><strong>Showing custom favicons may affect your blog's performance.</strong></p>
			<input type="checkbox" id="tl_toggle_favicon" name='tl_toggle_favicon' <?php echo $options['toggle_favicon']==1?"checked=\"checked\"":"" ?>/> <label for="tl_toggle_favicon">Turn off custom favicons</label>
			<p>The following links appear in your blog posts.  You may modify which you want to show in your TopLinks, as well as the TopLink names</p>
        	
        	<table class="widefat">
        		<thead>
        			<tr>
        				<th>Link Name</th>
        				<th>URL</th>
        				<th>Show Me</th>
        			</tr>
        		</thead>
        		<tbody>
        		
        		<?php 
        		$i = 0;
        		foreach ($top_links as $top_link){  
        		$class = '';
				if ($i % 2) $class=' class="alternate"';
        		?>
        		
        			<tr <?php echo $class; ?> >
        				<td><input type="text" name='update[<?php echo $top_link->_id?>][]' value="<?php echo $top_link->_url_name ?>" /></td>
        				<td><a href="<?php echo $top_link->_url ?>" target="_blank"><?php echo $top_link->_url ?></a></td>
        				<td><input type="checkbox" name='update[<?php echo $top_link->_id?>][]' <?php if($top_link->_show_me==1) { echo 'checked="checked"';} ?> /></td>
        			</tr>
        		<?php 
					$i++;
				} ?>
        		
        		</tbody>
        	</table>
        	<input type="hidden" name="toplinks_updated" value="1" />
        	<input type="submit" name="toplinks_save" value="Update &raquo;" /> 
        	</form>
        </div>
        <?php
}

function widget_toplinks($args){
	// $args is an array of strings which help your widget
	extract($args);
	
	global $current_page;
	global $wpdb;
	
	$options = get_option('widget_toplinks');
	$toshow = empty($options['toshow']) ? 5 : $options['toshow'];
        
	$tls = new TopLinkStore;
	$top_links = $tls->findAll(0,$toshow);
        
	//It's important to use the $before_widget, $before_title,
	// $after_title and $after_widget variables in your output.
	if(isset($before_widget) && preg_match("/id=[\"\']toplinks[\"\']/", $before_widget)){
		echo $before_widget;
	}else{
		echo "<div id=\"toplinks\">";
		$after_widget = "</div>";
	}
	?>
	
		<a name="toplinks"></a>
		<h1>top<em>links</em></h1>

	    <ol id="tl_toplinkslist">
		<?php
			$i = 0;
			foreach($top_links as $top_link)
	            {
	            	$class = "even";
	            	if ($i % 2) $class = "odd";
	            	
	            	widget_toplinks_display_link($top_link, $class);
	            	$i++;
	            }
		?>
		</ol>
			<div id="tl_page_nav"><?php widget_toplinks_display_nav($current_page); ?></div>
	            
		<ul>
			<li><a href="#" id="tl_show_all">Show all my links</a></li>
			<li><a href="http://friendsroll.com">Want your own <strong>toplinks</strong>?</a></li>
			
		</ul>
	                 
		<div id="tl_logo">
			<a href="#"><img src="<?php bloginfo('url'); ?>/wp-content/plugins/toplinks/images/toplinks.gif" /></a>
		</div>
	<?php
	echo $after_widget;
}

function widget_toplinks_display_link($tl, $class = "odd"){		
		$options = get_option('widget_toplinks');
		/*$favicon = bloginfo('url');
		if($options['toggle_favicon']==0){
			$favicon = widget_toplinks_favicon($tl->_url);
		}
		*/
    	?>
	    <li class="<?php echo $class?>">
	    <a href="<?php echo $tl->_url; ?>">		
	    	<img src="<?php echo $options['toggle_favicon']==0?widget_toplinks_favicon($tl->_url):bloginfo('url')."/wp-content/plugins/toplinks/images/default_favicon.gif" ?>" width='16' height='16' /> 	   
		    <p class="name"><?php echo $tl->_url_name; ?></p>
		    <p><?php if($tl->_frequency==1) {echo $tl->_frequency . " link"; } else { echo $tl->_frequency . " links"; } ?></p>
		</a>
    </li>
    <?php 
}

function widget_toplinks_display_nav($current_page=0){
	if(!isset($current_page) ) {
		echo "ERROR: Current page not set";
	}
	?>	
		<a href="#" id="tl_prev_page" style="display:none">&lt;prev</a>&nbsp;&nbsp;<a href="#" id="tl_next_page">next&gt;</a>
	<?php
}

function widget_toplinks_favicon($url) { 
	$urlbits = @parse_url($url);
	$favicon = $urlbits['scheme'] . "://" . $urlbits['host'] . "/favicon.ico";
	return $favicon;
}

function widget_toplinks_head() {
	?> 
	<link rel="stylesheet" href="<?php bloginfo('url'); ?>/wp-content/plugins/toplinks/toplinks.css" type="text/css" media="screen" />
	<script type="text/javascript">
		var toplinks_url = "<?= get_bloginfo('url'); ?>";
	</script>
	<?php
}

function widget_toplinks_enqueue_script() {
	wp_enqueue_script("jquery_latest", get_bloginfo('url') . "/wp-content/plugins/toplinks/js/jquery.js");
	wp_enqueue_script("toplinks", get_bloginfo('url') . "/wp-content/plugins/toplinks/js/toplinks.js", array('jquery_latest'), '');
}


function widget_toplinks_control() {
	// Collect our widget's options.
	$options = get_option('widget_toplinks');
	
	// This is for handing the control form submission.
	if ( $_POST['toplinks-submit'] ) {
		// Clean up control form submission options
		$newoptions['toshow'] = strip_tags(stripslashes($_POST['toplinks-toshow']));
	}
	
	// If original widget options do not match control form
	// submission options, update them.
	if ( $options != $newoptions ) {
		$options = $newoptions;
		update_option('widget_toplinks', $options);
	}
	
	// Format options as valid HTML. Hey, why not.
	$toshow = htmlspecialchars($options['toshow'], ENT_QUOTES);
	// The HTML below is the control form for editing options.
	?>
	<div>
		<label for="toplinks-toshow" style="line-height:35px;display:block;">TopLinks to show: <input type="text" id="toplinks-toshow" name="toplinks-toshow" value="<?php echo empty($toshow) ? 5 : $toshow; ?>" /></label>
		<input type="hidden" name="toplinks-submit" id="toplinks-submit" value="1" />
	</div>
	<?php
	// end of widget_toplinks_control()
}

function widget_toplinks_populate_all(){
	global $wpdb;
	$tl = new TopLinkStore;

	$lastposts = get_posts('numberposts=1000&order=DESC&orderby=post_date');
	
	$content = array();
	foreach($lastposts as $post) :
	    setup_postdata($post);
	    
	    $tl->_post_id = $post->ID;
	    
	    $title = $post->post_title;
		$content = $post->post_content;
		$content .= $title;
		
		$pattern = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";
		
		preg_match_all("/$pattern/siU", $content, $matches);
	    
		$i=0;
		foreach($matches[2] as $url){
			//parse out domain name
			$url_name = $matches[3][$i];	
			$url_bits = parse_url($url);
			$url = $url_bits['scheme'] . "://" . $url_bits['host'];
			
			$tl_insert->_post_id = $post->ID;
			$tl_insert->_url = $url;
			$tl_insert->_url_name = $url_bits['host'];
			$tl_insert->_frequency = 1; 
		
			$tl->insert($tl_insert);
		
			$i++;
		}
		
		
	endforeach;

}


function widget_toplinks_populate_post($post_ID){
	global $wpdb;
	$tl = new TopLinkStore;
	
	$post = get_post($post_ID);
	$tl_delete->_post_id = $post_ID;
	//if editing a post, delete all current entries related to post to repopulate
	if($tl->find($post_ID)){
		//$tl->delete($tl_delete);
		$query = "DELETE FROM ". TOPLINKS_DB_TABLE ." WHERE post_id=".$post_ID."";
		$wpdb->query($query);
	}

	$title = $post->post_title;
	$content = $post->post_content;
	$content .= $title;
	
	$pattern = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";
	
	//need to check title as well
	preg_match_all("/$pattern/siU", $content, $matches);
	
	$i=0;
	foreach($matches[2] as $url){
		//parse out domain name
		$url_name = $matches[3][$i];	
		$url_bits = parse_url($url);
		$url = $url_bits['scheme'] . "://" . $url_bits['host'];
	
		$tl_insert->_post_id = $post_ID;
		$tl_insert->_url = $url;
		$tl_insert->_url_name = $url_bits['host'];
		$tl_insert->_frequency = 1; 
		
		$tl->insert($tl_insert);

		$i++;
	}
	return $post_ID;
}

function widget_toplinks_delete_post($post_ID){
	global $wpdb;
	$query = "DELETE FROM ". TOPLINKS_DB_TABLE ." WHERE post_id=".$post_ID."";
	$wpdb->query($query);
}

function widget_toplinks_initjs(){

}
