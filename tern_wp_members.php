<?php
/*
Plugin Name: Members List
Plugin URI: http://www.ternstyle.us/
Description: List your members with pagination and search capabilities.
Author: Matthew Praetzel
Version: 1.5
Author URI: http://www.ternstyle.us/
Licensing : http://www.ternstyle.us/readme.html
*/

////////////////////////////////////////////////////////////////////////////////////////////////////
////	File:
////		tern_wp_members.php
////	Actions:
////		1) list members
////		2) search through members
////	Account:
////		Added on January 29th 2009
////
////	Written by Matthew Praetzel. Copyright (c) 2009 Matthew Praetzel.
////////////////////////////////////////////////////////////////////////////////////////////////////

/****************************************Commence Script*******************************************/

//                                *******************************                                 //
//________________________________** INITIALIZE VARIABLES      **_________________________________//
//////////////////////////////////**                           **///////////////////////////////////
//                                **                           **                                 //
//                                *******************************                                 //
$tern_wp_members_defaults = array('limit'=>10,'meta'=>'','url'=>get_bloginfo('home').'/members');
//                                *******************************                                 //
//________________________________** INCLUDES                  **_________________________________//
//////////////////////////////////**                           **///////////////////////////////////
//                                **                           **                                 //
//                                *******************************                                 //
require_once(ABSPATH.'wp-content/plugins/tern_wp_members/ternstyle/class/forms.php');
require_once(ABSPATH.'wp-content/plugins/tern_wp_members/ternstyle/class/select.php');
//                                *******************************                                 //
//________________________________** ADD EVENTS                **_________________________________//
//////////////////////////////////**                           **///////////////////////////////////
//                                **                           **                                 //
//                                *******************************                                 //
add_action('wp_print_scripts','tern_wp_members_scripts');
add_action('admin_menu','tern_wp_members_menu');
//                                *******************************                                 //
//________________________________** SCRIPTS                   **_________________________________//
//////////////////////////////////**                           **///////////////////////////////////
//                                **                           **                                 //
//                                *******************************                                 //
function tern_wp_members_scripts() {
	echo '<link rel="stylesheet" href="'.get_bloginfo('home').'/wp-content/plugins/tern_wp_members/tern_wp_members.css" type="text/css" media="all" />' . "\n";
}
//                                *******************************                                 //
//________________________________** MENUS                     **_________________________________//
//////////////////////////////////**                           **///////////////////////////////////
//                                **                           **                                 //
//                                *******************************                                 //
function tern_wp_members_menu() {
	if(function_exists('add_menu_page')) {
		add_options_page('Members Options','Members Plugin',10,__FILE__,'tern_wp_members_options');
	}
}
//                                *******************************                                 //
//________________________________** SETTINGS                  **_________________________________//
//////////////////////////////////**                           **///////////////////////////////////
//                                **                           **                                 //
//                                *******************************                                 //
function tern_wp_members_options() {
	global $getOPTS,$tern_wp_msg,$tern_wp_members_defaults;
	//
	if(wp_verify_nonce($_REQUEST['_wpnonce'],'tern_wp_members_nonce') and $_REQUEST['action'] == 'update') {
		$f = new parseForm('post','_wp_http_referer,_wpnonce,action,submit');
		$o = get_option('tern_wp_members');
		if(empty($o)) {
			if(!add_option('tern_wp_members',$f->a)) {
				$tern_wp_msg = 'There was an error while attempting to update your settings. Please try again.';
			}
		}
		else {
			if(!update_option('tern_wp_members',$f->a)) {
				$tern_wp_msg = 'There was an error while attempting to update your settings. Please try again.';
			}
		}
		if(empty($tern_wp_msg)) {
			$tern_wp_msg = 'You have successfully updated your settings.';
		}
	}
	//
	$o = get_option('tern_wp_members');
	if(empty($o)) {
		add_option('tern_wp_members',$tern_wp_members_defaults);
		$o = get_option('tern_wp_members');
	}
?>
<div class="wrap">
	<div id="icon-options-general" class="icon32"><br /></div>
	<h2>Members Settings</h2>
	<?php
		if(!empty($tern_wp_sponsor_msg)) {
			echo '<div id="message" class="updated fade"><p>'.$tern_wp_msg.'</p></div>';
		}
	?>
	<form method="post" action="">
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><label for="url">URL for your members page</label></th>
				<td>
					<input type="text" name="url" class="regular-text" value="<?=$o['url'];?>" />
					<span class="setting-description">http://blog.ternstyle.us/members</span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="limit">Number of viewable members at one time</label></th>
				<td>
					<?php
						$a = array(5,10,15,20,25,50,100,200);
						echo $getOPTS->select($a,'limit','limit','','',false,array($o['limit']));
					?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="meta">Meta fields to search by</label></th>
				<td>
					<textarea name="meta" style="width:100%;"><?=$o['meta'];?></textarea><br />
					<span class="setting-description">e.g. occupation,employer,department,city,state,zip,country</span>
				</td>
			</tr>
		</table>
		<p class="submit"><input type="submit" name="submit" class="button-primary" value="Save Changes" /></p>
		<input type="hidden" name="action" value="update" />
		<input type="hidden" id="_wpnonce" name="_wpnonce" value="<?=wp_create_nonce('tern_wp_members_nonce');?>" />
		<input type="hidden" name="_wp_http_referer" value="<?php wp_get_referer(); ?>" />
	</form>
</div>
<?php
}
//                                *******************************                                 //
//________________________________** COMPILE MEMBERS LIST      **_________________________________//
//////////////////////////////////**                           **///////////////////////////////////
//                                **                           **                                 //
//                                *******************************                                 //
class tern_members {

