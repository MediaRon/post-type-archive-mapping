<?php
/**
 * Custom Post Types Block.
 *
 * @package PTAM
 */

namespace MediaRon\PTAM\Blocks\Custom_Post_Types;

use MediaRon\PTAM\Admin\Options;
use MediaRon\PTAM\Helpers;

/**
 * Custom Post Types Block helper methods.
 */
class Custom_Post_Types {

	/**
	 * Initialize hooks/actions for class.
	 */
	public function run() {
		add_action( 'init', array( $this, 'register_block' ) );
		add_action( 'after_setup_theme', array( $this, 'add_image_sizes' ) );
	}

	/**
	 * Add Image Sizes needed by the plugin.
	 */
	public function add_image_sizes() {
		/**
		 * Allow others to disable programmatically the image size addition.
		 *
		 * @since 3.3.5
		 *
		 * @param bool $add true (default)
		 */
		if ( ! apply_filters( 'ptam_add_image_sizes', true ) ) {
			return;
		}

		// Checks if image sizes are disabled or not.
		if ( false === Options::is_custom_image_sizes_disabled() ) {
			add_image_size( 'ptam-block-post-grid-landscape', 600, 400, true );
			add_image_size( 'ptam-block-post-grid-square', 600, 600, true );
			add_image_size( 'ptam-block-post-grid-featured-landscape', 1000, 600, true );
		}
	}

	/**
	 * Renders the post grid block on server.
	 *
	 * @param array $attributes    Block attributes.
	 * @param int   $post_thumb_id The post thumbnail ID.
	 * @param int   $post_author   The post author.
	 * @param int   $post_id       The Post ID.
	 *
	 * @return string Image markup.
	 */
	public function get_profile_image( $attributes, $post_thumb_id = 0, $post_author = 0, $post_id = 0 ) {
		ob_start();
		// Get the featured image.
		$list_item_markup = '';

		if ( isset( $attributes['displayPostImage'] ) && $attributes['displayPostImage'] ) {
			$post_thumb_size = $attributes['imageTypeSize'];
			$image_type      = $attributes['imageType'];
			if ( 'gravatar' === $image_type ) {
				if ( ! $attributes['removeStyles'] ) {
					$list_item_markup .= sprintf(
						'<div class="ptam-block-post-grid-image" %3$s><a href="%1$s" rel="bookmark">%2$s</a></div>',
						esc_url( get_permalink( $post_id ) ),
						get_avatar( $post_author, $attributes['avatarSize'] ),
						'grid' === $attributes['postLayout'] ? "style='text-align: {$attributes['imageAlignment']}'" : ''
					);
				} else {
					$list_item_markup .= sprintf(
						'<div class="ptam-block-post-grid-image"><a href="%1$s" rel="bookmark">%2$s</a></div>',
						esc_url( get_permalink( $post_id ) ),
						get_avatar( $post_author, $attributes['avatarSize'] )
					);
				}
			} else {
				if ( ! $attributes['removeStyles'] ) {
					$list_item_markup .= sprintf(
						'<div class="ptam-block-post-grid-image" %3$s><a href="%1$s" rel="bookmark">%2$s</a></div>',
						esc_url( get_permalink( $post_id ) ),
						wp_get_attachment_image( $post_thumb_id, $post_thumb_size ),
						'grid' === $attributes['postLayout'] ? "style='text-align: {$attributes['imageAlignment']}'" : ''
					);
				} else {
					$list_item_markup .= sprintf(
						'<div class="ptam-block-post-grid-image"><a href="%1$s" rel="bookmark">%2$s</a></div>',
						esc_url( get_permalink( $post_id ) ),
						wp_get_attachment_image( $post_thumb_id, $post_thumb_size )
					);
				}
			}
			echo $list_item_markup; // phpcs:ignore
		}
		return ob_get_clean();
	}

