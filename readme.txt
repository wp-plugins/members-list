=== Members List Plugin ===
Contributors: mpraetzel
Donate link: http://www.ternstyle.us/donate
Tags: members, members list, users, users list
Requires at least: 2.7
Tested up to: 3.0
Stable tag: 3.2

The Members Plugin allows you to create a post on your wordpress blog that lists all your wordpress members.

== Description ==

The Members Plugin allows you to create a post on your wordpress blog that lists all your wordpress members. When viewing the list of members you can also search through your members according to first name, last name, email address, URL or any other number of user meta fields you may specify. Employing pagination you can page through your search results and sort your results according to last name, first name, registration date or email. Documentation: `http://wiki.ternstyle.us/index.php/Wordpress_Members_Plugin_Documentation`

NOTE: A FRESH INSTALLATION OF THE PLUGIN IS REQUIRED TO USE VERSION 3.2!!!!!

* Homepage for this plugin: `http://www.ternstyle.us/products/plugins/wordpress/wordpress-members-list-plugin`
* Documentation: `http://www.ternstyle.us/products/plugins/wordpress/wordpress-members-list-plugin/wordpress-members-list-plugin-documentation`
* Working example: `http://www.ternstyle.us/products/plugins/wordpress/wordpress-members-list-plugin/members-list-plugin-demo`
* Change Log: `http://www.ternstyle.us/products/plugins/wordpress/wordpress-members-list-plugin/wordpress-members-list-plugin-change-log`

== Installation ==

* Upload the `members-list` folder to the `/wp-content/plugins/` directory
* Activate the plugin through the 'Plugins' menu in WordPress
* Navigate to your Wordpress blog's theme folder which should be found in /wp-content/themes/the-name-of-your-theme
* You'll need to create a new template file entitled "members.php"
** To do this copy your file entitled single.php and name it members.php.
** Place this code `<?php
/*
Template Name: Members
*/
?>` on the first line of the file.
** Remove the code that prints the single post to the page and replace it with this code: `<?php $members = new tern_members;$members->members(array('search'=>true,'pagination'=>true,'sort'=>true));?>`
** Upload the new file to the server.
* Now you'll need to create a new page which you can title whatever you like.
* Assign this page to the template entitled "Members"
* Remember to alter your Members List settings to reflect the new name of this page.
* That should be it. View the page and you should see the Members List in its moderately useful glory!

== Features ==

* List your members in a wordpress page
* Hide selected members from members list
* Search through your members using user standard and meta fields
* Search alphabetically by last name
* Pagination to page through members list and search results
* Sort by last name, first name, registration date or email

== Resources ==

* Homepage for this plugin: `http://www.ternstyle.us/products/plugins/wordpress/wordpress-members-list-plugin`
* Documentation: `http://www.ternstyle.us/products/plugins/wordpress/wordpress-members-list-plugin/wordpress-members-list-plugin-documentation`
* Working example: `http://www.ternstyle.us/products/plugins/wordpress/wordpress-members-list-plugin/members-list-plugin-demo`
* Change Log: `http://www.ternstyle.us/products/plugins/wordpress/wordpress-members-list-plugin/wordpress-members-list-plugin-change-log`

== Frequently Asked Questions ==

= When I click on a members link it goes nowehere. Why? =

The Members List Plugin does not handle your templates. Wordpress allows you to create a template file called "author.php" which should be placed in your theme's folder. It is this file that handles the displaying of each of your members. You can read about how to create this file here: `http://codex.wordpress.org/Author_Templates`


== Screenshots ==

1. This is an image of a the working example found at: `http://blog.ternstyle.us/members`
2. This is an image of the administrative settings page for this plugin.
3. This screenshot is of the new Members List Editing features.