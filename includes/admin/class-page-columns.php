<?php
/**
 * Page Columns
 *
 * @package PTAM
 */

namespace MediaRon\PTAM\Admin;

/**
 * Page Columns class.
 */
class Page_Columns {

	/**
	 * Class initializer.
	 */
	public function run() {
		add_filter( 'manage_pages_columns', array( $this, 'add_columns' ), 5 );
		add_action( 'manage_pages_custom_column', array( $this, 'column_values' ), 5, 2 );
	}

	/**
	 * Add columns to the pages screen in the admin
	 *
	 * @param array $columns Array of key => value columns.
	 *
	 * @return array Updated list of columns.
	 */
	public function add_columns( $columns ) {
		/**
		 * Allow others to programatically disable columns.
		 *
		 * @since 3.3.5
		 *
		 * @param bool $disable true default.
		 */
		if ( ! apply_filters( 'ptam_add_pages_column', true ) ) {
			return $columns;
		}
		$columns['ptam'] = __( 'Archive Mapping', 'post-type-archive-mapping' );
		return $columns;
	}

	/**
	 * Populate the column vaues for the mapped posts.
	 *
	 * @param string $column_name The column name.
	 * @param int    $page_id     The Page ID.
	 */
	public function column_values( $column_name, $page_id ) {
		if ( 'ptam' === $column_name ) {
			// If successful, returns the post type slug.
			$post_type = get_post_meta( $page_id, '_post_type_mapped', true );
			if ( $post_type && ! empty( $post_type ) ) {
				$archive_link = get_post_type_archive_link( $post_type );
				if ( $archive_link ) {
					echo sprintf(
						'<a href="%s">%s</a>',
						esc_url( $archive_link ),
						esc_html__( 'View Post Type Archive', 'post-type-archive-mapping' )
					);
				}
				return;
			}
			$term_id = get_post_meta( $page_id, '_term_mapped', true );
			if ( $term_id && ! empty( $term_id ) ) {
				$archive_link = get_term_link( absint( $term_id ) );
				if ( is_wp_error( $archive_link ) ) {
					return;
				}
				echo sprintf(
					'<a href="%s">%s</a>',
					esc_url( $archive_link ),
					esc_html__( 'View Term Archive', 'post-type-archive-mapping' )
				);
				return;
			}
		}
	}
}