	/**
	 * Get the taxonomy terms for a post.
	 *
	 * @param object $post The Post.
	 * @param array  $attributes Array of attributes.
	 *
	 * @return string HTML of taxonomy terms.
	 */
	public function get_taxonomy_terms( $post, $attributes = array() ) {
		$markup     = '';
		$taxonomies = get_object_taxonomies( $post->post_type, 'objects' );
		$terms      = array();
		foreach ( $taxonomies as $key => $taxonomy ) {
			if ( 'author' === $key ) {
				continue;
			}
			$term_list  = get_the_terms( $post->ID, $key );
			$term_array = array();
			if ( $term_list && ! empty( $term_list ) ) {
				foreach ( $term_list as $term ) {
					if ( ! $attributes['removeStyles'] ) {
						$term_permalink = get_term_link( $term, $key );
						$term_array[]   = sprintf( '<a href="%s" style="color: %s; text-decoration: none; box-shadow: unset;">%s</a>', esc_url( $term_permalink ), esc_attr( $attributes['linkColor'] ), esc_html( $term->name ) );
					} else {
						$term_permalink = get_term_link( $term, $key );
						$term_array[]   = sprintf( '<a href="%s">%s</a>', esc_url( $term_permalink ), esc_html( $term->name ) );
					}
				}
				$terms[ $key ] = implode( ', ', $term_array );
			} else {
				$terms[ $key ] = false;
			}
		}
		foreach ( $taxonomies as $key => $taxonomy ) {
			if ( 'author' === $key ) {
				continue;
			}
			if ( false === $terms[ $key ] ) {
				continue;
			}
			$markup .= sprintf( '<div class="ptam-terms"><span class="ptam-term-label">%s: </span><span class="ptam-term-values">%s</span></div>', esc_html( $taxonomy->label ), $terms[ $key ] );
		}
		return $markup;
	}

