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


class TopLinkStore{
	var $_table;
	
	function TopLinkStore()
	{
		$this->_table = TOPLINKS_DB_TABLE;
	}
	
	function insert($tl)
	{
		global $wpdb;
		/*
		$query = "INSERT INTO $this->_table VALUES ( " .
			"null, '". $wpdb->escape($tl->_post_id) ."', '". $wpdb->escape($tl->_url_name) ."', " .
			"'". $wpdb->escape($tl->_url) ."', '". $wpdb->escape($tl->_frequency) ."', " .
			"', 1, now(), now()" .
			")";
		*/
		$query = "INSERT INTO $this->_table VALUES ( 
			null, '". $tl->_post_id ."', '". $tl->_url_name.
			"', '". $tl->_url ."', '". $tl->_frequency.
			"', 1, now(), now() 
		  )";
		
		$wpdb->query($query);
		
		$tl->_id = $wpdb->insert_id;
		return $tl;
	}
	
	function update($tl)
	{
		global $wpdb;
		if ($tl->_id == null) return false; // Can't update an object without id.
		if ($tl->_url == null) return false; // Can't update an object without url.
	
		$query = "UPDATE $this->_table SET url_name = '".$tl->_url_name."', show_me='".$tl->_show_me."', modified= '".$tl->_modified."' WHERE url = '".$tl->_url."'";
		
		$wpdb->query($query);
	}
	
	function delete($tl)
	{
		global $wpdb;
		if ($tl->_id == null) return false; // Can't delete an object without an id.
		$query = "DELETE FROM". $this->_table ."WHERE post_id='".$tl->_post_id."'";
		$wpdb->query($query);
		return true;
		
	}
	
	function find($id=null)
	{
		global $wpdb;
		if ($id == null) return false; // Can't find an object without an id.
		$query = "SELECT * FROM $this->_table WHERE id=$id LIMIT 1";
		
		$results = $wpdb->get_results($query);
		$tl = $this->_createTopLinkFromResults($results[0]);
     
    	return $tl;
	}
	
	function _createTopLinkFromResults($results)
	{
		$tl = new TopLink();
		$tl->_id = $results->id;
		$tl->_post_id = $results->post_id;
		$tl->_url_name = $results->url_name;
	    $tl->_url = $results->url;
	    $tl->_frequency = $results->frequency;
	    $tl->_show_me = $results->show_me;
	    $tl->_created = $results->created;
	    $tl->_modified = $results->modified;
	    return $tl;
	}
	
	function findAll($start=0, $num=5, $show=1)
	{
		global $wpdb;

		if ($num=="all"){
			$num_limit = '';
		}else{
			$num_limit = 'LIMIT ' . $num;
		}
		
		//showme limits for display
		//showme = 0 will list all in DB
		if($show==1){ $showme = ' AND show_me = '. $show;}
		else { $showme = ''; }

		$start -= 1;	
		
		$query = "
			SELECT 
				id,
				post_id,
				url,
				url_name,
				SUM(frequency) as frequency,
				show_me
			FROM $this->_table 
			WHERE post_id > $start $showme
			GROUP BY url
			ORDER BY frequency DESC
			$num_limit
		";
		
		$results = $wpdb->get_results($query);
		$toplinks = array();
		foreach($results as $toplink)
		{
			$toplinks[] = $this->_createTopLinkFromResults($toplink);
		}
		return $toplinks;
	}
	
	function findByPage($num=0, $display=2, $show=1)
	{
		global $wpdb;
		
		$num_limit = "LIMIT " . $num . ", " . $display;
		
		//showme limits for display
		//showme = 0 will list all in DB
		if($show==1){ $showme = ' AND show_me = '. $show;}
		else { $showme = ''; }

		$start -= 1;	
		
		$query = "
			SELECT 
				id,
				post_id,
				url,
				url_name,
				SUM(frequency) as frequency,
				show_me
			FROM $this->_table 
			WHERE post_id > $start $showme
			GROUP BY url
			ORDER BY frequency DESC
			$num_limit
		";
		
		$results = $wpdb->get_results($query);
		$toplinks = array();
		foreach($results as $toplink)
		{
			$toplinks[] = $this->_createTopLinkFromResults($toplink);
		}
		return $toplinks;
	}
	
	function findMaxPage($per_page=5, $show=1)
	{
		global $wpdb;
		
		//showme limits for display
		//showme = 0 will list all in DB
		if($show==1){ $showme = ' AND show_me = '. $show;}
		else { $showme = ''; }

		$start -= 1;	
		
		$query = "
			SELECT 
				COUNT(url)
			FROM $this->_table 
			WHERE 1 $showme
			GROUP BY url
		";
		
		$total = $wpdb->query($query);
		
		return ceil($total/$per_page)	;
	}
}

class TopLink{

	var $_id		= null;
	var $_post_id 	= null;
	var $_url_name 	= null;
	var $_url		= null;
	var $_frequency = null;
	var $_show_me	= null;
	var $_created 	= null;
	var $_modified 	= null;
	
	function TopLink($initialize = null, $new = true){
		if ($initialize == null && $new) return;
		
		$this->_id = $initialize['toplinks_id'];
		$this->_post_id = $initialize['toplinks_post_id'];
		$this->_url_name = $initialize['toplinks_url_name'];
		$this->_url = $initialize['toplinks_url'];
		$this->_frequency = $initialize['toplinks_frequency'];
		$this->_show_me = $initialize['toplinks_show_me'];
	}
	
}
?>