=== ConoHa Object Sync ===
Contributors: hironobu
Tags: ConoHa, OpenStack Swift, object storage
Requires at least: 0.1
Tested up to: 0.1
Stable tag: 0.1
License: GPLv2 or later.
License URI: http://www.gnu.org/licenses/gpl-2.0.html

ConoHa Object Sync is a simple plugin for WordPress that helps you to synchronizes media files with ConoHa Object Storage or other OpenStack Swift-based system.

== Description ==

[ConoHa](https://www.conoha.jp/en/) is Cloud like VPS service and ConoHa Object Storage is the OpenStack Swift-based Object Store service.

This WordPress plugin allows you to upload media files from the library to ConoHa Object Storage or other OpenStack Swift-based Object Store.

These files then load from the Object Storage and optimize the your site/blog performance.

This plugin may be available in other OpenStack Swift-based system.

= Features =

* Synchronization media files with the Object Storage.
* Automatically rewrite the media url to the endpoint url.

= For Japanese users. =

WordPressのメディアファイル(画像など)をConoHaオブジェクトストレージで扱うためのWordPressプラグインです。

WordPressの管理画面からメディアを追加すると、自動的にオブジェクトストレージにアップロードを行います。オブジェクトストレージは容量無制限なため、空き容量を気にすること無くメディアファイルを扱うことができます。

また、このプラグインはメディアファイルのURLを変更し、オブジェクトストレージから直接配信するように設定します。これにより、WordPressを運用しているサーバに負荷をかけずに、メディアファイルを配信することができます。

ConoHaオブジェクトストレージ以外でも、OpenStack Swiftがベースのシステムであれば動作すると思います(未検証)。


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
