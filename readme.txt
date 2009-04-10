=== Members List Plugin ===
Contributors: mpraetzel
Donate link: http://www.ternstyle.us/donate
Tags: members, members list
Requires at least: 2.7
Tested up to: 2.7.1
Stable tag: 1.5

The Members Plugin allows you to create a post on your wordpress blog that lists all your wordpress members.

== Description ==

The Members Plugin allows you to create a post on your wordpress blog that lists all your wordpress members. When viewing the list of members you can also search through your members according to first name, last name, email address, URL or any other number of user meta fields you may specify. Employing pagination you can page through your search results and sort your results according to last name, first name, registration date or email.

== Installation ==

* Upload `tern_wp_members` to the `/wp-content/plugins/` directory
* Activate the plugin through the 'Plugins' menu in WordPress
* Place `<?php $members = new tern_members;$members->members(array('search'=>true,'pagination'=>true,'sort'=>true));?>` in your templates

== Features ==

* List your members in a wordpress post
* Search through your members using user standard and meta fields
* Search alphabetically by last name
* Pagination to page through members list and search results
* Sort by last name, first name, registration date or email

== Frequently Asked Question ==

== Screenshots ==