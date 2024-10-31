=== PW_Archives ===
Contributors: philipwalton
Tags: archives, archive, sitemap, site, map, sidebar, nav, posts, widget, shortcode
Requires at least: 3.0
Tested up to: 3.3
Stable tag: 2.0.4

A fully-customizable yet light-weight and intuitive archiving plugin. Its features include custom post type support, optional javascript enhancement, shortcodes, and widgets all with only one additional database query.

== Description ==

The primary goal of PW_Archives is to be light-weight and efficient. I've tried almost all of the popular archiving plugins out there, and so many of them make hundreds or even thousands of queries depending on the number of posts you have on your site. PW_Archives makes only one query to get your posts. That's it. Not only that but it doesn't do endless amounts of looping and counting to determine post and comment counts. That's all included in the one query it does, and it's very efficient.

The second main goal of PW_Archives is customization. Almost everything is customizable. Select what post types to include, choose whether you want the javascript to fade in or slide down (or no javascript at all). You can even select the date format and add custom markup. If you don't know HTML, don't worry; the defaults will work for most people.

For complete documentation, visit [philipwalton.com](http://philipwalton.com/2011/02/08/pw_archives/)

== Installation ==

PW_Archives settings works just like WordPress menus. There can be multiple PW_Archives instances with their own unique settings. Each instance is identified by a name that can be referenced in widgets or shortcodes.

For example: let's say you want to list archives in your sidebar only showing the most recent year, and you also want archives listed on a dedicated archives page showing all years, all months, and all posts. Here's how you'd do it:

1) Just create two instances, name one "Sidebar" and the other something like "Full" and set the options for each.
2) Next, add the PW_Archives widget to your sidebar and select the "Sidebar" instance in the widget form.
3) Finally, create a new page, call it "Archives" and add the shortcode [PW_Archives name="Full"] in the content.

For complete documentation, visit [philipwalton.com](http://philipwalton.com/2011/02/08/pw_archives/)


== Changelog ==

= 2.0.4 =
* Updated to support WordPress 3.3
* Fixed a minor bug where a JS files was being properly dequeued

= 2.0.3 =
* Fixed a bug where some javascript files weren't being loaded on public pages

= 2.0.2 =
* Fixed a bug where PW_MultiModelController wasn't properly deleting a model instance

= 2.0.1 =
* Fixed errors from the svn upload process

= 2.0 =
* PW_Archives options are now controlled using a system based off of WordPress's menus. This allows users to create multiple instances of specific settings and easily reference them in widgets and shortcodes.
* Added custom post type support
* Added Javascript enhancement

= 1.0.4.2 =
* Updated the installation instruction to fix some confusion that existed.
* Changed the PW_Form class to pwaForm to avoid a naming conflict

= 1.0.4.1 =
* Decided changing the defaults wasn't a good idea because it might break existing sites that are hand coding their options and relying on defaults. The default is once again to show posts.

= 1.0.4 =
* Switch from date() to date_i18n() to help add support for other localizations.
* Change the default options to not show posts
* Updated the uninstall.php file to removed options from all blogs (if in multisite), not just the primary blog.

= 1.0.3 = 
* Added an uninstall.php file to remove options from the database when a user uninstalls the plugin. Previously these options were removed when a user deactivated the plugin, which caused problems when users would update WordPress because (as part of the upgrade process) it would deactivate and then reactivate all plugins and thus erase the options stored in the database.