=== Post Type Archive Mapping ===
Contributors: ronalfy, chrislogan, paaljoachim
Tags: map pages, archives, post type block, 404 page, category grid
Requires at least: 5.5
Requires PHP: 5.6
Tested up to: 6.2
Stable tag: 5.1.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Donate link: https://github.com/MediaRon/post-type-archive-mapping

Map your post type and category archives to a page, and also map your 404 template to a page as well. Three helper blocks allow you to display a post grid, a term (category) grid, and posts by category.

== Description ==

A WordPress plugin for displaying posts and terms (e.g., categories) using a Gutenberg block. Works well with posts, pages, custom post types, taxonomies, and terms.

Bonus: archive mapping.

> Note: (2021-07-28) The blocks are deprecated as there are better custom query items and the new Query block in WordPress 5.8. Thank you for trying us out.

<ul>
	<li>Map your post type archives to a page for customization of the post type archive page.</li>
	<li>Map your category archives to a page for customization of the term archive page.</li>
	<li>Map your 404 template to a page and easily customize your 404 page.</li>
</ul>

<a href="https://mediaron.com/custom-query-blocks/">View Documentation and Overview</a>

The plugin currently has three blocks:

* <a href="https://mediaron.com/custom-query-blocks/custom-post-types-block/">Custom Post Types Block</a>
* <a href="https://mediaron.com/custom-query-blocks/term-category-grid-block/">Term (Category) Grid Block</a>
* <a href="https://mediaron.com/custom-query-blocks/featured-posts-by-category-block/">Featured Posts by Category Block</a>

=== Post Type Archive Mapping ===

This plugin allows you to map your custom post type archive pages. Just create a page and go to Settings->Reading to set the page for your archive.

Ensure your post types have <code>has_archive</code> set to true.

<ul>
<li>Select a Public page to use as your post type archive page.</li>
<li>View the archive and you will see the page content instead of the archive content.</li>
<li>Use page templates on your pages for flexibility.</li>
<li>Custom Gutenberg block for showing your posts.</li>
</ul>

=== Term Archive Mapping ===

This plugin also allows you to map your term archives to a page. Just create a page and go edit your term to set the archive page.

<ul>
<li>Create a public page to use as your term archive.</li>
<li>Visit the edit term page and select the page.</li>
<li>View the term and you will see your selected page.</li>
<li>Use Gutenberg on your public page to customize the archive.</li>
</ul>

=== 404 Page Mapping ===

This plugin allows you to map a page to your 404 template, so you can customize a 404 page as needed.

== Installation ==

1. Just unzip and upload the "post-type-archive-mapping" folder to your '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress

Post Type Archive Mapping

1. Create a custom post type that has an archive
2. Create a page with a custom template. Use the Gutenberg block to show off posts in that Custom Post Type.
3. Go to Settings->Reading and assign the page to the custom post type archive
4. Go to the post type archive and observe the page content is now the archive

Term Archive Mapping

1. Create a public page to use as your term archive.
2. Visit the edit term page and select the page.
3. View the term and you will see your selected page.
4. Use Gutenberg on your public page to customize the archive.

404 Template Mapping

1. Visit Settings->Reading and map your 404 template to a page.
2. Customize the new 404 page.
3. Navigate to a broken URL to preview the 404 page.

== Frequently Asked Questions ==

= I don't see the post types. What's wrong? =

Make sure your post type has <code>has_archive</code> set to true and <code>show_in_rest</code> set to true.

= Can you do posts instead of pages? =

This plugin will only allow mapping to pages.

= Does this plugin work with term (e.g., category) archives? =

Yes. Edit a term and you can map it to any page.

= I need help. Can you help me? =

Yes, just post in the support forums here and I'll do my best to address your issue.

== Screenshots ==

1. Setting up the custom post types block.
2. Styling the block.
3. New Term Grid Block.
4. Settings->Reading option.
5. Term archive option.

== Changelog ==

= 5.1.6 =
* Released 2023-03-17
* Fixing a bug where the term grid didn't show on the frontend.

= 5.1.5 =
* Released 2022-10-02
* Fixing a bug where a term would not save in the custom post types block.

= 5.1.4 =
* Released 2022-10-01
* Updated build scripts.
* Fixing schema errors when it comes to registering attributes.

= 5.1.2 =
* Released 2021-07-28
* Fixing block categories for WP 5.8.

= 5.1.0 =
* Released 2021-03-25
* Seeking volunteer devs if you want to learn blocks and help lower the build size.
* Feature: Can disable post type mapping via the settings page.
* Feature: Can disable the blocks via the settings page.
* Feature: Can disable the columns on the pages screen.
* Feature: Can disable the created image sizes.
* Feature: New admin options to enable/disable settings.
* Bug fix: Selecting individual terms in the term block would not reflect on the front-end.
* Misc: Removing block icon from the Add Blocks screen.
* Misc: Updating block icons and color scheme.
* Bug fix: Tweaking word-break in the terms block.

= 5.0.6 =
* Released 2021-03-09
* Fixing term saving causing archives to fail.

= 5.0.5 =
* Released 2021-02-12
* Mapping to pages now shows a hierachy to more easily select pages.
* New branding. Hopefully less spammy.
* Making readme more descriptive of what the plugin does.

= 5.0.1 =
* Released 2021-02-12
* Fixed the Term block with the block erroring out when modifying the query parameters.
* Testing with WordPress 5.7.

