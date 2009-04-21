<?php
////////////////////////////////////////////////////////////////////////////////////////////////////
////	File:
////		wordpress.php
////	Actions:
////		1) ternstyle's wordpress functions
////	Account:
////		Added on April 21st 2009
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
		return get_option($n);
	}

}
$getWP = new ternWP;
//
}
	
/****************************************Terminate Script******************************************/
?>