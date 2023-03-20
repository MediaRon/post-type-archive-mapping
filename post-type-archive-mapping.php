<?php // phpcs:ignore
/*
Plugin Name: Custom Query Blocks
Plugin URI: https://mediaron.com/custom-query-blocks/
Description: Map your post type and term archives to a page and use our Gutenberg blocks to show posts or terms.
Author: MediaRon LLC
Version: 5.1.6
Requires at least: 5.5
Author URI: https://mediaron.com
Contributors: MediaRon LLC
Text Domain: post-type-archive-mapping
Domain Path: /languages
Credit: Forked from https://github.com/bigwing/post-type-archive-mapping
Credit: Gutenberg block based on Atomic Blocks
Credit: Chris Logan for the initial idea.
Credit: Paal Joaquim for UX and Issue Triage.
*/

global $wp_embed;

define( 'PTAM_VERSION', '5.1.6' );
define( 'PTAM_FILE', __FILE__ );
define( 'PTAM_SPONSORS_URL', 'https://github.com/sponsors/MediaRon' );

require_once __DIR__ . '/autoloader.php';

add_action(
	'plugins_loaded',
	function () {
		MediaRon\PTAM\Plugin::get_instance();
	}
);

add_filter( 'ptam_the_content', array( $wp_embed, 'run_shortcode' ), 8 );
add_filter( 'ptam_the_content', array( $wp_embed, 'autoembed' ), 8 );
add_filter( 'ptam_the_content', 'wptexturize' );
add_filter( 'ptam_the_content', 'convert_chars' );
add_filter( 'ptam_the_content', 'wpautop' );
add_filter( 'ptam_the_content', 'shortcode_unautop' );
add_filter( 'ptam_the_content', 'do_shortcode' );
