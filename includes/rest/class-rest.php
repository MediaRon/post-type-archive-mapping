<?php
/**
 * Register REST functions.
 *
 * @package PTAM
 */

namespace MediaRon\PTAM\Rest;

use MediaRon\PTAM\Helpers;

/**
 * Class functions
 */
class Rest {
	/**
	 * Initialization function.
	 */
	public function run() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register REST routes for the plugin.
	 */
	public function register_routes() {
		register_rest_route(
			'ptam/v2',
			'/get_terms',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'get_all_terms' ),
				'permission_callback' => '__return_true',
			)
		);
		register_rest_route(
			'ptam/v2',
			'/get_posts',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'get_posts' ),
				'permission_callback' => '__return_true',
			)
		);
		register_rest_route(
			'ptam/v2',
			'/get_taxonomies',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'get_taxonomies' ),
				'permission_callback' => '__return_true',
			)
		);
		register_rest_route(
			'ptam/v2',
			'/get_images',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'get_image' ),
				'permission_callback' => '__return_true',
			)
		);
		register_rest_route(
			'ptam/v2',
			'/get_tax_terms',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'get_tax_terms' ),
				'permission_callback' => '__return_true',
			)
		);
		register_rest_route(
			'ptam/v2',
			'/get_tax_term_data',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'get_tax_term_data' ),
				'permission_callback' => '__return_true',
			)
		);
		register_rest_route(
			'ptam/v2',
			'/get_featured_posts',
			array(
				'methods'  => 'POST',
				'callback' => array( $this, 'get_featured_posts' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * Return terms for taxonomy.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $tax_data The tax data.
	 */
	public function get_all_terms( $tax_data ) {
		$taxonomy  = $tax_data['taxonomy'];
		$post_type = $tax_data['post_type'];
		add_filter( 'terms_clauses', array( $this, 'terms_clauses' ), 10, 3 );
		$terms = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => true,
				'post_type'  => $post_type,
			)
		);
		remove_filter( 'terms_clauses', array( $this, 'terms_clauses' ), 10, 3 );
		if ( is_wp_error( $terms ) ) {
			die( wp_json_encode( array() ) );
		} else {
			die( wp_json_encode( $terms ) );
		}
	}

	/**
	 * Return terms for taxonomy.
	 *
	 * @since 4.0.0
	 *
	 * @param WP_REST_Request $tax_data The tax data.
	 */
	public function get_tax_terms( $tax_data ) {
		$taxonomy = $tax_data['taxonomy'];
		$terms    = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => true,
			)
		);
		if ( is_wp_error( $terms ) ) {
			die( wp_json_encode( array() ) );
		} else {
			die( wp_json_encode( $terms ) );
		}
	}

	/**
	 * Return term data for passed terms.
	 *
	 * @since 4.0.0
	 *
	 * @param WP_REST_Request $term_data The term data.
	 */
	public function get_tax_term_data( $term_data ) {
		$terms                     = $term_data['terms'];
		$terms_exclude             = $term_data['termsExclude'];
		$order                     = $term_data['order'];
		$order_by                  = $term_data['orderBy'];
		$taxonomy                  = $term_data['taxonomy'];
		$background_image_source   = $term_data['backgroundImageSource'];
		$background_fallback_image = $term_data['backgroundImageFallback'];
		$background_image_meta_key = $term_data['backgroundImageMeta'];
		$background_image_size     = $term_data['imageSize'];

		// Get All Terms again so we have a full list.
		$all_terms    = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => true,
			)
		);
		$all_term_ids = array();
		foreach ( $all_terms as $index => $term ) {
			$all_term_ids[] = $term->term_id;
		}

		// Populate terms to display.
		$display_all_terms = false;
		$terms_to_include  = array();
		foreach ( $terms as $index => $term_id ) {
			if ( 0 === $term_id ) {
				$display_all_terms = true;
				$terms_to_include  = $all_term_ids;
				break;
			} else {
				$terms_to_include[] = $term_id;
			}
		}

		// Now let's get terms to exclude.
		if ( $display_all_terms ) {
			foreach ( $terms_to_include as $index => $term_id ) {
				if ( in_array( $term_id, $terms_exclude, true ) ) {
					unset( $terms_to_include[ $index ] );
				}
			}
		}

		// Build Query.
		$query = array();
		switch ( $order_by ) {
			case 'slug':
				$query = array(
					'orderby'    => 'slug',
					'order'      => $order,
					'hide_empty' => true,
					'include'    => $terms_to_include,
					'taxonomy'   => $taxonomy,
				);
				break;
			case 'order':
				$query = array(
					'orderby'    => 'meta_value_num',
					'order'      => $order,
					'meta_query' => array( // phpcs:ignore
						'relation' => 'OR',
						array(
							'key'     => 'post_order',
							'compare' => 'NOT EXISTS',
						),
						array(
							'key'     => 'post_order',
							'value'   => 0,
							'compare' => '>=',
						),
					),
					'hide_empty' => true,
					'include'    => $terms_to_include,
					'taxonomy'   => $taxonomy,
				);
				break;
			default:
				$query = array(
					'orderby'    => 'name',
					'order'      => $order,
					'hide_empty' => true,
					'include'    => $terms_to_include,
					'taxonomy'   => $taxonomy,
				);
				break;
		}
		if ( empty( $terms ) || ! is_array( $terms ) ) {
			die( wp_json_encode( array( 'term_data' => new \stdClass() ) ) );
		}

		// Retrieve the terms in order.
		$raw_term_results = get_terms( $query );
		if ( is_wp_error( $raw_term_results ) ) {
			die( wp_json_encode( array( 'term_data' => new \stdClass() ) ) );
		}

		// Get data for each term.
		foreach ( $raw_term_results as &$term ) {
			$term->permalink        = get_term_link( $term );
			$term->background_image = Helpers::get_term_image(
				$background_image_size,
				$background_image_meta_key,
				$background_image_source,
				$taxonomy,
				$term->term_id
			);
			if ( empty( $term->background_image ) ) {
				$term->background_image = isset( $background_fallback_image['id'] ) ? absint( $background_fallback_image['id'] ) : 0;
				$fallback_image         = wp_get_attachment_url( $term->background_image );
				if ( $fallback_image ) {
					$term->background_image = $fallback_image;
				}
			}
		}

		if ( is_wp_error( $terms ) ) {
			die( wp_json_encode( array( 'term_data' => new \stdClass() ) ) );
		} else {
			die( wp_json_encode( array( 'term_data' => $raw_term_results ) ) );
		}
	}

	/**
	 * Return Taxonomies
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $post_data Post data.
	 */
	public function get_taxonomies( $post_data ) {
		$post_type  = $post_data['post_type'];
		$taxonomies = get_object_taxonomies( $post_type, 'objects' );
		die( wp_json_encode( $taxonomies ) );
	}

	/**
	 * Return Posts
	 *
	 * @since 1.0.0
	 * @param WP_REST_Request $post_data Post data.
	 */
	public function get_posts( $post_data ) {
		$taxonomy       = $post_data['taxonomy'];
		$order          = $post_data['order'];
		$orderby        = $post_data['orderby'];
		$term           = $post_data['term'];
		$post_type      = $post_data['post_type'];
		$posts_per_page = $post_data['posts_per_page'];
		$image_type     = $post_data['image_type'];
		$image_size     = $post_data['image_size'];
		$avatar_size    = $post_data['avatar_size'];
		$link_color     = $post_data['link_color'];
		$default_image  = isset( $post_data['default_image']['id'] ) ? absint( $post_data['default_image']['id'] ) : 0;
		$language       = $post_data['language'];

		$post_args = array(
			'post_type'      => $post_type,
			'post_status'    => 'publish',
			'order'          => $order,
			'orderby'        => $orderby,
			'posts_per_page' => $posts_per_page,
		);
		if ( 'all' !== $term && 0 !== absint( $term ) && 'none' !== $taxonomy ) {
			$post_args['tax_query'] = array( // phpcs:ignore
				array(
					'taxonomy' => $taxonomy,
					'terms'    => $term,
				),
			);
		}
		// WPML Compatability.
		global $sitepress;
		if ( ! empty( $sitepress ) ) {
			$sitepress->switch_lang( $language );
		}
		$query = new \WP_Query( $post_args );
		if ( ! empty( $sitepress ) ) {
			$sitepress->switch_lang( $sitepress->get_default_language() );
		}
		$posts = array();
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				global $post;
				$query->the_post();
				if ( 'gravatar' === $image_type ) {
					$thumbnail = get_avatar( $post->post_author, $avatar_size );
				} else {
					$thumbnail = get_the_post_thumbnail( $post->ID, $image_size );
					if ( empty( $thumbnail ) ) {
						$thumbnail = wp_get_attachment_image( $default_image, $image_size );
					}
				}
				$post->featured_image_src = $thumbnail;

				// Get author information.
				$display_name = get_the_author_meta( 'display_name', $post->post_author );
				$author_url   = get_author_posts_url( $post->post_author );

				$post->author_info               = new \stdClass();
				$post->author_info->display_name = $display_name;
				$post->author_info->author_link  = $author_url;

				$post->link = get_permalink( $post->ID );

				// Get taxonomy information.
				$taxonomies = get_object_taxonomies( $post->post_type, 'objects' );
				$terms      = array();
				foreach ( $taxonomies as $key => $taxonomy ) {
					if ( 'author' === $key ) {
						unset( $taxonomies[ $key ] );
						continue;
					}
					$term_list  = get_the_terms( $post->ID, $key );
					$term_array = array();
					if ( $term_list && ! empty( $term_list ) ) {
						foreach ( $term_list as $term ) {
							$term_permalink = get_term_link( $term, $key );
							$term_array[]   = sprintf( '<a href="%s" style="color: %s; text-decoration: none; box-shadow: unset;">%s</a>', esc_url( $term_permalink ), esc_attr( 6 === strlen( $link_color ) ? '#' . $link_color : $link_color ), esc_html( $term->name ) );
						}
						$terms[ $key ] = implode( ', ', $term_array );
					} else {
						$terms[ $key ] = false;
					}
				}
				$post->terms = $terms;

				if ( empty( $post->post_excerpt ) ) {
					$post->post_excerpt = apply_filters( 'the_excerpt', wp_strip_all_tags( strip_shortcodes( $post->post_content ) ) );
				}

				if ( ! $post->post_excerpt ) {
					$post->post_excerpt = null;
				}

				$post->post_excerpt = wp_kses_post( $post->post_excerpt );
				$post->post_content = apply_filters( 'ptam_the_content', $post->post_content );
				$posts[]            = $post;
			}
		}
		$return = array(
			'posts'       => $posts,
			'image_sizes' => Helpers::get_all_image_sizes(),
			'taxonomies'  => $taxonomies,
			'fonts'       => Helpers::get_fonts(),
		);
		die( wp_json_encode( $return ) );
	}

	/**
	 * Return Featured Posts
	 *
	 * @since 4.5.0
	 * @param WP_REST_Request $post_data Post data.
	 */
	public function get_featured_posts( $post_data ) {
		$taxonomy       = $post_data['taxonomy'];
		$order          = $post_data['order'];
		$orderby        = $post_data['orderby'];
		$term           = $post_data['term'];
		$post_type      = $post_data['post_type'];
		$posts_per_page = $post_data['posts_per_page'];
		$image_type     = $post_data['image_type'];
		$image_size     = $post_data['image_size'];
		$avatar_size    = $post_data['avatar_size'];
		$default_image  = isset( $post_data['default_image']['id'] ) ? absint( $post_data['default_image']['id'] ) : 0;

		$post_args = array(
			'post_type'      => $post_type,
			'post_status'    => 'publish',
			'order'          => $order,
			'orderby'        => $orderby,
			'posts_per_page' => $posts_per_page,
		);
		if ( 'all' !== $term && 0 !== absint( $term ) && 'none' !== $taxonomy ) {
			$post_args['tax_query'] = array( // phpcs:ignore
				array(
					'taxonomy' => $taxonomy,
					'terms'    => $term,
				),
			);
		}
		$posts = get_posts( $post_args );

		foreach ( $posts as &$post ) {

			if ( 'gravatar' === $image_type ) {
				$thumbnail = get_avatar( $post->post_author, $avatar_size );
			} else {
				$thumbnail = get_the_post_thumbnail( $post->ID, $image_size );
				if ( empty( $thumbnail ) ) {
					$thumbnail = wp_get_attachment_image( $default_image, $image_size );
				}
			}
			$post->featured_image_src = $thumbnail;

			// Get author information.
			$display_name = get_the_author_meta( 'display_name', $post->post_author );
			$author_url   = get_author_posts_url( $post->post_author );

			$post->author_info               = new \stdClass();
			$post->author_info->display_name = $display_name;
			$post->author_info->author_link  = $author_url;

			$post->link = get_permalink( $post->ID );

			// Get taxonomy information.
			$taxonomies = get_object_taxonomies( $post->post_type, 'objects' );
			$terms      = array();
			foreach ( $taxonomies as $key => $taxonomy ) {
				if ( 'author' === $key ) {
					unset( $taxonomies[ $key ] );
					continue;
				}
				$term_list = get_the_terms( $post->ID, $key );
				if ( ! is_wp_error( $term_list ) && ! $term_list && is_array( $term_list ) ) {
					foreach ( $term_list as $index => $term_raw ) {
						$terms[ $term_raw->term_id ] = $term_raw->name;
					}
				}
			}

			if ( empty( $post->post_excerpt ) ) {
				$post->post_excerpt = wp_trim_words( apply_filters( 'the_excerpt', wp_strip_all_tags( strip_shortcodes( $post->post_content ) ) ), 55 );
			}

			if ( ! $post->post_excerpt ) {
				$post->post_excerpt = null;
			}

			$post->post_excerpt = wp_kses_post( $post->post_excerpt );
			$post->post_content = apply_filters( 'ptam_the_content', $post->post_content );
		}
		$return = array(
			'posts'      => $posts,
			'taxonomies' => $taxonomies,
			'terms'      => $terms,
		);
		die( wp_json_encode( $return ) );
	}

	/**
	 * Get an image based on what a user has selected
	 *
	 * @param WP_REST_Request $post_data Post data.
	 */
	public function get_image( $post_data ) {
		$taxonomy       = $post_data['taxonomy'];
		$order          = $post_data['order'];
		$orderby        = $post_data['orderby'];
		$term           = $post_data['term'];
		$post_type      = $post_data['post_type'];
		$posts_per_page = $post_data['posts_per_page'];
		$avatar_size    = $post_data['avatar_size'];
		$image_type     = $post_data['image_type'];
		$image_size     = $post_data['image_size'];
		$link_color     = $post_data['link_color'];
		$default_image  = isset( $post_data['default_image']['id'] ) ? absint( $post_data['default_image']['id'] ) : 0;
		$language       = $post_data['language'];

		$post_args = array(
			'post_type'      => $post_type,
			'post_status'    => 'publish',
			'order'          => $order,
			'orderby'        => $orderby,
			'posts_per_page' => $posts_per_page,
		);
		if ( 'all' !== $term && 0 !== absint( $term ) && 'none' !== $taxonomy ) {
			$post_args['tax_query'] = array( // phpcs:ignore
				array(
					'taxonomy' => $taxonomy,
					'terms'    => $term,
				),
			);
		}
		// WPML Compatability.
		global $sitepress;
		if ( ! empty( $sitepress ) ) {
			$sitepress->switch_lang( $language );
		}
		$query = new \WP_Query( $post_args );
		if ( ! empty( $sitepress ) ) {
			$sitepress->switch_lang( $sitepress->get_default_language() );
		}
		$posts = array();
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				global $post;
				$query->the_post();
				$thumbnail = '';
				if ( 'gravatar' === $image_type ) {
					$thumbnail = get_avatar( $post->post_author, $avatar_size );
				} else {
					$thumbnail = get_the_post_thumbnail( $post->ID, $image_size );
					if ( empty( $thumbnail ) ) {
						$thumbnail = wp_get_attachment_image( $default_image, $image_size );
					}
				}
				$post->featured_image_src = $thumbnail;

				// Get author information.
				$display_name = get_the_author_meta( 'display_name', $post->post_author );
				$author_url   = get_author_posts_url( $post->post_author );

				$post->author_info               = new \stdClass();
				$post->author_info->display_name = $display_name;
				$post->author_info->author_link  = $author_url;

				$post->link = get_permalink( $post->ID );

				// Get taxonomy information.
				$taxonomies = get_object_taxonomies( $post->post_type, 'objects' );
				$terms      = array();
				foreach ( $taxonomies as $key => $taxonomy ) {
					if ( 'author' === $key ) {
						unset( $taxonomies[ $key ] );
						continue;
					}
					$term_list  = get_the_terms( $post->ID, $key );
					$term_array = array();
					if ( $term_list && ! empty( $term_list ) ) {
						foreach ( $term_list as $term ) {
							$term_permalink = get_term_link( $term, $key );
							$term_array[]   = sprintf( '<a href="%s" style="color: %s; text-decoration: none; box-shadow: unset;">%s</a>', esc_url( $term_permalink ), esc_attr( 6 === strlen( $link_color ) ? '#' . $link_color : $link_color ), esc_html( $term->name ) );
						}
						$terms[ $key ] = implode( ', ', $term_array );
					} else {
						$terms[ $key ] = false;
					}
				}
				$post->terms = $terms;

				// Get excerpt.
				if ( empty( $post->post_excerpt ) ) {
					$post->post_excerpt = apply_filters( 'the_excerpt', wp_strip_all_tags( strip_shortcodes( $post->post_content ) ) );
				}

				if ( ! $post->post_excerpt ) {
					$post->post_excerpt = null;
				}

				$post->post_excerpt = wp_kses_post( $post->post_excerpt );
				$posts[]            = $post;
			}
		}
		$return = array(
			'posts'       => $posts,
			'image_sizes' => Helpers::get_all_image_sizes(),
			'taxonomies'  => $taxonomies,
			'fonts'       => Helpers::get_fonts(),
		);
		die( wp_json_encode( $return ) );
	}

	/**
	 * Extend get terms with post type parameter.
	 *
	 * @global $wpdb
	 * @param string $clauses Term clauses.
	 * @param string $taxonomy Taxonomy.
	 * @param array  $args Aaaarghhhhs.
	 * @return string
	 */
	public function terms_clauses( $clauses, $taxonomy, $args ) {
		if ( isset( $args['post_type'] ) && ! empty( $args['post_type'] ) && 'count' === $args['fields'] ) {
			global $wpdb;

			$post_types = array();

			if ( is_array( $args['post_type'] ) ) {
				foreach ( $args['post_type'] as $cpt ) {
					$post_types[] = "'" . $cpt . "'";
				}
			} else {
				$post_types[] = "'" . $args['post_type'] . "'";
			}

			if ( ! empty( $post_types ) ) {
				$clauses['fields']  = 'DISTINCT ' . str_replace( 'tt.*', 'tt.term_taxonomy_id, tt.taxonomy, tt.description, tt.parent', $clauses['fields'] ) . ', COUNT(p.post_type) AS count';
				$clauses['join']   .= ' LEFT JOIN ' . $wpdb->term_relationships . ' AS r ON r.term_taxonomy_id = tt.term_taxonomy_id LEFT JOIN ' . $wpdb->posts . ' AS p ON p.ID = r.object_id';
				$clauses['where']  .= ' AND (p.post_type IN (' . implode( ',', $post_types ) . ') OR p.post_type IS NULL)';
				$clauses['orderby'] = 'GROUP BY t.term_id ' . $clauses['orderby'];
			}
		}
		return $clauses;
	}
}
