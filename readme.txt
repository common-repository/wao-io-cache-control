=== wao.io Cache Control ===
Contributors: waoio
Tags: cache, caching, clear, control, pagespeed, performance, speed, velocity, wao.io, webperformance
Requires at least: 4.8
Tested up to: 5.5
Requires PHP: 5.6
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

wao.io Cache Control is a free plugin to clear your WordPress site's cache at [wao.io](https://wao.io/en/?utm_source=wordpressMarketplace&utm_medium=description&utm_campaign=CachePlugin).

=== Cache Control ===

Use this plugin to control your site's cache at wao.io.

Click on the **Invalidate Origin Content** button to clear your optimized WordPress website's cache at wao.io
to make sure that you and your customers will see the latest version of your content.

Cache invalidation will automatically be triggered after publishing or updating a post or a page, and after switching a theme.

Cache invalidation treats all content in the cache as if it was outdated.
This means that wao.io will check on the next request if the content changed on the origin server.
If so, it will be requested. Otherwise wao.io will continue to use the cached resource.
This is especially useful if you changed only a few images and the majority of content remained unmodified.

=== Settings ====

In the **wao.io Cache Control Settings** section of your general WordPress settings,
please enter your Site ID, and the API key that you obtained from wao.io's support team.

If you do not have an API key yet, please contact [wao.io support](https://wao.io/en/account/support?utm_source=wordpressMarketplace&utm_medium=description&utm_campaign=CachePlugin).

In future versions of this plugin, you can disable or enable automatic cache invalidation here.

=== Automatic Cache Invalidation ===

Automatic cache invalidation will make WordPress try to invalidate your
optimized WordPress website's cache at wao.io automatically each time your content has changed,
e.g. after publishing a page or uploading an image.

=== Known Issues ===

Some WordPress sites reported a bug that the publish / update post event did not fire when using the block editor (Gutenberg issue #17632).
This may affect the plugin wao.io Cache Control which depends on the event for automatic cache invalidation.

=== Development and Contribution ===

wao.io Cache Control plugin for WordPress was developed for wao.io by Ingo Steinke, Senior Software Developer at Avenga Germany GmbH.

If you want to contribute to this plugin, please contact wao.io product development via our [website wao.io](https://wao.io/en/?utm_source=wordpressMarketplace&utm_medium=description&utm_campaign=CachePlugin).

== Installation ==

Install the plugin and activate it.

In the **wao.io Cache Control Settings** section of your general WordPress settings,
please enter your Site ID, and the API key you obtained from wao.io's support team.


Visit [wao.io](https://wao.io/en/?utm_source=wordpressMarketplace&utm_medium=description&utm_campaign=CachePlugin) for more information.

== Frequently Asked Questions ==

= Where can I get support? =

Visit our support center at [wao.io/en/account/support](https://wao.io/en/account/support?utm_source=wordpressMarketplace&utm_medium=description&utm_campaign=CachePlugin), or browse our [frequently asked questions](https://wao.io/en/faq?utm_source=wordpressMarketplace&utm_medium=description&utm_campaign=CachePlugin).

== Screenshots ==

1. This screen shows the main page, **Cache Control**

2. This screen shows the main page after successfully triggering a cache invalidation.

3. This screen shows the **Settings** section


== Changelog ==

= 1.0.0 =
* First release.

== Upgrade Notice ==

= 1.0.0 =
* First release.
