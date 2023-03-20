<?php
/**
 * Override Yoast OpenGraph tags and meta tags.
 *
 * @package PTAM
 */

namespace MediaRon\PTAM;

/**
 * Class Yoast
 */
class Yoast {

	/**
	 * Main init functioin.
	 */
	public function run() {
		add_action(
			'wp',
			function () {
				if ( get_query_var( 'original_archive_type' ) && get_query_var( 'original_archive_id' ) ) {
					add_filter( 'wpseo_opengraph_desc', array( $this, 'opengraph_desc' ), 20, 1 );
					add_filter( 'wpseo_twitter_description', array( $this, 'opengraph_desc' ), 20, 1 );
					add_filter( 'wpseo_opengraph_title', array( $this, 'opengraph_title' ), 20, 1 );
					add_filter( 'wpseo_twitter_title', array( $this, 'opengraph_title' ), 20, 1 );
					add_filter( 'wpseo_opengraph_url', array( $this, 'opengraph_url' ), 20, 1 );
					// Disable schemas on archives.
					add_filter( 'wpseo_json_ld_output', '__return_false' );
				}
			}
		);
	}

	/**
	 * Override Opengraph Description.
	 *
	 * @param string $description Open graph description.
	 *
	 * @return string description.
	 */
	public function opengraph_desc( $description ) {
		$archive_type  = get_query_var( 'original_archive_type' );
		$archive_id    = get_query_var( 'original_archive_id' );
		$yoast_options = get_option( 'wpseo_titles' );
		if ( 'page' === $archive_type ) {
			$post_type = $archive_id;
			if ( isset( $yoast_options[ 'metadesc-' . $post_type ] ) ) {
				return $yoast_options[ 'metadesc-' . $post_type ];
			}
		}
		if ( 'term' === $archive_type ) {
			$term_id          = absint( $archive_id );
			$term             = get_term_by( 'id', $term_id, get_query_var( 'term_tax' ) );
			$term_description = get_term_field( 'description', $term_id );
			if ( is_wp_error( $term_description ) ) {
				return $description;
			}
			return wp_strip_all_tags( $term_description );
		}
		return $description;
	}

	/**
	 * Change the opengraph url.
	 *
	 * @param string $url The URL to override.
	 *
	 * @return string Updated URL.
	 */
	public function opengraph_url( $url ) {
		$archive_type = get_query_var( 'original_archive_type' );
		$archive_id   = get_query_var( 'original_archive_id' );
		if ( 'page' === $archive_type ) {
			$post_type = $archive_id;
			$url       = rawurlencode( get_post_type_archive_link( $post_type ) );
			return $url;
		}
		if ( 'term' === $archive_type ) {
			$term_id = absint( $archive_id );
			$url     = rawurlencode( get_term_link( $term_id ) );
			return $url;
		}
		return $url;
	}

	/**
	 * Change the opengraph title.
	 *
	 * @param string $title The Title to override.
	 *
	 * @return string Updated Title.
	 */
	public function opengraph_title( $title ) {
		$archive_type = get_query_var( 'original_archive_type' );
		$archive_id   = get_query_var( 'original_archive_id' );
		if ( 'page' === $archive_type ) {
			$post_type      = $archive_id;
			$post_type_data = get_post_type_object( $post_type );

			$title = isset( $post_type_data->labels->name ) ? apply_filters( 'post_type_archive_title', $post_type_data->labels->name, $post_type ) : $title;
			return $title;
		}
		if ( 'term' === $archive_type ) {
			$term_id = absint( $archive_id );
			$term    = get_term_by( 'id', $term_id, get_query_var( 'term_tax' ) );
			if ( is_wp_error( $term ) ) {
				return $title;
			}
			$title = apply_filters( 'single_term_title', $term->name );
			return $title;
		}
		return $title;
	}
}
