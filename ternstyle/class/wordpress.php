<?php
////////////////////////////////////////////////////////////////////////////////////////////////////
////	File:
////		wordpress.php
////	Actions:
////		1) ternstyle's wordpress functions
////	Account:
////		Added on April 21st 2009
////	Version:
////		0.3
////
////	Written by Matthew Praetzel. Copyright (c) 2009 Matthew Praetzel.
////////////////////////////////////////////////////////////////////////////////////////////////////

/****************************************Commence Script*******************************************/

//                                *******************************                                 //
//________________________________** INITIALIZE VARIABLES      **_________________________________//
//////////////////////////////////**                           **///////////////////////////////////
//                                **                           **                                 //
//                                *******************************                                 //

//                                *******************************                                 //
//________________________________** WORDPRESS                 **_________________________________//
//////////////////////////////////**                           **///////////////////////////////////
//                                **                           **                                 //
//                                *******************************                                 //
if(!class_exists('ternWP')) {
//
class ternWP {

	function postByName($n) {
		global $wpdb;
		return $wpdb->get_var("select ID from $wpdb->posts where post_name='".$n."'");
	}
	function getOption($n,$d='',$v=false) {
		$o = get_option($n);
		if(!isset($o) and !empty($d)) {
			add_option($n,$d);
		}
		elseif(isset($o) and (empty($o) or $v) and !empty($d)) {
			update_option($n,$d);
		}
		elseif(isset($o) and !empty($d)) {
			foreach($d as $k => $v) {
				if(!isset($o[$k])) {
					$o[$k] = $v;
				}
			}
			update_option($n,$o);
		}
		return get_option($n);
	}
	function updateOption($n,$d,$w) {
		global $tern_wp_msg;
		$o = $this->getOption($n,$d);
		if(wp_verify_nonce($_REQUEST['_wpnonce'],$w) and $_REQUEST['action'] == 'update') {
			$f = new parseForm('post','_wp_http_referer,_wpnonce,action,submit,page');
			foreach($o as $k => $v) {
				if(!isset($f->a[$k])) {
					$f->a[$k] = $v;
				}
			}
			return $this->getOption($n,$f->a,true);
			$tern_wp_msg = empty($tern_wp_msg) ? 'You have successfully updated your settings.' : $tern_wp_msg;
		}
		else {
			return $this->getOption($n,$d);
		}
	}

}
$getWP = new ternWP;
//
}
	
/****************************************Terminate Script******************************************/
?>