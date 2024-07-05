=== RT Filter Page List ===
Contributors: roytanck
Donate link: http://www.roytanck.com/
Tags: pages, widget, filter, folding, accordion
Requires at least: 3.5.2
Tested up to: 6.6
Stable tag: 1.0
License: GPLv3

Hooks into WordPress core function wp_list_pages, and removes pages from the generated list that are not part of the current navigation path.

== Description ==

Hooks into WordPress core function `wp_list_pages`, and removes pages from the generated list that are not part of the
current navigation path. This creates an accordion navigation effect, and is an alternative approach to the CSS-based
way suggested in the [Codex](http://codex.wordpress.org/Function_Reference/wp_list_pages#Markup_and_styling_of_page_items).

By removing unneeded HTML elements server-side, page size is reduced, and client-side rendering will usually be faster.
This is especially true for older browsers, and sites with a large number of published pages.

This plugin is intended to be used with the "pages" widget that comes with WordPress, or any navigation element that uses
wp_list_pages.

There are two ways the plugin can apply the filter.

1. By modifying the default Pages widget that comes with WordPress
1. By applying the filter everywhere wp_list_pages is used

Please note that since the second option uses the wp_list_pages hook, the filtering will take place anywhere
wp_list_pages is used. This includes menu locations to which no custom menu is assigned. Please make sure all your
menu locations have a custom menu assigned to them to avoid unexpected behavior.

This plugin requires the PHP DOMDocument extension, and PHP5.

== Installation ==

1. Upload the folder `rt-filter-page-list` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Visit the new Settings / RT Filter Page List settings page to configure filtering

== Frequently Asked Questions ==

= Which type of filtering should I choose =

If you plan on using the Pages widget that comes with WordPress, it's probably best to filter just the output of that
widget by selecting 'Modify the behavior of the Pages widget'.

If you're building your own navigation based on wp_list_pages, select 'Modify wp_list_pages globally'. This will filter
the page list anywhere wp_list_pages is called. This could lead to unexpected behavior with menus and other widgets. If
this happens on your site, you can either opt to use the Pages widget, select 'None'.

Selecting 'None' disables the plugin's automatic filters, but still allows you to call the
RT_Filter_Page_List::filter_html function directly to apply filtering in your code.

`
$html = wp_list_pages( $args );
$html = RT_Filter_Page_List::filter_html( $html );
`

= Why would I use this instead of hiding pages through CSS? =

This is meant for cases where the pages menu contains a large number of pages, or where the target browser is slow
(e.g. IE7/8). Or both. This plugin can reduce the pages listing returned by WordPress by up to 99%.

= Will this make my site slower? =

Since the filtering is done server-side, your site's pages might take slightly longer to generate. However, they will
probably also be smaller (in terms of file size), and thus take less time to download. On sites where pages are cached,
the effect should be negligable.

= It's not clear which page is currently selected =

This plugin does not include CSS styles to highlight the currently selected page, its ancestors, or it children.
Ideally, your theme should take care of this.

== Screenshots ==

1. The plugin's settings page lets you select the filtering type
2. Child pages of non-ancestor pages are filtered from the page list

== Changelog ==

= 1.0 (2020-08-19) =
* Version bump to 1.0
* Tested succesfully with WordPress 5.5
* Development moved to GitHub
* Translations now done through wp-org

= 0.9.4 =
* Updated Dutch translation
* Fixed a typo in the copyright notice email address

= 0.9.3 =
* Added support for WordPress 4.3's more accessible heading structure

= 0.9.2 =
* Added Spanish translation
* Tested against WordPress 4.0

= 0.9.1 =
* Plugin now defaults to Pages widget filtering if not explicitly configured

= 0.9 =
* Added an options page to choose the filtering method
* Added screenshots and banner image for the wordpress.org repo
* Added i18n support
* Edited readme to reflect changes

= 0.8 =
* Initial release, based on 'proven technology' from a client project.
