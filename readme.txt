=== Plugin Name ===
Contributors: steppek, geniodiabolico
Donate link: http://example.com/
Tags: comments, spam
Requires at least: 3.0.1
Tested up to: 4.8.0
Stable tag: 1.5.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin will import Productions from the Auphonic website and import them into WordPress as a podcast with an enclosure.

== Description ==

This plugin for WordPress will read your productions on using the Auphonic service (via API) and pull the last 10 productions 
into WordPress automatically. It will create a post with enclosure, with the category you choose, and you have the option 
to put them in Published, or Draft mode to allow edits and add pictures, etc…and then publish.

* Title of the production from Auphonic will be the Title of your post in WordPress.
* The Summary (Description) of the Production in Auphonic will be the body of your WordPress post.
* Any Tags you put in your Auphonic preset or production will come over to WordPress as Tags.
* This plugin only recognizes Productions from Auphonic that produce an url with a file type of mp3, mp4, and m4a.
* If you are using Dropbox as a service in Auphonic to place your file for example, you may not get an url directly to your file and it therefore will not be imported.

== Installation ==

1. If you have not already, create an account on [Auphonic's Website](http://auphonic.com/)
1. To install, copy the plugin into your WordPress plugins folder. From the Admin->Plugins interface Activate it.
1. Go to the Auphonic Importer options page under Settings and Click the “Save Changes” button to finish the install.
1. Return to the Auphonic Importer options page under Settings and fill out the required fields. The Tag will be the corresponding Tag that you set in your Auphonic preset (See screen-shot below). The problem I was trying to solve was if you produce several podcasts through Auphonic the Tag would the filter for the productions intended for only this blog. In the example below ‘Podcast’ is used, change as needed.
1. To do an hourly cron just call http://yourblog.com/?ai_update=yes (substitute 'yes' with the update password you choose) and pipe it to null.

== Screenshots ==

1. Service Settings need a Base url in Auphonic
2. Add a Tag to your Preset settings in Auphonic
3. Settings in Auphonic Importer in WordPress

== Changelog ==

= 1.5.1 =
* Slight Cleanup

= 1.5 =
* Added support for no/empty tag lookup
* Fixed issue where plugin was not looking in cache for previous entries

= 1.4 =
* Minor bug fix

= 1.2 =
* Added ability to change the password used to import Productions from the URL

= 1.0 =
* Initial Release