	//variables
	var $order = 'user_nicename';
	var $meta_fields = array('');
	var $fields = array('user_nicename','user_email','user_url');
	var $all_fields = array('Last Name'=>'last_name','First Name'=>'first_name','User Name'=>'user_nicename','Description'=>'description','Email'=>'user_email','URL'=>'user_url');
	var $num = 10;
	
	//functions
	function tern_members() {
		$o = get_option('tern_wp_members');
		if(!empty($o)) {
			$this->num = $o['limit'];
			$this->meta_fields = explode(',',$o['meta']);
		}
		$this->url = strpos($this->url,'?') !== false ? $o['url'] : $o['url'].'?';
	}
	function members($a) {
		$this->scope();
		$this->query();
		//
		if($a['search']) {
			$this->search();
		}
		if($a['pagination']) {
			$this->pagination();
		}
		if($a['sort']) {
			$this->sortby();
		}
		//
		$this->x = count($this->a) > 0 ? array_slice($this->a,($this->s*$this->num),$this->num) : $this->a;
		//
		echo '<ul class="tern_wp_members_list">';
		foreach($this->x as $u) {
			//compile name to be displayed
			$n = $u->first_name . ' ' . $u->last_name;
			$n = empty($u->first_name) ? $u->display_name : $n;
			if(!empty($n)) {
				//edit this code to suit how you'd like each user to be displayed in HTML
				echo '<li>
						<h3 id="tern_wp_member_'.$u->ID.'">
							<a href="'.get_bloginfo('url').'/?author='.$u->ID.'">'.$n.'</a>
						</h3>
						<div class="tern_wp_member_info">
							<a href="mailto:'.$u->user_email.'">'.$u->user_email.'</a><br />
							<a href="'.$u->user_url.'">'.$u->user_url.'</a>
						</div>
					</li>';
			}
		}
		echo '</ul>';
	}
	function query() {
		global $wpdb;
		$q = urldecode($_GET['query']);
		$t = $_GET['type'];
		$b = $_REQUEST['by'];
		if(empty($q)) {
			$this->r = $wpdb->get_col("select ID from $wpdb->users");
		}
		elseif(!empty($b)) {
			if(in_array($b,$this->fields)) {
				$this->r = $wpdb->get_col("select ID from $wpdb->users where instr($b,'$q') != 0");
			}
			else {
				$this->r = $wpdb->get_col("select user_id from $wpdb->usermeta where meta_key = '$b' and instr(meta_value,'$q') != 0");
			}
		}
		else {
			foreach($this->meta_fields as $v) {
				if($t != 'alpha') {
					$uq .= "select user_id from $wpdb->usermeta where meta_key = '".$v."' and instr(meta_value,'$q') != 0 union ";
				}
			}
			if($t == 'alpha') {
				$uq = "select user_id from $wpdb->usermeta where meta_key = 'last_name' and SUBSTRING(UPPER(meta_value),1,1) = '".$q."'";
			}
			else {
				$uq .= "select user_id from $wpdb->usermeta where meta_key = 'first_name' and STRCMP(meta_value,'$q') = 0 union " . 
						"select user_id from $wpdb->usermeta where meta_key = 'last_name' and STRCMP(meta_value,'$q') = 0 union " .
						"select user_id from $wpdb->usermeta where meta_key = 'description' and instr(meta_value,'$q') != 0";
			}
			$r = $wpdb->get_col($uq);
			//
			unset($uq);
			foreach($this->fields as $v) {
				if($t != 'alpha') {
					$uq .= empty($uq) ? "select ID from $wpdb->users where instr(".$v.",'$q') != 0" : " union select ID from $wpdb->users where instr(".$v.",'$q') != 0";
				}
			}
			$r = array_merge($r,$wpdb->get_col($uq));
			//
			$this->r = array_values(array_unique($r));
		}
		unset($this->a);
		foreach($this->r as $v) {
			if(!empty($v)) {
				$this->a[] = get_userdata($v);
			}
		}
		$s = empty($_GET['sort']) ? 'last_name' : $_GET['sort'];
		$o = empty($_GET['order']) ? 'asc' : $_GET['order'];
		$this->a = $this->sortMulti($this->a,$s,'str',$o);
		return $this->a;
	}
	function scope() {
		$this->p = empty($_GET['page']) ? 1 : $_GET['page'];
		$this->n = ceil(count($this->a)/$this->num);
		//start
		$this->s = intval($this->p-1);
		if(empty($this->s)) {
			$this->s = 0;
		}
		elseif($this->s >= $this->n) {
			$this->s = ($this->n-1);
		}
		//end
		$this->e = count($this->a) > (($this->s*$this->num)+$this->num) ? (($this->s*$this->num)+$this->num) : count($this->a);
	}
	function pagination() {
		$q = $_GET['query'];
		$b = $_GET['by'];
		$t = $_GET['type'];
		$this->scope();
		if($this->n > 1) {
			$s = $this->p-2;
			$e = ($s+4)>$this->n ? $this->n : $s+4;
			if($s <= 0) {
				$s = 1;
				$e = ($s+4)>$this->n ? $this->n : $s+4;
			}
			elseif(($this->p+2) > $this->n) {
				$e = $this->n;
				$s = ($e-4)<=0 ? 1 : $e-4;
			}
			//
			for($i=$s;$i<=$e;$i++) {
				$h = $this->url.'&page='.($i).'&query='.$q.'&by='.$b.'&type='.$t.'&sort='.$_GET['sort'].'&order='.$_GET['order'];
				$c = intval($this->s+1) == $i ? ' class="tern_members_pagination_current"' : '';
				$r .= '<li'.$c.'><a href="' . $h . '">' . $i . '</a></li>';
			}
			if($this->s > 0) {
				$r = '<li><a href="'.$this->url.'&page='.intval($this->s).'&query='.$q.'&by='.$b.'&type='.$t.'&sort='.$_GET['sort'].'&order='.$_GET['order'].'">Previous</a></li>'.$r;
			}
			if(count($this->a) > (($this->s*$this->num)+$this->num)) {
				$r .= '<li><a href="'.$this->url.'&page='.intval($this->s+2).'&query='.$q.'&by='.$b.'&type='.$t.'&sort='.$_GET['sort'].'&order='.$_GET['order'].'">Next</a></li>';
			}
			$r = '<ul class="tern_members_pagination tern_wp_pagination">' . $r . '</ul>';
		}
		//
		$m = '.';
		if($t == 'alpha') {
			$m = ' whose last names begin with the letter "'.strtoupper($q).'".';
		}
		echo '<div id="tern_members_pagination"><p>Now viewing <b>' . (($this->s*$this->num)+1) . '</b> through <b>' . $this->e . '</b> of <b>'.count($this->a).'</b> members found'.$m.'</p>' . $r.'</div>';
	}
	function search() {
		global $getOPTS;
		$a = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
		$o = array_merge($this->all_fields,$this->meta_fields);
		echo
		'<div id="tern_members_search">
		<form method="get" action="'.$this->url.'">
			<label for="query">Search Our Members:</label>
			<input type="text" id="query" name="query" id="" />
			by '.$getOPTS->selectPaired($o,'by','by','','','All Fields',array($_REQUEST['by'])).' 
			<input type="submit" value="Submit" />
		</form>
		Search alphabetically:<ul class="tern_members_pagination">';
		foreach($a as $v) {
			unset($c);
			if($v == $_GET['query']) {
				$c = 'class="tern_members_current"';
			}
			echo '<li><a '.$c.' href="'.$this->url.'&page=1&query='.$v.'&type=alpha">'.strtoupper($v).'</a></li>';
		}
		echo '</ul></div>';
	}
	function sortby() {
		$a = array('Last Name'=>'last_name','First Name'=>'first_name','Registration Date'=>'user_registered','Email'=>'user_email');
		//
		foreach($a as $k => $v) {
			unset($c);
			$o = 'asc';
			if($_GET['sort'] == $v and $_GET['order'] == 'asc') {
				$o = 'desc';
			}
			if($_GET['sort'] == $v) {
				$c = $o == 'asc' ? ' class="tern_members_sorted_u" ' : ' class="tern_members_sorted_d" ';
			}
			$r .= '<li'.$c.'><a href="'.$this->url.'&query='.urldecode($_GET['query']).'&by='.$_GET['by'].'&type='.$_GET['type'].'&sort='.$v.'&order='.$o.'">'.$k.'</a></li>';
		}
		echo '<div id="tern_members_sort"><label>Sort by:</label><ul>'.$r.'</ul></div>';
	}
	function sortMulti($a,$c,$t,$o,$p=false) {
		$r = array();
		for($i=0;$i<count($a);$i++) {
			if(empty($r)) {
				$r[] = $a[$i];
			}
			else {
				for($b=0;$b<count($r);$b++) {
					if($t == "str") {
						if(strcmp(strtolower($a[$i]->$c),strtolower($r[$b]->$c)) <= 0) {
							$n = array($a[$i]);
							array_splice($r,$b,0,$n);
							break;
						}
						elseif(strcmp(strtolower($a[$i]->$c),strtolower($r[$b]->$c)) > 0 and $b == (count($r)-1)) {
							array_push($r,$a[$i]);
							break;
						}
					}
					elseif($t == "num") {
						if($a[$i][$c] < $r[$b][$c] or $a[$i][$c] == $r[$b][$c]) {
							$n = array($a[$i]);
							array_splice($r,$b,0,$n);
							break;
						}
						elseif($a[$i][$c] > $r[$b][$c] and $b == (count($r)-1)) {
							array_push($r,$a[$i]);
							break;
						}
					}
				}
			}
		}
		if($o == "desc") {
			$r = is_array($r) ? array_reverse($r) : array();
		}
		return $r;
	}
	
}

/****************************************Terminate Script******************************************/
?>