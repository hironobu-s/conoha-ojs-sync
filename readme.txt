=== ConoHa Object Sync ===
Contributors: hironobu
Tags: ConoHa, OpenStack Swift, object storage
Requires at least: 0.1
Tested up to: 0.1
Stable tag: 0.1
License: GPLv2 or later.
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WordPress plugin for ConoHa Object Storage.

== Description ==

This WordPress plugin allows you to upload files from the library to [ConoHa Object Storage](https://www.conoha.jp/en/) or OpenStack Swift-based Object Store. 


== Installation ==


1. Run the following command.
2. Activate the plugin through the 'Plugins' menu in WordPress

* cd [WORDPRESS_ROOT]/wp-content/plugins
* git clone https://github.com/hironobu-s/conoha-ojs-sync
* cd conoha-ojs-sync
* curl -sS https://getcomposer.org/installer | php
* ./composer.phar install

== Frequently Asked Questions ==

= Can I use this plugin on other OpenStack Swift system instead of ConoHa? =

It will probably use. I don't test.

== Upgrade Notice ==

No upgrade, so far.

== Screenshots ==

1. Edit settings through the 'Settings' menu as you like.

== Changelog ==


= 0.1 =
* First release.
