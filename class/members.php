<?php
////////////////////////////////////////////////////////////////////////////////////////////////////
//
//		File:
//			members.php
//		Description:
//			This class controls the compilation of the members list and its pagination.
//		Actions:
//			1) compile members list
//			2) compile members list pagination
//			3) compile members list search options
//		Date:
//			Added January 29th, 2009
//		Version:
//			1.0
//		Copyright:
//			Copyright (c) 2009 Matthew Praetzel.
//		License:
//			This software is licensed under the terms of the GNU Lesser General Public License v3
//			as published by the Free Software Foundation. You should have received a copy of of
//			the GNU Lesser General Public License along with this software. In the event that you
//			have not, please visit: http://www.gnu.org/licenses/gpl-3.0.txt
//
////////////////////////////////////////////////////////////////////////////////////////////////////

/****************************************Commence Script*******************************************/

//                                *******************************                                 //
//________________________________** MEMBERS LIST              **_________________________________//
//////////////////////////////////**                           **///////////////////////////////////
//                                **                           **                                 //
//                                *******************************                                 //
if(!class_exists('tern_members')) {
//
class tern_members {
	
	function tern_members() {
		global $getFIX,$getWP,$tern_wp_members_defaults;
		$this->wp = $getWP;
		$o = $this->wp->getOption('tern_wp_members',$tern_wp_members_defaults);
		if(!empty($o)) {
			$this->num = $o['limit'];
			$f = explode(',',$o['meta']);
			$f = $getFIX->removeEmptyValues($f);
			$a = array();
			foreach($f as $k => $v) {
				$a[$v] = $v;
			}
			$this->meta_fields = $a;
		}
		$this->url = strpos($o['url'],'?') !== false ? $o['url'] : $o['url'].'?';
	}
	function members($a,$e=true) {
		global $tern_wp_members_defaults;
		$this->scope();
		$this->query();
		$o = $this->wp->getOption('tern_wp_members',$tern_wp_members_defaults);
		//
		$r = '<div id="tern_members">';
		if($a['search']) {
			$r .= $this->search();
		}
		if($a['alpha']) {
			$r .= $this->alpha();
		}
		$r .= $this->viewing($a);
		if($a['sort']) {
			$r .= $this->sortby();
		}
		$r .= '<ul class="tern_wp_members_list">';
		foreach($this->r as $u) {
			//get user info
			$u = new WP_User($u);
			//compile name to be displayed
			$n = $u->first_name . ' ' . $u->last_name;
			$n = empty($u->first_name) ? $u->display_name : $n;
			if(!empty($n)) {
				$r .= $this->markup($u);
			}
		}
		$r .= '</ul>';
		if($a['pagination2']) {
			$r .= $this->pagination();
		}
		$r .= '</div>';
		if($e) { echo $r; }
		return $r;
	}
	function scope() {
		$this->p = empty($_GET['page']) ? 1 : $_GET['page'];
		$this->n = ceil($this->total/$this->num);
		$this->s = intval($this->p-1);
		if(empty($this->s)) {
			$this->s = 0;
		}
		elseif($this->n > 0 and $this->s >= $this->n) {
			$this->s = ($this->n-1);
		}
		$this->e = $this->total > (($this->s*$this->num)+$this->num) ? (($this->s*$this->num)+$this->num) : $this->total;
	}
	function query($g=false) {
		global $wpdb,$tern_wp_members_defaults,$tern_wp_user_fields,$tern_wp_members_fields,$tern_wp_meta_fields;
		$o = $this->wp->getOption('tern_wp_members',$tern_wp_members_defaults);
		foreach($_GET as $k => $v) {
			$$k = $this->sanitize($v);
		}
		$sort = empty($sort) ? $o['sort'] : $sort;
		$order = empty($order) ? $o['order'] : $order;
		$s = strval($this->s*$this->num);
		$e = strval($this->num);
		//
		$h = !empty($o['hidden']) ? " and ID NOT IN (".implode(',',$o['hidden']).")" : '';
		//
		if($g == 'all') {
			$q = "select ID from $wpdb->users";
		}
		elseif(empty($query)) {
			if(in_array($sort,$tern_wp_user_fields)) {
				$q = "select distinct ID from $wpdb->users where 1 = 1 $h order by $sort $order limit $s,$e";//
			}
			else {
				$q = "select distinct ID from $wpdb->users left join $wpdb->usermeta on ($wpdb->users.ID = $wpdb->usermeta.user_id and $wpdb->usermeta.meta_key = '".$sort."') where 1 = 1 $h order by $wpdb->usermeta.meta_value $order limit $s,$e";//
			}
			$tq = "select COUNT(distinct ID) from $wpdb->users where 1 = 1 $h";//
		}
		elseif(!empty($by)) {
			if(in_array($by,$tern_wp_user_fields)) {
				if(in_array($sort,$tern_wp_user_fields)) {
					$q = "select distinct ID from $wpdb->users where instr($by,'$query') != 0 $h order by $sort $order limit $s,$e";//
				}
				else {
					$q = "select distinct ID from $wpdb->users left join $wpdb->usermeta on ($wpdb->users.ID = $wpdb->usermeta.user_id and $wpdb->usermeta.meta_key = '$sort') where instr($wpdb->users.$by,'$query') != 0 $h order by $wpdb->usermeta.meta_value $order limit $s,$e";//
				}
				$tq = "select COUNT(distinct ID) from $wpdb->users where instr($by,'$query') != 0 $h";//
			}
			else {
				if(in_array($sort,$tern_wp_user_fields)) {
					$h = !empty($o['hidden']) ? " and $wpdb->users.ID NOT IN (".implode(',',$o['hidden']).")" : '';
					$q = "select distinct $wpdb->users.ID from $wpdb->users join $wpdb->usermeta on ($wpdb->users.ID = $wpdb->usermeta.user_id) where $wpdb->usermeta.meta_key = '$by' and instr($wpdb->usermeta.meta_value,'$query') != 0 $h order by $wpdb->users.$sort $order limit $s,$e";//
				}
				else {
					$h = !empty($o['hidden']) ? " and t1.user_id NOT IN (".implode(',',$o['hidden']).")" : '';
					$q = "select distinct t1.user_id from $wpdb->usermeta as t1, $wpdb->usermeta as t2 where t1.user_id = t2.user_id and t1.meta_key = '$by' and instr(t1.meta_value,'$query') != 0 and t2.meta_key='$sort' $h order by t2.meta_value $order limit $s,$e";//
				}
				$h = !empty($o['hidden']) ? " and user_id NOT IN (".implode(',',$o['hidden']).")" : '';
				$tq = "select COUNT(distinct user_id) from $wpdb->usermeta where meta_key = '$by' and instr(meta_value,'$query') != 0 $h";//
			}
		}
		else {
			if($type == 'alpha') {
				if(in_array($sort,$tern_wp_user_fields)) {
					$h = !empty($o['hidden']) ? " and $wpdb->users.ID NOT IN (".implode(',',$o['hidden']).")" : '';
					$q = "select distinct $wpdb->users.ID from $wpdb->users join $wpdb->usermeta on ($wpdb->users.ID = $wpdb->usermeta.user_id) where $wpdb->usermeta.meta_key = 'last_name' and SUBSTRING(LOWER($wpdb->usermeta.meta_value),1,1) = '$query' $h order by $wpdb->users.$sort $order limit $s,$e";//
				}
				else {
					$h = !empty($o['hidden']) ? " and t1.user_id NOT IN (".implode(',',$o['hidden']).")" : '';
					$q = "select distinct t1.user_id from $wpdb->usermeta as t1, $wpdb->usermeta as t2 where t1.user_id = t2.user_id and t1.meta_key = 'last_name' and SUBSTRING(LOWER(t1.meta_value),1,1) = '$query' and t2.meta_key='".$sort."' $h order by t2.meta_value $order limit $s,$e";//
				}
				$h = !empty($o['hidden']) ? " and user_id NOT IN (".implode(',',$o['hidden']).")" : '';
				$tq = "select COUNT(distinct user_id) from $wpdb->usermeta where meta_key = 'last_name' and SUBSTRING(UPPER($wpdb->usermeta.meta_value),1,1) = '$query' $h";//
			}
			else {
				$c = 1;
				$a = array_merge($this->meta_fields,$tern_wp_meta_fields);
				foreach($a as $v) {
					$w .= empty($w) ? " t2.meta_key = '$v'" : " or t2.meta_key = '$v'";
				}
				foreach($tern_wp_members_fields as $v) {
					$x .= empty($x) ? " instr($wpdb->users.$v,'$query') != 0 " : " or instr($wpdb->users.$v,'$query') != 0 ";
				}
				$h = !empty($o['hidden']) ? " and $wpdb->users.ID NOT IN (".implode(',',$o['hidden']).")" : '';
				if(in_array($sort,$tern_wp_user_fields)) {
					
					$q = "select distinct $wpdb->users.ID from $wpdb->users left join $wpdb->usermeta as t2 on ($wpdb->users.ID = t2.user_id) where 1=1 and( (($w) and instr(t2.meta_value,'$query') != 0) or ($x) ) $h order by $wpdb->users.$sort $order limit $s,$e";//
				}
				else {
					$q = "select distinct $wpdb->users.ID from $wpdb->users left join $wpdb->usermeta as t1 on ($wpdb->users.ID = t1.user_id and t1.meta_key='$sort') left join $wpdb->usermeta as t2 on ($wpdb->users.ID = t2.user_id) where 1=1 and( (($w) and instr(t2.meta_value,'$query') != 0) or ($x) ) $h order by t1.meta_value $order limit $s,$e";
				}
				$tq = "select COUNT(distinct $wpdb->users.ID) from $wpdb->users left join $wpdb->usermeta as t2 on ($wpdb->users.ID = t2.user_id) where 1=1 and( (($w) and instr(t2.meta_value,'$query') != 0) or ($x) ) $h";
			}
		}
		$this->r = $wpdb->get_col($q);
		$this->total = intval($wpdb->get_var($tq));
		return $this->r;
	}
	function pagination($z=false) {
		global $tern_wp_members_defaults;
		$o = $this->wp->getOption('tern_wp_members',$tern_wp_members_defaults);
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
			$sort = empty($_GET['sort']) ? $o['sort'] : $_GET['sort'];
			$order = empty($_GET['order']) ? $o['order'] : $_GET['order'];
			for($i=$s;$i<=$e;$i++) {
				$h = $this->url.'&page='.($i).'&query='.$q.'&by='.$b.'&type='.$t.'&sort='.$sort.'&order='.$order;
				$c = intval($this->s+1) == $i ? ' class="tern_members_pagination_current tern_pagination_current"' : '';
				$r .= '<li'.$c.'><a href="' . $h . '">' . $i . '</a></li>';
			}
			if($this->s > 0) {
				$r = '<li><a href="'.$this->url.'&page='.intval($this->s).'&query='.$q.'&by='.$b.'&type='.$t.'&sort='.$sort.'&order='.$order.'">Previous</a></li>'.$r;
			}
			if($this->total > (($this->s*$this->num)+$this->num)) {
				$r .= '<li><a href="'.$this->url.'&page='.intval($this->s+2).'&query='.$q.'&by='.$b.'&type='.$t.'&sort='.$sort.'&order='.$order.'">Next</a></li>';
				$r .= '<li><a href="'.$this->url.'&page='.$this->n.'&query='.$q.'&by='.$b.'&type='.$t.'&sort='.$sort.'&order='.$order.'">Last</a></li>';
			}
			$r = $this->s > 0 ? '<li><a href="'.$this->url.'&page=1&query='.$q.'&by='.$b.'&type='.$t.'&sort='.$sort.'&order='.$order.'">First</a></li>'.$r : $r;
			$r = '<ul class="tern_pagination">' . $r . '</ul>';
		}
		if($z) { echo $r; }
		return $r;
	}
	function search($e=false) {
		global $ternSel,$tern_wp_members_fields,$tern_wp_meta_fields,$tern_wp_members_defaults;
		$p = $this->wp->getOption('tern_wp_members',$tern_wp_members_defaults);
		$a = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
		$o = array_merge($tern_wp_meta_fields,$tern_wp_members_fields,$this->meta_fields);
		$r = '<div class="tern_members_search">
		<form method="get" action="'.$this->url.'">
			<h2>Search Our '.ucwords($p['noun']).':</h2>
			<input type="text" id="query" name="query" class="blur" value="search..." />
			by '.$ternSel->create(array(
						'type'			=>	'paired',
						'data'			=>	$o,
						'id'			=>	'by',
						'name'			=>	'by',
						'select_value'	=>	'All Fields',
						'selected'		=>	array($_REQUEST['by'])
					)).'<input type="hidden" name="p" value="'.$_REQUEST['p'].'" />
			<input type="hidden" name="page_id" value="'.$_REQUEST['page_id'].'" />
			<input type="submit" value="Submit" />
		</form></div>';
		if($e) { echo $r; }
		return $r;
	}
	function alpha($e=false) {
		$a = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
		$r = '<div class="tern_members_alpha">Search alphabetically <span>(by last name)</span>:<br /><ul>';
		foreach($a as $v) {
			unset($c);
			if($v == $_GET['query']) {
				$c = 'class="tern_members_selected"';
			}
			$r .= '<li><a '.$c.' href="'.$this->url.'&page=1&query='.$v.'&type=alpha&sort=last_name">'.strtoupper($v).'</a></li>';
		}
		$r .= '</ul></div>';
		if($e) { echo $r; }
		return $r;
	}
	function sortby($e=false) {
		global $tern_wp_members_defaults;
		$b = $this->wp->getOption('tern_wp_members',$tern_wp_members_defaults);
		$a = array('Last Name'=>'last_name','First Name'=>'first_name','Registration Date'=>'user_registered','Email'=>'user_email');
		foreach($a as $k => $v) {
			unset($c);
			$o = 'asc';
			if($_GET['sort'] == $v and $_GET['order'] == 'asc') {
				$o = 'desc';
			}
			if($_GET['sort'] == $v) {
				$c = $o == 'asc' ? ' class="tern_members_sorted_u" ' : ' class="tern_members_sorted_d" ';
			}
			if(empty($_GET['sort']) and $b['sort'] == $v) {
				$o = $b['order'] == 'asc' ? 'desc' : 'asc';
				$c = $o == 'asc' ? ' class="tern_members_sorted_u" ' : ' class="tern_members_sorted_d" ';
			}
			$r .= '<li'.$c.'><a href="'.$this->url.'&query='.urldecode($_GET['query']).'&by='.$_GET['by'].'&type='.$_GET['type'].'&sort='.$v.'&order='.$o.'">'.$k.'</a></li>';
		}
		$r = '<div class="tern_members_sort"><label>Sort by:</label><ul>'.$r.'</ul></div>';
		if($e) { echo $r; }
		return $r;
	}
	function viewing($a,$e=false) {
		global $tern_wp_members_defaults;
		$o = $this->wp->getOption('tern_wp_members',$tern_wp_members_defaults);
		$this->scope();
		$v = $this->total > 0 ? (($this->s*$this->num)+1) : '0';
		$m = '.';
		if($t == 'alpha') {
			$m = ' whose last names begin with the letter "'.strtoupper($q).'".';
		}
		$r = '<div class="tern_members_view">Now viewing <b>' . $v . '</b> through <b>' . $this->e . '</b> of <b>'.$this->total.'</b> '.$o['noun'].' found'.$m;
		if($a['pagination']) {
			$r .= $this->pagination();
		}
		$r .= '</div>';
		if($e) { echo $r; }
		return $r;
	}
	function markup($u) {
		global $tern_wp_members_defaults;
		$o = $this->wp->getOption('tern_wp_members',$tern_wp_members_defaults);
		$s = '<li>'."\n    ";
		if($o['gravatars']) {
			$s .= '<a class="tern_wp_member_gravatar" href="'.get_author_posts_url($u->ID).'">'."\n        ".get_avatar($u->ID,60)."\n    ".'</a>'."\n    ";
		}
		$s .= '<div class="tern_wp_member_info">';
		foreach($o['fields'] as $k => $v) {
			if($v['name'] == 'user_email' and $o['hide_email'] and !is_user_logged_in()) {
				continue;
			}
			$s .= "\n        ".str_replace('%author_url%',get_author_posts_url($u->ID),str_replace('%value%',$u->$v['name'],$v['markup']));
		}
		return $s."\n    ".'</div>'."\n".'</li>';
	}
	function sanitize($s) {
		// to be used in future updates
		return $s;
	}
	
}
//
}
	
/****************************************Terminate Script******************************************/
?>