	/**
	 * Retrieve a list of custom posts.
	 *
	 * @param array $attributes Array of passed attributes.
	 *
	 * @return string HTML of the custom posts.
	 */
	public function custom_posts( $attributes ) {
		$paged = 0;
		if ( absint( get_query_var( 'paged' ) > 1 ) ) {
			$paged = absint( get_query_var( 'paged' ) );
		}
		// WP 5.5 quirk for items on the front page.
		if ( is_front_page() ) {
			if ( absint( get_query_var( 'page' ) > 1 ) ) {
				$paged = absint( get_query_var( 'page' ) );
			}
		}
		if ( empty( $paged ) ) {
			$paged = 0;
		}
		$post_args = array(
			'post_type'      => $attributes['postType'],
			'posts_per_page' => $attributes['postsToShow'],
			'post_status'    => 'publish',
			'order'          => $attributes['order'],
			'orderby'        => $attributes['orderBy'],
			'paged'          => $paged,
		);
		if ( isset( $attributes['taxonomy'] ) && isset( $attributes['term'] ) ) {
			if ( 'all' !== $attributes['term'] && 0 !== absint( $attributes['term'] ) && 'none' !== $attributes['taxonomy'] ) {
				$post_args['tax_query'] = array( // phpcs:ignore
					array(
						'taxonomy' => $attributes['taxonomy'],
						'terms'    => $attributes['term'],
					),
				);
			}
		}
		$attributes['displayTitle']        = isset( $attributes['displayTitle'] ) ? esc_html( $attributes['displayTitle'] ) : true;
		$attributes['removeStyles']        = isset( $attributes['removeStyles'] ) ? esc_html( $attributes['removeStyles'] ) : false;
		$attributes['displayCustomFields'] = isset( $attributes['displayCustomFields'] ) ? esc_html( $attributes['displayCustomFields'] ) : true;
		$attributes['titleFont']           = isset( $attributes['titleFont'] ) ? esc_attr( $attributes['titleFont'] ) : 'inherit';
		$attributes['metaFont']            = isset( $attributes['metaFont'] ) ? esc_attr( $attributes['metaFont'] ) : 'inherit';
		$attributes['contentFont']         = isset( $attributes['contentFont'] ) ? esc_attr( $attributes['contentFont'] ) : 'inherit';
		$attributes['continueReadingFont'] = isset( $attributes['continueReadingFont'] ) ? esc_attr( $attributes['continueReadingFont'] ) : 'inherit';
		$attributes['titleHeadingTag']     = isset( $attributes['titleHeadingTag'] ) ? esc_html( $attributes['titleHeadingTag'] ) : 'h2';
		$attributes['wpmlLanguage']        = isset( $attributes['wpmlLanguage'] ) ? esc_html( $attributes['wpmlLanguage'] ) : 'en';

		$image_placememt_options    = $attributes['imageLocation'];
		$taxonomy_placement_options = $attributes['taxonomyLocation'];
		$image_size                 = $attributes['imageTypeSize'];

		/**
		 * Filter the post query.
		 *
		 * @since 4.5.0
		 *
		 * @param array  $post_args  The post arguments.
		 * @param array  $attributes The passed attributes.
		 */
		$post_args = apply_filters( 'ptam_custom_post_types_query', $post_args, $attributes );

		// WPML Compatability.
		global $sitepress;
		if ( ! empty( $sitepress ) ) {
			$sitepress->switch_lang( $attributes['wpmlLanguage'] );
		}

		// Front page pagination fix.
		global $wp_query;
		$temp = $wp_query;
		if ( is_front_page() ) {
			$wp_query     = new \WP_Query( $post_args ); // phpcs:ignore
			$recent_posts = $wp_query;
		} else {
			$recent_posts = new \WP_Query( $post_args );
		}
		if ( ! empty( $sitepress ) ) {
			$sitepress->switch_lang( defined( 'ICL_LANGUAGE_CODE' ) ? ICL_LANGUAGE_CODE : $sitepress->get_default_language() );
		}

		$list_items_markup = '';

		if ( $recent_posts->have_posts() ) :
			while ( $recent_posts->have_posts() ) {
				global $post;
				$recent_posts->the_post();

				// Get the post ID.
				$post_id          = $post->ID;
				$post_type_object = get_post_type_object( get_post_type( $post ) );

				// Get the post thumbnail.
				if ( 'gravatar' === $attributes['imageType'] ) {
					$post_thumb_id = 1;
				} else {
					$post_thumb_id = get_post_thumbnail_id( $post_id );
					if ( empty( $post_thumb_id ) && isset( $attributes['fallbackImg']['id'] ) ) {
						$post_thumb_id = absint( $attributes['fallbackImg']['id'] );
					}
				}

				if ( $post_thumb_id && isset( $attributes['displayPostImage'] ) && $attributes['displayPostImage'] ) {
					$post_thumb_class = 'has-thumb';
				} else {
					$post_thumb_class = 'no-thumb';
				}

				// Start the markup for the post.
				$article_style      = sprintf( 'border: %dpx solid %s;  background: %s; padding: %dpx; border-radius: %dpx;', absint( $attributes['border'] ), esc_attr( $attributes['borderColor'] ), esc_attr( $attributes['backgroundColor'] ), absint( $attributes['padding'] ), absint( $attributes['borderRounded'] ) );
				$list_items_markup .= sprintf(
					'<article class="%1$s" style="%2$s">',
					esc_attr( $post_thumb_class ),
					( ! $attributes['removeStyles'] ) ? $article_style : ''
				);
				if ( 'regular' === $image_placememt_options ) {
					$list_items_markup .= $this->get_profile_image( $attributes, $post_thumb_id, $post->post_author, $post->ID );
				}

				// Wrap the text content.
				$list_items_markup .= sprintf(
					'<div class="ptam-block-post-grid-text">'
				);

				// Get the post title.
				$title = get_the_title( $post_id );

				if ( ! $title ) {
					$title = __( 'Untitled' );
				}

				$display_post_anchor_link = isset( $attributes['displayTitleLink'] ) ? $attributes['displayTitleLink'] : true;

				if ( $attributes['displayTitle'] ) {
					if ( ! $attributes['removeStyles'] ) {
						if ( $post_type_object->publicly_queryable && $display_post_anchor_link ) {
							$list_items_markup .= sprintf(
								'<%5$s class="ptam-block-post-grid-title" %3$s><a href="%1$s" rel="bookmark" style="%4$s">%2$s</a></%5$s>',
								esc_url( get_permalink( $post_id ) ),
								esc_html( $title ),
								( 'grid' === $attributes['postLayout'] && ! $attributes['removeStyles'] ) ? "style='text-align: {$attributes['titleAlignment']}'" : '',
								sprintf(
									'color: %1$s; font-family: %2$s; box-shadow: unset;',
									esc_attr( $attributes['titleColor'] ),
									esc_attr( $attributes['titleFont'] )
								),
								$attributes['titleHeadingTag']
							);
						} else {
							$list_items_markup .= sprintf(
								'<%3$s class="ptam-block-post-grid-title" %2$s>%1$s</%3$s>',
								esc_html( $title ),
								( 'grid' === $attributes['postLayout'] && ! $attributes['removeStyles'] ) ? "style='text-align: {$attributes['titleAlignment']}; color: {$attributes['titleColor']}; font-family: {$attributes['titleFont']}'" : '',
								wp_kses_post( $attributes['titleHeadingTag'] )
							);
						}
					} else {
						if ( $post_type_object->publicly_queryable && $display_post_anchor_link ) {
							$list_items_markup .= sprintf(
								'<h2 class="ptam-block-post-grid-title"><a href="%1$s" rel="bookmark">%2$s</a></h2>',
								esc_url( get_permalink( $post_id ) ),
								esc_html( $title )
							);
						} else {
							$list_items_markup .= sprintf(
								'<h2 class="ptam-block-post-grid-title">%1$s</h2>',
								esc_html( $title )
							);
						}
					}
				}

				if ( $attributes['displayCustomFields'] ) {
					$custom_fields_markup = isset( $attributes['customFields'] ) ? $attributes['customFields'] : '';
					if ( ! empty( $custom_fields_markup ) ) {
						if ( ! $attributes['removeStyles'] ) {
							$list_items_markup .= sprintf(
								'<div class="ptam-block-post-custom-fields" style="color: %s; font-family: %s; text-align: %s;">',
								isset( $attributes['customFieldsColor'] ) ? esc_attr( $attributes['customFieldsColor'] ) : 'inherit',
								isset( $attributes['customFieldsFont'] ) ? esc_attr( $attributes['customFieldsFont'] ) : 'inherit',
								isset( $attributes['customFieldAlignment'] ) ? esc_attr( $attributes['customFieldAlignment'] ) : 'inherit'
							);
						} else {
							$list_items_markup .= '<div class="ptam-block-post-custom-fields">';
						}

						preg_match_all( '/{([-_a-zA-Z0-9]+)}/', $custom_fields_markup, $matches );
						if ( isset( $matches[0] ) && is_array( $matches[0] ) ) {
							foreach ( $matches[0] as $custom_field_match ) {
								// Strip out the {}.
								$maybe_custom_field = str_replace( '{', '', $custom_field_match );
								$maybe_custom_field = str_replace( '}', '', $maybe_custom_field );

								$custom_field_value = '';

								// We may have a custom field. Try ACF first.
								if ( function_exists( 'get_field' ) ) {
									$custom_field_value = get_field( $maybe_custom_field, $post_id );
									if ( $custom_field_value ) {
										/**
										 * Filter the custom field value.
										 *
										 * Filter the custom field value.
										 *
										 * @since 3.0.0
										 *
										 * @param mixed  $custom_field_value The custom field value.
										 * @param string $maybe_custom_field The custom field name.
										 */
										$custom_field_value   = apply_filters( 'ptam_custom_field', $custom_field_value, $maybe_custom_field );
										$custom_fields_markup = str_replace( $custom_field_match, $custom_field_value, $custom_fields_markup );
									}
								}
								// ACF Failed. Let's try post meta.
								if ( empty( $custom_field_value ) ) {
									$custom_field_value = get_post_meta( $post_id, $maybe_custom_field, true );
									if ( $custom_field_value ) {
										/**
										 * Filter the custom field value.
										 *
										 * Filter the custom field value.
										 *
										 * @since 3.0.0
										 *
										 * @param mixed  $custom_field_value The custom field value.
										 * @param string $maybe_custom_field The custom field name.
										 * @param int    $post_id            The Post ID.
										 */
										$custom_field_value   = apply_filters( 'ptam_custom_field', $custom_field_value, $maybe_custom_field, $post_id );
										$custom_fields_markup = str_replace( $custom_field_match, $custom_field_value, $custom_fields_markup );
									} else {
										/**
										 * Filter the custom field value.
										 *
										 * Filter the custom field value.
										 *
										 * @since 3.0.0
										 *
										 * @param mixed  $custom_field_value The custom field value.
										 * @param string $maybe_custom_field The custom field name.
										 * @param int    $post_id            The Post ID.
										 */
										$custom_field_value   = apply_filters( 'ptam_custom_field', '', $maybe_custom_field, $post_id );
										$custom_fields_markup = str_replace( $custom_field_match, $custom_field_value, $custom_fields_markup );
									}
								}
							}
						}
						$list_items_markup .= wp_kses_post( $custom_fields_markup ); // wp_kses_post used to strip out harmful HTML.
						$list_items_markup .= '</div>';
					}
				}

				$show_meta = false;
				if ( $attributes['displayCustomFields'] || ( isset( $attributes['displayPostAuthor'] ) && $attributes['displayPostAuthor'] ) || ( isset( $attributes['displayTaxonomies'] ) && $attributes['displayTaxonomies'] ) || ( isset( $attributes['displayPostDate'] ) && $attributes['displayPostDate'] ) ) {
					$show_meta = true;
				}

				if ( $show_meta ) {
					// Wrap the byline content.
					if ( ! $attributes['removeStyles'] ) {
						$list_items_markup .= sprintf(
							'<div class="ptam-block-post-grid-byline %s" %s>',
							isset( $attributes['changeCapitilization'] ) && $attributes['changeCapitilization'] ? 'ptam-text-lower-case' : '',
							'grid' === $attributes['postLayout'] ? "style='text-align: {$attributes['metaAlignment']}; color: {$attributes['contentColor']}; font-family: {$attributes['metaFont']}'" : "style='color: {$attributes['contentColor']}; font-family: {$attributes['metaFont']}'"
						);
					} else {
						$list_items_markup .= sprintf(
							'<div class="ptam-block-post-grid-byline %s">',
							isset( $attributes['changeCapitilization'] ) && $attributes['changeCapitilization'] ? 'ptam-text-lower-case' : ''
						);
					}
				}

				// Get the featured image.
				if ( isset( $attributes['displayPostImage'] ) && $attributes['displayPostImage'] && $post_thumb_id && 'below_title' === $attributes['imageLocation'] ) {
					if ( ! $attributes['removeStyles'] ) {
						$list_items_markup .= sprintf(
							'<div class="ptam-block-post-grid-image" %3$s><a href="%1$s" rel="bookmark">%2$s</a></div>',
							esc_url( get_permalink( $post_id ) ),
							$this->get_profile_image( $attributes, $post_thumb_id, $post->post_author, $post->ID ),
							'grid' === $attributes['postLayout'] ? "style='text-align: {$attributes['imageAlignment']}" : ''
						);
					} else {
						$list_items_markup .= sprintf(
							'<div class="ptam-block-post-grid-image"><a href="%1$s" rel="bookmark">%2$s</a></div>',
							esc_url( get_permalink( $post_id ) ),
							$this->get_profile_image( $attributes, $post_thumb_id, $post->post_author, $post->ID )
						);
					}
				}

				// Get the post author.
				if ( isset( $attributes['displayPostAuthor'] ) && $attributes['displayPostAuthor'] ) {
					if ( ! $attributes['removeStyles'] ) {
						$list_items_markup .= sprintf(
							'<div class="ptam-block-post-grid-author"><a class="ptam-text-link" href="%2$s" style="color: %3$s">%1$s</a></div>',
							esc_html( get_the_author_meta( 'display_name', $post->post_author ) ),
							esc_html( get_author_posts_url( $post->post_author ) ),
							esc_attr( $attributes['linkColor'] )
						);
					} else {
						$list_items_markup .= sprintf(
							'<div class="ptam-block-post-grid-author"><a class="ptam-text-link" href="%2$s">%1$s</a></div>',
							esc_html( get_the_author_meta( 'display_name', $post->post_author ) ),
							esc_html( get_author_posts_url( $post->post_author ) )
						);
					}
				}

				// Get the post date.
				if ( isset( $attributes['displayPostDate'] ) && $attributes['displayPostDate'] ) {
					$list_items_markup .= sprintf(
						'<time datetime="%1$s" class="ptam-block-post-grid-date">%2$s</time>',
						esc_attr( get_the_date( 'c', $post_id ) ),
						esc_html( get_the_date( '', $post_id ) )
					);
				}
				// Get the taxonomies.
				if ( isset( $attributes['displayTaxonomies'] ) && $attributes['displayTaxonomies'] && 'regular' === $taxonomy_placement_options ) {
					$list_items_markup .= $this->get_taxonomy_terms( $post, $attributes );
				}
				// Get the featured image.
				if ( isset( $attributes['displayPostImage'] ) && $attributes['displayPostImage'] && $post_thumb_id && 'below_title_and_meta' === $attributes['imageLocation'] ) {
					$list_items_markup .= sprintf(
						'<div class="ptam-block-post-grid-image"><a href="%1$s" rel="bookmark">%2$s</a></div>',
						esc_url( get_permalink( $post_id ) ),
						$this->get_profile_image( $attributes, $post_thumb_id, $post->post_author, $post->ID )
					);
				}

				// Close the byline content.
				if ( $show_meta ) {
					$list_items_markup .= sprintf(
						'</div>'
					);
				}

				// Wrap the excerpt content.
				if ( ! $attributes['removeStyles'] ) {
					$list_items_markup .= sprintf(
						'<p class="ptam-block-post-grid-excerpt" %s>',
						'grid' === $attributes['postLayout'] ? "style='text-align: {$attributes['contentAlignment']}; color: {$attributes['contentColor']}; font-family: {$attributes['contentFont']}'" : "style='color: {$attributes['contentColor']}; font-family: {$attributes['contentFont']}'"
					);
				} else {
					$list_items_markup .= '<p class="ptam-block-post-grid-excerpt">';
				}

				if ( isset( $attributes['postLayout'] ) && 'full_content' === $attributes['postLayout'] ) {
					$list_items_markup .= apply_filters( 'the_content', $post->post_content );
				} else {
					// Get the excerpt.
					$excerpt = $post->post_excerpt;

					if ( empty( $excerpt ) ) {
						$excerpt = wp_strip_all_tags( strip_shortcodes( $post->post_content ) );
					}

					if ( ! $excerpt ) {
						$excerpt = null;
					} else {
						$excerpt = wp_trim_words( apply_filters( 'the_excerpt', $excerpt ), isset( $attributes['trimWords'] ) ? $attributes['trimWords'] : 55 );
					}

					if ( isset( $attributes['displayPostExcerpt'] ) && $attributes['displayPostExcerpt'] ) {
						$list_items_markup .= wp_kses_post( $excerpt );
					}
				}
				if ( $post_type_object->publicly_queryable ) {
					if ( isset( $attributes['displayPostLink'] ) && $attributes['displayPostLink'] ) {
						if ( ! $attributes['removeStyles'] ) {
							$list_items_markup .= sprintf(
								'<p class="ptam-block-post-grid-link-wrapper"><a class="ptam-block-post-grid-link ptam-text-link" href="%1$s" rel="bookmark" style="color: %3$s; font-family: %4$s">%2$s</a></p>',
								esc_url( get_permalink( $post_id ) ),
								esc_html( $attributes['readMoreText'] ),
								esc_attr( $attributes['continueReadingColor'] ),
								esc_attr( $attributes['continueReadingFont'] )
							);
						} else {
							$list_items_markup .= sprintf(
								'<p class="ptam-block-post-grid-link-wrapper"><a class="ptam-block-post-grid-link ptam-text-link" href="%1$s" rel="bookmark">%2$s</a></p>',
								esc_url( get_permalink( $post_id ) ),
								esc_html( $attributes['readMoreText'] )
							);
						}
					}
				}

				// Get the featured image.
				if ( isset( $attributes['displayPostImage'] ) && $attributes['displayPostImage'] && $post_thumb_id && 'bottom' === $attributes['imageLocation'] ) {
					if ( 'landscape' === $attributes['imageCrop'] ) {
						$post_thumb_size = 'ptam-block-post-grid-landscape';
					} else {
						$post_thumb_size = 'ptam-block-post-grid-square';
					}

					$list_items_markup .= sprintf(
						'<div class="ptam-block-post-grid-image"><a href="%1$s" rel="bookmark">%2$s</a></div>',
						esc_url( get_permalink( $post_id ) ),
						$this->get_profile_image( $attributes, $post_thumb_id, $post->post_author, $post->ID )
					);
				}

				// Close the excerpt content.
				$list_items_markup .= sprintf(
					'</p>'
				);

				// Get the taxonomies.
				if ( isset( $attributes['displayTaxonomies'] ) && $attributes['displayTaxonomies'] && 'below_content' === $taxonomy_placement_options ) {
					if ( ! $attributes['removeStyles'] ) {
						$list_items_markup .= sprintf( '<div %s>', 'grid' === $attributes['postLayout'] ? "style='text-align: {$attributes['metaAlignment']};color: {$attributes['contentColor']}; font-family: {$attributes['metaFont']}'" : "style='color: {$attributes['contentColor']}; font-family: {$attributes['metaFont']}'" );
						$list_items_markup .= $this->get_taxonomy_terms( $post, $attributes );
						$list_items_markup .= '</div>';
					} else {
						$list_items_markup .= '<div>';
						$list_items_markup .= $this->get_taxonomy_terms( $post, $attributes );
						$list_items_markup .= '</div>';
					}
				}
				// Wrap the text content.
				$list_items_markup .= '</div>'; // ptam-block-post-grid-text.

				// Close the markup for the post.
				$list_items_markup .= "</article>\n";
			}
		endif;

		// Build the classes.
		$class = "ptam-block-post-grid align{$attributes['align']}";

		if ( isset( $attributes['className'] ) ) {
			$class .= ' ' . $attributes['className'];
		}

		$grid_class = 'ptam-post-grid-items';

		if ( isset( $attributes['postLayout'] ) && 'list' === $attributes['postLayout'] ) {
			$grid_class .= ' is-list';
		} elseif ( isset( $attributes['postLayout'] ) && 'grid' === $attributes['postLayout'] ) {
			$grid_class .= ' is-grid';
		} else {
			$grid_class .= ' full-content';
		}

		if ( isset( $attributes['columns'] ) && 'grid' === $attributes['postLayout'] ) {
			$grid_class .= ' columns-' . $attributes['columns'];
		}

		// Pagination.
		$pagination = '';
		if ( isset( $attributes['pagination'] ) && $attributes['pagination'] ) {
			$pagination = paginate_links(
				array(
					'total'        => $recent_posts->max_num_pages,
					'current'      => max( 1, get_query_var( 'paged' ) ),
					'format'       => 'page/%#%',
					'show_all'     => false,
					'type'         => 'list',
					'end_size'     => 1,
					'mid_size'     => 2,
					'prev_next'    => false,
					'prev_text'    => sprintf( '<i></i> %1$s', __( 'Newer Items', 'post-type-archive-mapping' ) ),
					'next_text'    => sprintf( '%1$s <i></i>', __( 'Older Items', 'post-type-archive-mapping' ) ),
					'add_args'     => false,
					'add_fragment' => '',
				)
			);
		}

		// Output the post markup.
		$block_content = sprintf(
			'<div class="%1$s"><div class="%2$s">%3$s</div><div class="ptam-pagination">%4$s</div></div>',
			esc_attr( $class ),
			esc_attr( $grid_class ),
			$list_items_markup,
			$pagination
		);

		$wp_query = $temp; // phpcs:ignore

		return $block_content;
	}

	/**
	 * Registers the block on server.
	 */
	public function register_block() {

		// Check if the register function exists.
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		register_block_type(
			Helpers::get_plugin_dir( 'build/block/custom-post-one/block.json' ),
			array( 'render_callback' => array( $this, 'custom_posts' ) ),
		);
	}
}