= 5.0.0 =
* Released 2020-09-07
* Added ability to map 404 to a page.
* Fixed Gutenberg bugs in the Custom Post Types block and Featured Posts block.

= 4.5.5 =
* Released 2020-08-23
* Minimium supported version is now WordPress 5.5
* Fixed pagination for WP 5.5.
* Fixed REST API errors for WP 5.5.
* Miscellaneous block fixes and updates.

= 4.5.3 =
* Released 2020-05-17
* Added Polylang support when mapping posts. Added WPML support for the main Custom Post Types block.

= 4.5.2 =
* Released 2020-05-01
* Removing the term redirect as some pages would get "stuck."

= 4.5.1 =
* Released 2020-04-29
* Added pagination to the Featured Posts by Category Block.

= 4.5.0 =
* Released 2020-04-20
* Added featured posts by category block.
* Added block previews.
* New plugin name: Custom Query Blocks

= 4.0.5 -
* Released 2020-04-16
* Added compatibility for Yoast SEO.

= 4.0.1 =
* Released 2020-04-12
* New block: Term (Category) Grid Block.
* HOT FIX: Term Grid wasn't loading.

= 3.3.5 =
* Released 2020-04-07
* Updated plugin architecture.
* Show mapped pages and its own column.
* Mapped term pages now redirect to the correct term.
* Added new contributor: <a href="https://www.easywebdesigntutorials.com/">Paal Joachim</a>.

= 3.3.1 =
* Released 2020-04-01
* Fixing PHP notice saying invalid argument for foreach statement.

= 3.3.0 =
* Released 2020-03-11
* New full post mode for showing off the full post.
* Option to remove the link from the title.

= 3.2.2 =
* Released 2020-02-10
* Plugin was having conflicts with other admin screens when saving terms, resulting in a 403 error.

= 3.2.1 =
* Released 2020-02-03
* Added support for translations.
* Revised loading screen to make it easier on translators.

= 3.2.0 =
* Released 2020-02-03
* Added ability to map term archives to pages.

= 3.1.1 =
* Released 2020-02-01
* Added several order and orderby parameters.

= 3.1.0 =
* Released 2020-01-26
* Removing custom field placeholder if a custom field isn't present.
* Removing continue reading link and post link if custom post type isn't publicly queryable.
* Wrapping excerpt in paragraph tag.
* Add class to readmore paragraph tag for styling.
* Hiding styles options if override styles is present.
* Changing verbiage of remove styles to Override styles.
* Added support for Adobe fonts through https://wordpress.org/plugins/custom-typekit-fonts/

= 3.0.9 =
* Released 2019-12-08
* Added the ability to set a fallback image for the featured image.

= 3.0.7 =
* Released 2019-12-05
* Fixing pagination when a page with the Gutenberg block is set as the front page.

= 3.0.6 =
* Released 2019-12-05
* Moving featured image to its own panel in Gutenberg settings.
* Moving title to its own panel in Gutenberg settings.
* Cleaning up editor styles for headings.
* Adding ability to change the heading HTML tag.

= 3.0.5 =
* Released 2019-12-05
* Adding ability to remove styles so that you can style your own layout.

= 3.0.0 =
* Released 2019-12-03
* Adding custom field support.
* Updating REST API for faster loading.

= 2.2.2 =
* Released 2019-12-02
* Added ability to remove title from displaying.
* Added new branded loading animation.

= 2.2.1 =
* Released 2019-10-29
* Testing up to WordPress 5.3.
* Fixing JS error when jQuery is not defined as a $ variable.

= 2.2.0 =
* Released 2019-08-23
* Bug fix when in grid mode and image is placed below the title.
* Bug fix: skipping taxonomies when there are none.
* Bug fix: fixing capitalization error.
* Enhancement: You can now select fonts for your content areas.

= 2.1.2 =
* Released 2019-06-11
* Fixing pagination

= 2.1.1 =
* Released 2019-05-26
* Some users were seeing featured images twice in the back-end.

= 2.1.0 =
* Released 2019-05-25
* Fixed Gravatar sizing not saving.
* Changing the way excerpts are shown in Gutenberg.
* Added the ability to change taxonomy location.
* Content can now be centered in the Grid view.
* Added border, padding, and background style options in Gutenberg block.
* Added color options for text in Gutenberg block.

= 2.0.7 =
* Released 2019-05-24
* Added the ability to trim the excerpt length.
* Reduce the file size of the Gutenberg block script using a new build technique.

= 2.0.5 =
* Released 2019-04-21
* Conditional term filtering

= 2.0.4 =
* Released 2019-04-18
* Adding support for six columns
* Fixing undefined index error

= 2.0.3 =
* Released 2019-03-29
* Fixed term not being saved when displaying posts

= 2.0.1 =
* Released 2019-01-17
* Fixed pagination when using a page with just the block

= 2.0.1 =
* Released 2019-01-17
* Fixing bug where arguments weren't an array when switching reading types in Settings->Reading.

= 2.0.0 =
* Released 2019-01-06
* Numerous enhancements to the Gutenberg block including showing taxonomies, setting the image type (Avatar vs Regular), setting where the featured image is displayed, selecting the image size, and much more.

= 1.0.1 =
* Released 2018-11-07
* WordPress 5.0 compatibility

= 1.0.0 =
* Released 2018-09-24
* Initial release.

== Upgrade Notice ==

= 5.1.6 =
Fixing a bug when the term grid would not show on the frontend. Testing with WP 6.2.
