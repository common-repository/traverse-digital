=== Traverse Digital ===
Contributors: jackreichert
Donate link: http://www.jackreichert.com/buy-me-a-beer/
Tags: foursquare, maps, images, location, geo-location data, gallery, Google Maps
Requires at least: 4.3
Tested up to: 4.4
Stable tag: trunk
License: GPLv3

Create maps using geo-location data for your image posts displaying where your pictures are taken. Take control over how you share your location.

== Description ==

This plugin lets you easily display the locations of photos you've taken. I developed it as part of a proof-of-concept for leveraging the WordPress app to create a Foursquare/Swarm checking functionality for your blog.

This will create a custom post type called "traverse" (optional) or will apply the functionality to posts. It checks posts for images or a gallery and extracts the EXIF geo-location data from the image. It then set's up maps on the pages on which it found geo-data with markers for each of the images.

This is a great companion with my If Post Then That WordPress plugin that lets you trigger WordPress actions via tags in the post which makes the iOS and Android apps that much more powerful. This allows you to "check in" easily from the road.

== Installation ==

1. Upload plugin to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Get your API key for the [Google Maps Javascript API](https://developers.google.com/maps/documentation/javascript/tutorial)
4. Add it to the settings page under Settings > Traverse Digital

== Frequently Asked Questions ==

= Why would I want a different post type? =

If you don't want to clutter up your blogroll with every location you post, you might want to do this. It's more segregated than categories.

= Why aren't the posts showing up? =

Visit your permalinks settings page, it's possible that the rewrite cache just needs to be cleared.


== Screenshots ==

1. This is what you could add to your site. Cool, right?!

== Changelog ==

= 0.1 =
* Initial commit.

== Upgrade Notice ==

= 0.1 =
Upgrade your photoblogging. Checking everywhere! Initial commit.