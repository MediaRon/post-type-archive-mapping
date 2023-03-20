<?php
/**
 * Terms Block.
 *
 * @package PTAM
 */

namespace MediaRon\PTAM\Blocks\Term_Grid;

use MediaRon\PTAM\Helpers;

/**
 * Custom Post Types Block helper methods.
 */
class Terms {

	/**
	 * Initialize hooks/actions for class.
	 */
	public function run() {
		add_action( 'init', array( $this, 'register_block' ) );
	}

	/**
	 * Retrieve a list of terms for display.
	 *
	 * @param array $attributes Array of passed attributes.
	 *
	 * @return string HTML of the custom posts.
	 */
	public function term_grid( $attributes ) {
		ob_start();

		// Get terms to include.
		$terms = isset( $attributes['terms'] ) ? $attributes['terms'] : array();
		if ( ! is_array( $terms ) || empty( $terms ) ) {
			return ob_get_clean();
		}

		// Get taxonomy.
		$taxonomy = sanitize_text_field( $attributes['taxonomy'] );

		// Get terms to exclude.
		$terms_exclude = isset( $attributes['termsExclude'] ) ? $attributes['termsExclude'] : array();
		if ( ! is_array( $terms_exclude ) ) {
			return ob_get_clean();
		}

		// Get oroder and orderby.
		$order_by = isset( $attributes['orderBy'] ) ? sanitize_text_field( $attributes['orderBy'] ) : '';
		$order    = isset( $attributes['order'] ) ? sanitize_text_field( $attributes['order'] ) : '';

		// Get All Terms again so we have a full list.
		$all_terms = get_terms(
			array(
				'taxonomy'   => isset( $attributes['taxonomy'] ) ? sanitize_text_field( $attributes['taxonomy'] ) : '',
				'hide_empty' => true,
			)
		);
		if ( is_wp_error( $all_terms ) ) {
			return ob_get_clean();
		}
		$all_term_ids = array();
		foreach ( $all_terms as $index => $term ) {
			$all_term_ids[] = $term->term_id;
		}

		// Populate terms to display.
		$display_all_terms = false;
		$terms_to_include  = array();
		foreach ( $terms as $index => $term_data ) {
			// Skip over empty term data.
			if ( empty( $term_data ) ) {
				continue;
			}
			if ( ! isset( $term_data['id'] ) ) {
				$display_all_terms = true;
				$terms_to_include  = $all_term_ids;
				break;
			}
			if ( 0 === $term_data['id'] ) {
				$display_all_terms = true;
				$terms_to_include  = $all_term_ids;
				break;
			} else {
				$terms_to_include[] = absint( $term_data['id'] );
			}
		}

		$terms_to_exclude = array();
		foreach ( $terms_exclude as $index => $term_data ) {
			if ( isset( $term_data['id'] ) ) {
				$terms_to_exclude[] = absint( $term_data['id'] );
			}
		}

		// Now let's get terms to exclude.
		if ( $display_all_terms ) {
			foreach ( $terms_to_include as $index => $term_id ) {
				if ( in_array( $term_id, $terms_to_exclude, true ) ) {
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
		/**
		 * Filter the term query.
		 *
		 * @since 4.0.0
		 *
		 * @param array  $query      The term query.
		 * @param array  $attributes The passed attributes.
		 * @param string $taxonomy   The taxonomy.
		 */
		$query = apply_filters( 'ptam_term_grid_query', $query, $attributes, $taxonomy );

		// Retrieve the terms in order.
		$raw_term_results = get_terms( $query );
		if ( is_wp_error( $raw_term_results ) ) {
			return ob_get_clean();
		}

		$attributes['align']                 = Helpers::sanitize_attribute( $attributes, 'align', 'text' );
		$attributes['columns']               = Helpers::sanitize_attribute( $attributes, 'columns', 'int' );
		$attributes['showTermTitle']         = Helpers::sanitize_attribute( $attributes, 'showTermTitle', 'bool' );
		$attributes['showTermDescription']   = Helpers::sanitize_attribute( $attributes, 'showTermDescription', 'bool' );
		$attributes['disableStyles']         = Helpers::sanitize_attribute( $attributes, 'disableStyles', 'bool' );
		$attributes['linkContainer']         = Helpers::sanitize_attribute( $attributes, 'linkContainer', 'bool' );
		$attributes['linkTermTitle']         = Helpers::sanitize_attribute( $attributes, 'linkTermTitle', 'bool' );
		$attributes['showButton']            = Helpers::sanitize_attribute( $attributes, 'showButton', 'bool' );
		$attributes['backgroundImageSource'] = Helpers::sanitize_attribute( $attributes, 'backgroundImageSource', 'text' );
		$attributes['backgroundImageMeta']   = Helpers::sanitize_attribute( $attributes, 'backgroundImageMeta', 'text' );
		if ( is_array( $attributes['backgroundImageFallback'] ) ) {
			if ( isset( $attributes['backgroundImageFallback']['id'] ) ) {
				$attributes['backgroundImageFallback'] = $attributes['backgroundImageFallback']['id'];
				$attributes['backgroundImageFallback'] = Helpers::sanitize_attribute( $attributes, 'backgroundImageFallback', 'int' );
			} else {
				$attributes['backgroundImageFallback'] = 0;
			}
		} else {
			$attributes['backgroundImageFallback'] = 0;
		}
		$attributes['backgroundColor']                = Helpers::sanitize_attribute( $attributes, 'backgroundColor', 'text' );
		$attributes['backgroundColorHover']           = Helpers::sanitize_attribute( $attributes, 'backgroundColorHover', 'text' );
		$attributes['backgroundGradient']             = Helpers::sanitize_attribute( $attributes, 'backgroundGradient', 'text' );
		$attributes['backgroundGradientHover']        = Helpers::sanitize_attribute( $attributes, 'backgroundGradientHover', 'text' );
		$attributes['overlayColor']                   = Helpers::sanitize_attribute( $attributes, 'overlayColor', 'text' );
		$attributes['overlayColorHover']              = Helpers::sanitize_attribute( $attributes, 'overlayColorHover', 'text' );
		$attributes['overlayOpacity']                 = Helpers::sanitize_attribute( $attributes, 'overlayOpacity', 'float' );
		$attributes['overlayOpacityHover']            = Helpers::sanitize_attribute( $attributes, 'overlayOpacityHover', 'float' );
		$attributes['termTitleColor']                 = Helpers::sanitize_attribute( $attributes, 'termTitleColor', 'text' );
		$attributes['termTitleColorHover']            = Helpers::sanitize_attribute( $attributes, 'termTitleColorHover', 'text' );
		$attributes['termDescriptionColor']           = Helpers::sanitize_attribute( $attributes, 'termDescriptionColor', 'text' );
		$attributes['termDescriptionColorHover']      = Helpers::sanitize_attribute( $attributes, 'termDescriptionColorHover', 'text' );
		$attributes['itemBorder']                     = Helpers::sanitize_attribute( $attributes, 'itemBorder', 'int' );
		$attributes['itemBorderColor']                = Helpers::sanitize_attribute( $attributes, 'itemBorderColor', 'text' );
		$attributes['termTitleFont']                  = Helpers::sanitize_attribute( $attributes, 'termTitleFont', 'text' );
		$attributes['termDescriptionFont']            = Helpers::sanitize_attribute( $attributes, 'termDescriptionFont', 'text' );
		$attributes['termButtonText']                 = Helpers::sanitize_attribute( $attributes, 'termButtonText', 'text' );
		$attributes['termButtonFont']                 = Helpers::sanitize_attribute( $attributes, 'termButtonFont', 'text' );
		$attributes['termButtonTextColor']            = Helpers::sanitize_attribute( $attributes, 'termButtonTextColor', 'text' );
		$attributes['termButtonTextHoverColor']       = Helpers::sanitize_attribute( $attributes, 'termButtonTextHoverColor', 'text' );
		$attributes['termButtonBackgroundColor']      = Helpers::sanitize_attribute( $attributes, 'termButtonBackgroundColor', 'text' );
		$attributes['termButtonBorder']               = Helpers::sanitize_attribute( $attributes, 'termButtonBorder', 'int' );
		$attributes['termButtonBorderColor']          = Helpers::sanitize_attribute( $attributes, 'termButtonBorderColor', 'text' );
		$attributes['termButtonBorderRadius']         = Helpers::sanitize_attribute( $attributes, 'termButtonBorderRadius', 'int' );
		$attributes['columns']                        = Helpers::sanitize_attribute( $attributes, 'columns', 'int' );
		$attributes['showTermTitle']                  = Helpers::sanitize_attribute( $attributes, 'showTermTitle', 'bool' );
		$attributes['disableStyles']                  = Helpers::sanitize_attribute( $attributes, 'disableStyles', 'bool' );
		$attributes['linkTermTitle']                  = Helpers::sanitize_attribute( $attributes, 'linkTermTitle', 'bool' );
		$attributes['imageSize']                      = Helpers::sanitize_attribute( $attributes, 'imageSize', 'text' );
		$attributes['containerId']                    = Helpers::sanitize_attribute( $attributes, 'containerId', 'text' );
		$attributes['backgroundType']                 = Helpers::sanitize_attribute( $attributes, 'backgroundType', 'text' );
		$attributes['itemBorderRadius']               = Helpers::sanitize_attribute( $attributes, 'itemBorderRadius', 'int' );
		$attributes['termButtonBackgroundHoverColor'] = Helpers::sanitize_attribute( $attributes, 'termButtonBackgroundHoverColor', 'text' );
		if ( ! $attributes['disableStyles'] ) :
			?>
		<style>
			<?php
			if ( 'image' === $attributes['backgroundType'] ) {
				$overlay_color       = Helpers::hex2rgba( $attributes['overlayColor'], $attributes['overlayOpacity'] );
				$overlay_color_hover = Helpers::hex2rgba( $attributes['overlayColorHover'], $attributes['overlayOpacityHover'] );
				?>
				#<?php echo esc_html( $attributes['containerId'] ); ?> .ptam-term-grid-item:before {
					content: '';
					position: absolute;
					width: 100%;
					height: 100%;
					background-color: <?php echo esc_html( $overlay_color ); ?>;
					z-index: 1;
				}
				#<?php echo esc_html( $attributes['containerId'] ); ?> .ptam-term-grid-item:hover:before {
					background-color: <?php echo esc_html( $overlay_color_hover ); ?>;
				}
				<?php
			}
			if ( 'none' === $attributes['backgroundType'] ) {
				?>
				#<?php echo esc_html( $attributes['containerId'] ); ?> .ptam-term-grid-item {
					background: transparent;
				}
				<?php
			}
			if ( 'color' === $attributes['backgroundType'] ) {
				?>
				#<?php echo esc_html( $attributes['containerId'] ); ?> .ptam-term-grid-item {
					background-color: <?php echo esc_html( $attributes['backgroundColor'] ); ?>;
				}
				<?php
				if ( $attributes['linkContainer'] ) :
					?>
					#<?php echo esc_html( $attributes['containerId'] ); ?> .ptam-term-grid-item:hover {
						background-color: <?php echo esc_html( $attributes['backgroundColorHover'] ); ?>;
					}
					<?php
				endif;
				?>
				<?php
			}
			if ( 'gradient' === $attributes['backgroundType'] ) {
				?>
				#<?php echo esc_html( $attributes['containerId'] ); ?> .ptam-term-grid-item {
					background-image: <?php echo esc_html( $attributes['backgroundGradient'] ); ?>;
				}
				<?php
				if ( $attributes['linkContainer'] ) :
					?>
					#<?php echo esc_html( $attributes['containerId'] ); ?> .ptam-term-grid-item:hover {
						background-image: <?php echo esc_html( $attributes['backgroundGradientHover'] ); ?>;
					}
					<?php
				endif;
				?>
				<?php
			}
			?>
			#<?php echo esc_html( $attributes['containerId'] ); ?> .ptam-term-grid-item {
				border-color: <?php echo esc_html( $attributes['itemBorderColor'] ); ?>;
				border-width: <?php echo absint( $attributes['itemBorder'] ); ?>px;
				border-radius: <?php echo absint( $attributes['itemBorderRadius'] ); ?>%;
				border-style: solid;
			}
			#<?php echo esc_html( $attributes['containerId'] ); ?> .ptam-term-grid-item h2,
			#<?php echo esc_html( $attributes['containerId'] ); ?> .ptam-term-grid-item h2 a,
			#<?php echo esc_html( $attributes['containerId'] ); ?> .ptam-term-grid-item h2 a:hover {
				color: <?php echo esc_html( $attributes['termTitleColor'] ); ?>;
				text-decoration: none;
				font-family: '<?php echo esc_html( $attributes['termTitleFont'] ); ?>';
			}
			<?php
			if ( $attributes['linkContainer'] ) :
				?>
				#<?php echo esc_html( $attributes['containerId'] ); ?> .ptam-term-grid-item:hover h2,
				#<?php echo esc_html( $attributes['containerId'] ); ?> .ptam-term-grid-item:hover h2 a,
				#<?php echo esc_html( $attributes['containerId'] ); ?> .ptam-term-grid-item:hover h2 a:hover {
					color: <?php echo esc_html( $attributes['termTitleColorHover'] ); ?>;
				}
				<?php
			endif;
			?>
			#<?php echo esc_html( $attributes['containerId'] ); ?> .ptam-term-grid-item .ptam-term-grid-item-description {
				color: <?php echo esc_html( $attributes['termDescriptionColor'] ); ?>;
				font-family: '<?php echo esc_html( $attributes['termDescriptionFont'] ); ?>';
			}
			<?php
			if ( $attributes['linkContainer'] ) :
				?>
				#<?php echo esc_html( $attributes['containerId'] ); ?> .ptam-term-grid-item:hover .ptam-term-grid-item-description {
					color: <?php echo esc_html( $attributes['termDescriptionColorHover'] ); ?>;
				}
				<?php
			endif;
			?>
			#<?php echo esc_html( $attributes['containerId'] ); ?> .ptam-term-grid-item .ptam-term-grid-button {
				color: <?php echo esc_html( $attributes['termButtonTextColor'] ); ?>;
				background-color: <?php echo esc_html( $attributes['termButtonBackgroundColor'] ); ?>;
				border-width: <?php echo absint( $attributes['termButtonBorder'] ); ?>px;
				border-color: <?php echo esc_html( $attributes['termButtonBorderColor'] ); ?>;
				border-radius: <?php echo absint( $attributes['termButtonBorderRadius'] ); ?>px;
				font-family: '<?php echo esc_html( $attributes['termButtonFont'] ); ?>';
				border-style: solid;
			}
			#<?php echo esc_html( $attributes['containerId'] ); ?> .ptam-term-grid-item .ptam-term-grid-button:hover {
				background-color: <?php echo esc_html( $attributes['termButtonBackgroundHoverColor'] ); ?>;
				color: <?php echo esc_html( $attributes['termButtonTextHoverColor'] ); ?>;
				text-decoration: none;
			}
		</style>
			<?php
		endif;
		?>
		<div id="<?php echo ! is_wp_error( $attributes['containerId'] ) ? esc_attr( $attributes['containerId'] ) : ''; ?>" class="columns-<?php echo absint( $attributes['columns'] ); ?> ptam-term-grid align<?php echo esc_attr( $attributes['align'] ); ?>" >
			<?php
			foreach ( $raw_term_results as $index => $term ) {
				?>
				<div class="ptam-term-grid-item"
					<?php
					if ( ! $attributes['disableStyles'] && 'image' === $attributes['backgroundType'] ) {
						$background_image = Helpers::get_term_image( $attributes['imageSize'], $attributes['backgroundImageMeta'], $attributes['backgroundImageSource'], $taxonomy, $term->term_id );
						if ( empty( $background_image ) ) {
							$background_image = Helpers::get_image( $attributes['backgroundImageFallback'], $attributes['imageSize'] );
						}
						echo 'style="background-image: url(' . esc_url( $background_image ) . ')"';
					}
					?>
					>
					<?php
					if ( $attributes['linkContainer'] ) {
						printf(
							'<a href="%s" aria-label="%s" class="ptam-term-grid-anchor-full"></a>',
							esc_url( get_term_link( $term->term_id, $term->taxonomy ) ),
							esc_attr( $term->name )
						);
					}
					?>
					<div class="ptam-term-grid-item-content">
						<?php
						if ( $attributes['showTermTitle'] ) {
							echo '<h2>';
							if ( $attributes['linkTermTitle'] && ! $attributes['linkContainer'] ) {
								$term_link = get_term_link( $term->term_id, $term->taxonomy );
								printf(
									'<a href="%s">%s</a>',
									esc_url( $term_link ),
									esc_html( $term->name )
								);
							} else {
								echo esc_html( $term->name );
							}
							echo '</h2>';
						}
						if ( $attributes['showTermDescription'] ) {
							?>
							<div class="ptam-term-grid-item-description">
								<?php echo wp_kses_post( $term->description ); ?>
							</div>
							<?php
						}
						if ( ! $attributes['linkContainer'] && $attributes['showButton'] ) {
							?>
							<a href="<?php echo esc_url( get_term_link( $term->term_id, $term->taxonomy ) ); ?>" class="ptam-term-grid-button btn button"><?php echo esc_html( $attributes['termButtonText'] ); ?></a>
							<?php
						}
						?>
					</div>
				</div>
				<?php
			}
			?>
		</div>
		<?php
		/**
		 * Override the term grid output.
		 *
		 * @since 4.0.0
		 *
		 * @param string $html             The grid HTML.
		 * @param array  $attributes       The passed and sanitized attributes.
		 * @param array  $raw_term_results The term results to show.
		 * @param string $taxonomy         The taxonomy to return the terms for.
		 */
		return apply_filters( 'ptam_term_grid_output', ob_get_clean(), $attributes, $raw_term_results, $taxonomy );
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
			Helpers::get_plugin_dir( 'build/block/term-grid/block.json' ),
			array( 'render_callback' => array( $this, 'term_grid' ) ),
		);
	}
}
