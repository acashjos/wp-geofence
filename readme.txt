=== WP Geofence ===
Contributors: acash
Tags: geofence,geolocation,country,post,hide
Requires at least: 3.0
Tested up to: 3.9.1
Stable tag: 1.0
License: GPLv2 or later
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=""I DONT HAVE A DONATE LINK YET""
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A WordPress plugin to restrict contents selectively based on the geographical location of visitors Times may come when you have to publish some posts specifically to the audiance from a particular country 

== Description ==

This plugin allows you to white-list the countries to which a specific post should be served. You can add multiple countries to white list.Each post will have individual white-lists.
In addition to that, the plugin impliments a shortcode with which you can show a small part of the post localized. The content within the shortcode will be visible only to people from the specified country.

It uses [MaxMind's GeoIP data file](http://www.maxmind.com/app/geolitecountry), for the purpose of geolocating;
`This product includes GeoLite data created by MaxMind, available from [http://www.maxmind.com](http://www.maxmind.com).`


GitHub Repository is [here](https://github.com/acashjos/wp-geofence). 

== Installation ==
Install and activate the plugin
= Metabox =

    *	Enter 3 letter Country code
    *	Press ';' to add the country to Whitelist. Don't press Enter/Return
    *	When you press ';' it will be saved only if its a valid code
    *	Type full country name to see hints on country code
    *		Eg: type USA then press ;
    *		You may type down "united sta"... to see the country code for USA
    Edit/Deletel
    *	Countries in white list is visible above the text field
    *	Double click to open it for editing. press ';' after editing
    *	If its not saved back using ';' , it will be deleted

= Short Code =
* use the short code [local code="country_code"]
* multiple shortcode sections can be added in a post
eg: [local code='IND'] .. [/local]

== Changelog ==

= 1.0 =
* Original release

== Frequently Asked Questions ==

= What is this =

A plugin to geofence your posts

== Screenshots ==



1. Add countries to white-list using metabox
2. Use shortcode to restrict only a part of the post.
