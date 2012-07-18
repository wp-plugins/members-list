<?php
/*
Plugin Name: Members List
Plugin URI: http://www.ternstyle.us/products/plugins/wordpress/wordpress-members-plugin
Description: List your members with pagination and search capabilities.
Author: Matthew Praetzel
Version: 2.2.1
Author URI: http://www.ternstyle.us/
Licensing : http://www.gnu.org/licenses/gpl-3.0.txt
*/

////////////////////////////////////////////////////////////////////////////////////////////////////
////	File:
////		tern_wp_members.php
////	Actions:
////		1) list members
////		2) search through members
////	Account:
////		Added on January 29th 2009
////	Version:
////		2.2.1
////
////	Written by Matthew Praetzel. Copyright (c) 2009 Matthew Praetzel.
////////////////////////////////////////////////////////////////////////////////////////////////////

/****************************************Commence Script*******************************************/

//                                *******************************                                 //
//________________________________** INITIALIZE VARIABLES      **_________________________________//
//////////////////////////////////**                           **///////////////////////////////////
//                                **                           **                                 //
//                                *******************************                                 //
$tern_wp_members_defaults = array(
	'limit'		=>	10,
	'sort'		=>	'last_name',
	'order'		=>	'asc',
	'meta'		=>	'',
	'url'		=>	get_bloginfo('home').'/members',
	'gravatars'	=>	1,
	'hidden'	=>	array(),
	'fields'	=>	array(
		'User Name'		=>	array(
			'name'		=>	'user_nicename',
			'markup'	=>	'<div class="tern_wp_members_user_nicename"><h3><a href="%author_url%">%value%</a></h3></div>'
		),
		'Email Address'	=>	array(
			'name'		=>	'user_email',
			'markup'	=>	'<div class="tern_wp_members_user_email"><a href="mailto:%value%">%value%</a></div>'
		),
		'URL'			=>	array(
			'name'		=>	'user_url',
			'markup'	=>	'<div class="tern_wp_members_user_url"><a href="%value%">%value%</a></div>'
		)
	)
);
$tern_wp_members_fields = array(
	'Last Name'		=>	'last_name',
	'First Name'	=>	'first_name',
	'User Name'		=>	'user_nicename',
	'Description'	=>	'description',
	'Email Address'	=>	'user_email',
	'URL'			=>	'user_url',
	'User Level'	=>	'user_level'
);
//                                *******************************                                 //
//________________________________** INCLUDES                  **_________________________________//
//////////////////////////////////**                           **///////////////////////////////////
//                                **                           **                                 //
//                                *******************************                                 //
require_once(ABSPATH.'wp-content/plugins/members-list/ternstyle/class/wordpress.php');
require_once(ABSPATH.'wp-content/plugins/members-list/ternstyle/class/forms.php');
require_once(ABSPATH.'wp-content/plugins/members-list/ternstyle/class/select.php');
require_once(ABSPATH.'wp-content/plugins/members-list/ternstyle/class/arrays.php');
//                                *******************************                                 //
//________________________________** ADD EVENTS                **_________________________________//
//////////////////////////////////**                           **///////////////////////////////////
//                                **                           **                                 //
//                                *******************************                                 //
add_action('init','tern_wp_members_actions');
add_action('init','tern_wp_members_js');
add_action('wp_print_scripts','tern_wp_members_scripts');
add_action('admin_menu','tern_wp_members_menu');
//                                *******************************                                 //
//________________________________** ACTIONS                   **_________________________________//
//////////////////////////////////**                           **///////////////////////////////////
//                                **                           **                                 //
//                                *******************************                                 //
function tern_wp_members_actions() {
	global $getWP,$tern_wp_members_defaults,$current_user;
	get_currentuserinfo();
	$o = $getWP->getOption('tern_wp_members',$tern_wp_members_defaults);
	//Configure Mark-Up Page Actions
	if($_REQUEST['page'] == 'Configure Mark-Up') {
		if(wp_verify_nonce($_REQUEST['_wpnonce'],'tern_wp_members_nonce')) {
			switch($_REQUEST['action']) {
				//update all fields
				case 'update' :
					$o['fields'] = array();
					foreach($_REQUEST['field_titles'] as $k => $v) {
						$v = stripslashes($v);
						$o['fields'][$v] = array(
							'name'		=>	$_REQUEST['field_names'][$k],
							'markup'	=>	stripslashes($_REQUEST['field_markups'][$k])
						);
					}
					$o = $getWP->getOption('tern_wp_members',$o,true);
					echo '<div id="message" class="updated fade"><p>Your order has been successfully saved.</p></div>';
					die();
				//add a field
				case 'add' :
					$f = $_REQUEST['new_field'];
					$o['fields'][$f] = array(
						'name'		=>	$f,
						'markup'	=>	'<div class="tern_wp_members_'.$f.'">%value%</div>'
					);
					$o = $getWP->getOption('tern_wp_members',$o,true);
				//delete a field
				case 'remove' :
					$a = array();
					foreach($o['fields'] as $k => $v) {
						if($v['name'] != $_REQUEST['fields'][0]) {
							$a[$k] = $v;
						}
					}
					$o['fields'] = $a;
					$o = $getWP->getOption('tern_wp_members',$o,true);
			}
		}
		//attempted to update all fields without nonce
		elseif($_REQUEST['action'] == 'update' or $_REQUEST['action'] == 'add' or $_REQUEST['action'] == 'delete') {
			echo '<div id="message" class="updated fade"><p>There was an error whil processing your request. Please try again.</p></div>';
			die();
		}
		//get sample mark-up
		if($_REQUEST['action'] == 'getmarkup') {
			$m = new tern_members();
			echo htmlentities($m->markup($current_user));
			die();
		}
	}
}
//                                *******************************                                 //
//________________________________** SCRIPTS                   **_________________________________//
//////////////////////////////////**                           **///////////////////////////////////
//                                **                           **                                 //
//                                *******************************                                 //
function tern_wp_members_scripts() {
	if(!is_admin() or $_REQUEST['page'] == 'Configure Mark-Up') {
		echo '<link rel="stylesheet" href="'.get_bloginfo('home').'/wp-content/plugins/members-list/tern_wp_members.css" type="text/css" media="all" />' . "\n";
		echo '<script type="text/javascript">var tern_wp_root = "'.get_bloginfo('home').'";</script>';
	}
}
function tern_wp_members_js() {
	if($_REQUEST['page'] == 'Configure Mark-Up') {
		wp_enqueue_script('TableDnD',get_bloginfo('home').'/wp-content/plugins/members-list/js/jquery.tablednd_0_5.js.php',array('jquery'),'0.5');
		wp_enqueue_script('members-list',get_bloginfo('home').'/wp-content/plugins/members-list/js/members-list.js');
	}
}
//                                *******************************                                 //
//________________________________** MENUS                     **_________________________________//
//////////////////////////////////**                           **///////////////////////////////////
//                                **                           **                                 //
//                                *******************************                                 //
function tern_wp_members_menu() {
	if(function_exists('add_menu_page')) {
		add_menu_page('Members List','Members List',10,__FILE__,'tern_wp_members_options');
		add_submenu_page(__FILE__,'Members List','Settings',10,__FILE__,'tern_wp_members_options');
		add_submenu_page(__FILE__,'Configure Mark-Up','Configure Mark-Up',10,'Configure Mark-Up','tern_wp_members_markup');
		add_submenu_page(__FILE__,'Edit Members','Edit Members',10,'Edit Members List','tern_wp_members_list');
	}
}
//                                *******************************                                 //
//________________________________** SETTINGS                  **_________________________________//
//////////////////////////////////**                           **///////////////////////////////////
//                                **                           **                                 //
//                                *******************************                                 //
function tern_wp_members_options() {
	global $getWP,$getOPTS,$tern_wp_msg,$tern_wp_members_defaults,$tern_wp_members_fields,$wpdb;
	$o = $getWP->getOption('tern_wp_members',$tern_wp_members_defaults);
	if(wp_verify_nonce($_REQUEST['_wpnonce'],'tern_wp_members_nonce') and $_REQUEST['action'] == 'update') {
		$f = new parseForm('post','_wp_http_referer,_wpnonce,action,submit');
		$o = $getWP->getOption('tern_wp_members',$f->a,true);
		$tern_wp_msg = empty($tern_wp_msg) ? 'You have successfully updated your settings.' : $tern_wp_msg;
	}
?>
<div class="wrap">
	<div id="icon-options-general" class="icon32"><br /></div>
	<h2>Members Settings</h2>
	<?php
		if(!empty($tern_wp_msg)) {
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
				<th scope="row"><label for="sort">Sort the members list originally by</label></th>
				<td>
					<?php
						$a = array();
						foreach($tern_wp_members_fields as $k => $v) {
							$a['Standard Fields'][] = array($k,$v);
						}
						$r = $wpdb->get_col("select distinct meta_key from $wpdb->usermeta");
						foreach($r as $v) {
							if(in_array($v,$tern_wp_members_fields)) {
								continue;
							}
							$a['Available Meta Fields'][] = array($v,$v);
						}
						echo $getOPTS->selectTiered($a,1,0,'sort','sort','Sort','',false,array($o['sort']));
					?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="order">Sort members list originally in this order</label></th>
				<td>
					<input type="radio" name="order" value="asc" <?php if($o['order'] == 'asc') { echo 'checked'; } ?> /> Ascending
					<input type="radio" name="order" value="desc" <?php if($o['order'] == 'desc') { echo 'checked'; } ?> /> Descending
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="meta">Meta fields to search by</label></th>
				<td>
					<textarea name="meta" style="width:100%;"><?=$o['meta'];?></textarea><br />
					<span class="setting-description">e.g. occupation,employer,department,city,state,zip,country</span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label for="gravatars">Use gravatars?</label></th>
				<td>
					<input type="radio" name="gravatars" value="1" <?php if($o['gravatars']) { echo 'checked'; } ?> /> yes
					<input type="radio" name="gravatars" value="0" <?php if(!$o['gravatars']) { echo 'checked'; } ?> /> no
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
//________________________________** MARK-UP                   **_________________________________//
//////////////////////////////////**                           **///////////////////////////////////
//                                **                           **                                 //
//                                *******************************                                 //
function tern_wp_members_markup() {
	global $wpdb,$getWP,$getOPTS,$tern_wp_members_defaults,$tern_wp_msg,$tern_wp_members_fields,$current_user;
	$o = $getWP->getOption('tern_wp_members',$tern_wp_members_defaults);
	get_currentuserinfo();
?>
	<div class="wrap">
		<div id="icon-options-general" class="icon32"><br /></div>
		<h2>Configure Your Members List Mark-Up</h2>
		<p>
			Below you can configure what fields are shown when viewing your members list. Add fields to be displayed and edit their names, 
			mark-up and order. When editing their mark-up, use the string %value% to place the respective value for each field and use the string 
			%author_url% to add the url (e.g. http://blog.ternstyle.us/?author=1) for each respective author's page.
		</p>
		<div id="tern_wp_message">
		<?php
			if(!empty($tern_wp_msg)) {
				echo '<div id="message" class="updated fade"><p>'.$tern_wp_msg.'</p></div>';
			}
		?>
		</div>
		<form class="field-form" action="" method="get">
			<p class="field-box">
				<label class="hidden" for="new-field-input">Add New Field:</label>
				<?php
					foreach($tern_wp_members_fields as $k => $v) {
						foreach($o['fields'] as $w) {
							if($v == $w['name']) {
								continue 2;
							}
						}
						$a['Standard Fields'][] = array($k,$v);
					}
					$r = $wpdb->get_col("select distinct meta_key from $wpdb->usermeta");
					foreach($r as $v) {
						if(in_array($v,$tern_wp_members_fields)) {
							continue;
						}
						foreach($o['fields'] as $w) {
							if($v == $w['name']) {
								continue 2;
							}
						}
						$a['Available Meta Fields'][] = array($v,$v);
					}
					echo $getOPTS->selectTiered($a,1,0,'new_field','new_field','Add New Field','',false);
				?>
				<input type="hidden" id="page" name="page" value="Configure Mark-Up" />
				<input type="submit" value="Add New Field" class="button" />
				<input type="hidden" name="action" value="add" />
				<input type="hidden" id="_wpnonce" name="_wpnonce" value="<?=wp_create_nonce('tern_wp_members_nonce');?>" />
				<input type="hidden" name="_wp_http_referer" value="<?php wp_get_referer(); ?>" />
			</p>
		</form>
		<form id="tern_wp_members_list_fm" method="post" action="">
			<table id="members_list_fields" class="widefat fixed" cellspacing="0">
				<thead>
				<tr class="thead">
					<th scope="col" id="cb" class="manage-column column-cb check-column" style=""><input type="checkbox" /></th>
					<th scope="col" id="field" class="manage-column column-field" style="width:20%;">Database Field</th>
					<th scope="col" id="name" class="manage-column column-name" style="width:20%;">Field Name</th>
					<th scope="col" id="markup" class="manage-column column-markup" style="">Mark-Up</th>
				</tr>
				</thead>
				<tfoot>
				<tr class="thead">
					<th scope="col" class="manage-column column-cb check-column" style=""><input type="checkbox" /></th>
					<th scope="col" id="field" class="manage-column column-field" style="">Database Field</th>
					<th scope="col" class="manage-column column-name" style="">Field Name</th>
					<th scope="col" class="manage-column column-markup" style="">Mark-Up</th>
				</tr>
				</tfoot>
				<tbody id="fields" class="list:fields field-list">
					<?php
						foreach($o['fields'] as $k => $v) {
							$d = empty($d) ? ' class="alternate"' : '';
					?>
							<tr id='field-<?=$v['name'];?>'<?=$d;?>>
								<th scope='row' class='check-column'><input type='checkbox' name='fields[]' id='field_<?=$v['name'];?>' value='<?=$v['name'];?>' /></th>
								<td class="field column-field">
									<input type="hidden" name="field_names%5B%5D" value="<?=$v['name'];?>" />
									<strong><?=$v['name'];?></strong><br />
									<div class="row-actions">
										<span class='edit tern_memebrs_edit'><a href="javascript:tern_members_editField('field-<?=$v['name'];?>');">Edit</a> | </span>
										<span class='edit'><a href="admin.php?page=Configure%20Mark-Up&fields%5B%5D=<?=$v['name'];?>&action=remove&_wpnonce=<?=wp_create_nonce('tern_wp_members_nonce');?>">Remove</a></span>
									</div>
								</td>
								<td class="name column-name">
									<input type="text" name="field_titles%5B%5D" class="tern_members_fields hidden" value="<?=$k;?>" /><br class="tern_members_fields hidden" />
									<input type="button" value="Update Field" onclick="tern_members_renderField('field-<?=$v['name'];?>');return false;" class="tern_members_fields hidden button" />
									<span class="tern_members_fields field_titles"><?=$k;?></span>
								</td>
								<td class="markup column-markup">
									<textarea name="field_markups%5B%5D" class="tern_members_fields hidden" rows="4" cols="10"><?=$v['markup'];?></textarea><br class="tern_members_fields hidden" />
									<input type="button" value="Update Field" onclick="tern_members_renderField('field-<?=$v['name'];?>');return false;" class="tern_members_fields hidden button" />
									<span class="tern_members_fields field_markups"><?php echo htmlentities($v['markup']); ?></span>
								</td>
							</tr>
					<?php
						}
					?>
				</tbody>
			</table>
			<input type="hidden" name="action" value="update" />
			<input type="hidden" id="page" name="page" value="Configure Mark-Up" />
			<input type="hidden" id="_wpnonce" name="_wpnonce" value="<?=wp_create_nonce('tern_wp_members_nonce');?>" />
			<input type="hidden" name="_wp_http_referer" value="<?php wp_get_referer(); ?>" />
		</form>
		<h3>Your Mark-Up will look like this:</h3>
		<?php
			$m = new tern_members();
			echo '<pre id="tern_members_sample_markup">'.htmlentities($m->markup($current_user)).'</pre>';
		?>
	</div>
<?php
}
//                                *******************************                                 //
//________________________________** MEMBERS LIST              **_________________________________//
//////////////////////////////////**                           **///////////////////////////////////
//                                **                           **                                 //
//                                *******************************                                 //
function tern_wp_members_list() {
	global $wp_roles,$getWP,$tern_wp_msg,$tern_wp_members_defaults,$current_user;
	get_currentuserinfo();
	//save settings
	$o = $getWP->getOption('tern_wp_members',$tern_wp_members_defaults);
	$a = empty($_REQUEST['action']) ? $_REQUEST['action2'] : $_REQUEST['action'];
	if(wp_verify_nonce($_REQUEST['_wpnonce'],'tern_wp_members_nonce') and !empty($a)) {
		$r = array();
		$o['hidden'] = is_array($o['hidden']) ? $o['hidden'] : array();
		foreach($_REQUEST['users'] as $v) {
			if($a == 'show' and in_array($v,$o['hidden'])) {
				array_splice($o['hidden'],array_search($v,$o['hidden']),1);
			}
			elseif($a == 'hide' and !in_array($v,$o['hidden'])) {
				$o['hidden'][] = $v;
			}
		}
		$o = $getWP->getOption('tern_wp_members',$o,true);
		$tern_wp_msg = empty($tern_wp_msg) ? 'You have successfully updated your settings.' : $tern_wp_msg;
	}
	//compile list
	$l = new tern_members();
	$_GET['order'] = 'desc';
	$m = $l->query();
?>
	<div class="wrap">
		<div id="icon-users" class="icon32"><br /></div>
		<h2>Members List</h2>
		<p>Here you are able to select which of your members you'd like to show or hide in your members list. By default all members are showm.</p>
		<?php
			if(!empty($tern_wp_msg)) {
				echo '<div id="message" class="updated fade"><p>'.$tern_wp_msg.'</p></div>';
			}
		?>
		<div class="filter">
			<form id="list-filter" action="" method="get">
				<ul class="subsubsub">
					<?php
						$l = array();
						$a = array();
						$u = get_users_of_blog();
						$t = count($u);
						foreach((array) $u as $c) {
							$d = unserialize($c->meta_value);
							foreach((array) $d as $e => $v) {
								if ( !isset($a[$e]) )
									$a[$e] = 0;
								$a[$e]++;
							}
						}
						unset($u);
						$current_role = false;
						$class = empty($role) ? ' class="current"' : '';
						$l[] = "<li><a href='admin.php?page=Edit%20Members%20List'$class>".sprintf(__ngettext('All<span class="count">(%s)</span>','All <span class="count">(%s)</span>',$t),number_format_i18n($t)).'</a>';
						foreach($wp_roles->get_names() as $s => $name) {
							if (!isset($a[$s]))
								continue;
							$class = '';
							if ($s == $role) {
								$current_role = $role;
								$class = ' class="current"';
							}
							$name = translate_with_context($name);
							$name = sprintf( _c('%1$s <span class="count">(%2$s)</span>|user role with count'),$name,$a[$s]);
							$l[] = "<li><a href='admin.php?page=Edit%20Members%20List&role=$s'$class>$name</a>";
						}
						echo implode( " |</li>\n", $l) . '</li>';
						unset($l);
					?>
				</ul>
			</form>
		</div>
		<form class="search-form" action="" method="get">
			<p class="search-box">
				<label class="hidden" for="user-search-input">Search Users:</label>
				<input type="text" class="search-input" id="user-search-input" name="query" value="" />
				<input type="hidden" id="page" name="page" value="Edit Members List" />
				<input type="submit" value="Search Users" class="button" />
			</p>
		</form>
		<form id="posts-filter" action="" method="get">
			<div class="tablenav">
				<div class="alignleft actions">
					<select name="action">
						<option value="" selected="selected">Bulk Actions</option>
						<option value="show">Show</option>
						<option value="hide">Hide</option>
					</select>
					<input type="submit" value="Apply" name="doaction" id="doaction" class="button-secondary action" />
				</div>
				<br class="clear" />
			</div>
			<table class="widefat fixed" cellspacing="0">
				<thead>
				<tr class="thead">
					<th scope="col" id="cb" class="manage-column column-cb check-column" style=""><input type="checkbox" /></th>
					<th scope="col" id="username" class="manage-column column-username" style="">Username</th>
					<th scope="col" id="name" class="manage-column column-name" style="">Name</th>
					<th scope="col" id="email" class="manage-column column-email" style="">E-mail</th>
					<th scope="col" id="role" class="manage-column column-role" style="">Role</th>
					<th scope="col" id="displayed" class="manage-column column-displayed" style="">Displayed</th>
				</tr>
				</thead>
				<tfoot>
				<tr class="thead">
					<th scope="col"  class="manage-column column-cb check-column" style=""><input type="checkbox" /></th>
					<th scope="col"  class="manage-column column-username" style="">Username</th>
					<th scope="col"  class="manage-column column-name" style="">Name</th>
					<th scope="col"  class="manage-column column-email" style="">E-mail</th>
					<th scope="col"  class="manage-column column-role" style="">Role</th>
					<th scope="col" id="displayed" class="manage-column column-displayed" style="">Displayed</th>
				</tr>
				</tfoot>
				<tbody id="users" class="list:user user-list">
<?php
	//
	$c = 0;
	foreach($m as $u) {
		$u = new WP_User($u->ID);
		$r = $u->roles;
		$r = array_shift($r);
		if(!empty($_REQUEST['role']) and $_REQUEST['role'] != $r) {
			continue;
		}
		$d = is_float($c/2) ? '' : ' class="alternate"';
		$nu = $current_user;
		$e = $u->ID == $nu->ID ? 'profile.php' : 'user-edit.php?user_id='.$u->ID.'&#038;wp_http_referer='.wp_get_referer();
?>
		<tr id='user-<?=$u->ID;?>'<?=$d;?>>
			<th scope='row' class='check-column'><input type='checkbox' name='users[]' id='user_<?=$u->ID;?>' class='administrator' value='<?=$u->ID;?>' /></th>
			<td class="username column-username">
				<?=get_avatar($u->ID,32);?>
				<strong>
					<a href="<?=$e;?>"><?=$u->user_nicename;?></a>
				</strong><br />
				<div class="row-actions">
					<span class='edit'><a href="admin.php?page=Edit%20Members%20List&users%5B%5D=<?=$u->ID;?>&action=show&_wpnonce=<?=wp_create_nonce('tern_wp_members_nonce');?>">Show</a> | </span>
					<span class='edit'><a href="admin.php?page=Edit%20Members%20List&users%5B%5D=<?=$u->ID;?>&action=hide&_wpnonce=<?=wp_create_nonce('tern_wp_members_nonce');?>">Hide</a></span>
				</div>
			</td>
			<td class="name column-name"><?=$u->first_name.' '.$u->last_name;?></td>
			<td class="email column-email"><a href='mailto:<?=$u->user_email;?>' title='e-mail: <?=$u->user_email;?>'><?=$u->user_email;?></a></td>
			<td class="role column-role"><?=$r;?></td>
			<td class="role column-displayed"><?php if(!empty($o['hidden']) and in_array($u->ID,$o['hidden'])) { echo 'no'; } else { echo 'yes'; } ?></td>
		</tr>
<?php
		$c++;
	}
?>
				</tbody>
			</table>
			<div class="tablenav">
				<div class="alignleft actions">
					<select name="action2">
						<option value="" selected="selected">Bulk Actions</option>
						<option value="show">Show</option>
						<option value="hide">Hide</option>
					</select>
					<input type="hidden" id="page" name="page" value="Edit Members List" />
					<input type="hidden" id="_wpnonce" name="_wpnonce" value="<?=wp_create_nonce('tern_wp_members_nonce');?>" />
					<input type="hidden" name="_wp_http_referer" value="<?php wp_get_referer(); ?>" />
					<input type="submit" value="Apply" name="doaction2" id="doaction2" class="button-secondary action" />
				</div>
				<br class="clear" />
			</div>
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
		global $getFIX,$getWP,$tern_wp_members_defaults;
		$this->wp = $getWP;
		$o = $this->wp->getOption('tern_wp_members',$tern_wp_members_defaults);
		if(!empty($o)) {
			$this->num = $o['limit'];
			$this->meta_fields = explode(',',$o['meta']);
			$this->meta_fields = $getFIX->removeEmptyValues($this->meta_fields);
			$a = array();
			foreach($this->meta_fields as $k => $v) {
				$a[$v] = $v;
			}
			$this->meta_fields = $a;
		}
		$this->url = strpos($o['url'],'?') !== false ? $o['url'] : $o['url'].'?';
	}
	function members($a) {
		global $tern_wp_members_defaults;
		$this->scope();
		$this->query();
		$o = $this->wp->getOption('tern_wp_members',$tern_wp_members_defaults);
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
				echo $this->markup($u);
				/*
				echo '<li>';
				if($o['gravatars']) {
					echo '<a class="tern_wp_member_gravatar" href="'.get_bloginfo('url').'/?author='.$u->ID.'">'.get_avatar($u->ID,60).'</a>';
				}
				echo		
						'<div class="tern_wp_member_info">
							<h3 id="tern_wp_member_'.$u->ID.'">
								<a href="'.get_bloginfo('url').'/?author='.$u->ID.'">'.$n.'</a>
							</h3>
							<a href="mailto:'.$u->user_email.'">'.$u->user_email.'</a>
							<a href="'.$u->user_url.'">'.$u->user_url.'</a>
						</div>
					</li>';
				*/
			}
		}
		echo '</ul>';
	}
	function markup($u) {
		global $tern_wp_members_defaults;
		$o = $this->wp->getOption('tern_wp_members',$tern_wp_members_defaults);
		$s = '<li>'."\n    ";
		if($o['gravatars']) {
			$s .= '<a class="tern_wp_member_gravatar" href="'.get_bloginfo('url').'/?author='.$u->ID.'">'."\n        ".get_avatar($u->ID,60)."\n    ".'</a>'."\n    ";
		}
		$s .= '<div class="tern_wp_member_info">';
		foreach($o['fields'] as $k => $v) {
			$s .= "\n        ".str_replace('%author_url%',get_bloginfo('url').'/?author='.$u->ID,str_replace('%value%',$u->$v['name'],$v['markup']));
		}
		return $s."\n    ".'</div>'."\n".'</li>';
	}
	function query() {
		global $wpdb,$tern_wp_members_defaults;
		$o = $this->wp->getOption('tern_wp_members',$tern_wp_members_defaults);
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
			if(!empty($v) and (empty($o['hidden']) or ((!is_admin() and !in_array($v,$o['hidden'])) or is_admin()))) {
				$this->a[] = new WP_User($v);
			}
		}
		$s = empty($_GET['sort']) ? $o['sort'] : $_GET['sort'];
		$o = empty($_GET['order']) ? $o['order'] : $_GET['order'];
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
			//
			$sort = empty($_GET['sort']) ? $o['sort'] : $_GET['sort'];
			$order = empty($_GET['order']) ? $o['order'] : $_GET['order'];
			//
			for($i=$s;$i<=$e;$i++) {
				$h = $this->url.'&page='.($i).'&query='.$q.'&by='.$b.'&type='.$t.'&sort='.$sort.'&order='.$order;
				$c = intval($this->s+1) == $i ? ' class="tern_members_pagination_current"' : '';
				$r .= '<li'.$c.'><a href="' . $h . '">' . $i . '</a></li>';
			}
			if($this->s > 0) {
				$r = '<li><a href="'.$this->url.'&page='.intval($this->s).'&query='.$q.'&by='.$b.'&type='.$t.'&sort='.$sort.'&order='.$order.'">Previous</a></li>'.$r;
			}
			if(count($this->a) > (($this->s*$this->num)+$this->num)) {
				$r .= '<li><a href="'.$this->url.'&page='.intval($this->s+2).'&query='.$q.'&by='.$b.'&type='.$t.'&sort='.$sort.'&order='.$order.'">Next</a></li>';
				$r .= '<li><a href="'.$this->url.'&page='.$e.'&query='.$q.'&by='.$b.'&type='.$t.'&sort='.$sort.'&order='.$order.'">Last</a></li>';
			}
			//
			$r = $this->s > 0 ? '<li><a href="'.$this->url.'&page=1&query='.$q.'&by='.$b.'&type='.$t.'&sort='.$sort.'&order='.$order.'">First</a></li>'.$r : $r;
			//
			$r = '<ul class="tern_members_pagination tern_wp_pagination">' . $r . '</ul>';
		}
		//
		$m = '.';
		if($t == 'alpha') {
			$m = ' whose last names begin with the letter "'.strtoupper($q).'".';
		}
		$v = count($this->a) > 0 ? (($this->s*$this->num)+1) : '0';
		echo '<div id="tern_members_pagination"><p>Now viewing <b>' . $v . '</b> through <b>' . $this->e . '</b> of <b>'.count($this->a).'</b> members found'.$m.'</p>' . $r.'</div>';
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
			<input type="hidden" name="p" value="'.$_REQUEST['p'].'" />
			<input type="hidden" name="page_id" value="'.$_REQUEST['page_id'].'" />
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
		echo '</ul><br /><span>(by last name)</span></div>';
	}
	function sortby() {
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