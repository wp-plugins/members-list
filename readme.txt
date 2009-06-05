=== Members List Plugin ===
Contributors: mpraetzel
Donate link: http://www.ternstyle.us/donate
Tags: members, members list, users, users list
Requires at least: 2.7
Tested up to: 2.7.1
Stable tag: 2.1.1

The Members Plugin allows you to create a post on your wordpress blog that lists all your wordpress members.

== Description ==

The Members Plugin allows you to create a post on your wordpress blog that lists all your wordpress members. When viewing the list of members you can also search through your members according to first name, last name, email address, URL or any other number of user meta fields you may specify. Employing pagination you can page through your search results and sort your results according to last name, first name, registration date or email.

== Installation ==

* Upload the `members-list` folder to the `/wp-content/plugins/` directory
* Activate the plugin through the 'Plugins' menu in WordPress
* Place `<?php $members = new tern_members;$members->members(array('search'=>true,'pagination'=>true,'sort'=>true));?>` in your templates
* Don't forget to alter your members list settings in your Wordpress 'Settings' menu.

== Features ==

* List your members in a wordpress page
* Hide selected members from members list
* Search through your members using user standard and meta fields
* Search alphabetically by last name
* Pagination to page through members list and search results
* Sort by last name, first name, registration date or email

== Resources ==

* Homepage for this plugin: `http://www.ternstyle.us/products/plugins/wordpress/wordpress-members-plugin`
* Documentation: `http://wiki.ternstyle.us/index.php/Wordpress_Members_Plugin_Documentation`
* Working example: `http://blog.ternstyle.us/members`

== Frequently Asked Questions ==

== Screenshots ==

1. This is an image of a the working example found at: `http://blog.ternstyle.us/members`
2. This is an image of the administrative settings page for this plugin.
3. This screenshot is of the new Members List Editing features.