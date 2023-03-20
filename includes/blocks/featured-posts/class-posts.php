<?php
/**
 * Featured Posts Block.
 *
 * @package PTAM
 */

namespace MediaRon\PTAM\Blocks\Featured_Posts;

use MediaRon\PTAM\Helpers;

/**
 * Featured Posts Block
 */
class Posts {

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
	public function output( $attributes ) {
		ob_start();

		// Get taxonomy.
		$taxonomy = sanitize_text_field( $attributes['taxonomy'] );
		$term     = absint( $attributes['term'] );

		// Get oroder and orderby.
		$orderby = isset( $attributes['orderBy'] ) ? sanitize_text_field( $attributes['orderBy'] ) : '';
		$order   = isset( $attributes['order'] ) ? sanitize_text_field( $attributes['order'] ) : '';

		// Get post type.
		$post_type = isset( $attributes['postType'] ) ? sanitize_text_field( $attributes['postType'] ) : 'post';

		// Get posts per page.
		$posts_per_page = isset( $attributes['postsToShow'] ) ? absint( $attributes['postsToShow'] ) : 2;

		// Build Query.
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
			'post_type'      => $post_type,
			'post_status'    => 'publish',
			'order'          => $order,
			'orderby'        => $orderby,
			'posts_per_page' => $posts_per_page,
			'paged'          => $paged,
		);
		if ( 'all' !== $term && 0 !== absint( $term ) && 'none' !== $taxonomy ) {
			$post_args['tax_query'] = array( // phpcs:ignore
			array(
				'taxonomy' => $taxonomy,
				'terms'    => $term,
			),
			);
		}

		$attributes['taxonomy']           = Helpers::sanitize_attribute( $attributes, 'align', 'text' );
		$attributes['postType']           = Helpers::sanitize_attribute( $attributes, 'postType', 'text' );
		$attributes['postLayout']         = Helpers::sanitize_attribute( $attributes, 'postLayout', 'text' );
		$attributes['displayPostContent'] = Helpers::sanitize_attribute( $attributes, 'displayPostContent', 'bool' );
		$attributes['term']               = Helpers::sanitize_attribute( $attributes, 'term', 'int' );
		$attributes['order']              = Helpers::sanitize_attribute( $attributes, 'order', 'text' );
		$attributes['orderBy']            = Helpers::sanitize_attribute( $attributes, 'orderBy', 'text' );
		$attributes['align']              = Helpers::sanitize_attribute( $attributes, 'align', 'text' );
		$attributes['imageTypeSize']      = Helpers::sanitize_attribute( $attributes, 'imageTypeSize', 'text' );
		$attributes['postsToShow']        = Helpers::sanitize_attribute( $attributes, 'postsToShow', 'int' );
		if ( is_array( $attributes['fallbackImg'] ) ) {
			if ( isset( $attributes['fallbackImg']['id'] ) ) {
				$attributes['fallbackImg'] = $attributes['fallbackImg']['id'];
				$attributes['fallbackImg'] = Helpers::sanitize_attribute( $attributes, 'fallbackImg', 'int' );
			} else {
				$attributes['fallbackImg'] = 0;
			}
		} else {
			$attributes['fallbackImg'] = 0;
		}
		$attributes['termDisplayPaddingLeft']             = Helpers::sanitize_attribute( $attributes, 'termDisplayPaddingLeft', 'int' );
		$attributes['termDisplayPaddingRight']            = Helpers::sanitize_attribute( $attributes, 'termDisplayPaddingRight', 'int' );
		$attributes['termDisplayPaddingBottom']           = Helpers::sanitize_attribute( $attributes, 'termDisplayPaddingBottom', 'int' );
		$attributes['termBackgroundColor']                = Helpers::sanitize_attribute( $attributes, 'termBackgroundColor', 'text' );
		$attributes['termTextColor']                      = Helpers::sanitize_attribute( $attributes, 'termTextColor', 'text' );
		$attributes['termFont']                           = Helpers::sanitize_attribute( $attributes, 'termFont', 'text' );
		$attributes['termTitle']                          = Helpers::sanitize_attribute( $attributes, 'termTitle', 'text' );
		$attributes['titleFont']                          = Helpers::sanitize_attribute( $attributes, 'titleFont', 'text' );
		$attributes['titleFontSize']                      = Helpers::sanitize_attribute( $attributes, 'titleFontSize', 'int' );
		$attributes['titleColor']                         = Helpers::sanitize_attribute( $attributes, 'titleColor', 'text' );
		$attributes['titleColorHover']                    = Helpers::sanitize_attribute( $attributes, 'titleColorHover', 'text' );
		$attributes['containerId']                        = Helpers::sanitize_attribute( $attributes, 'containerId', 'text' );
		$attributes['disableStyles']                      = Helpers::sanitize_attribute( $attributes, 'disableStyles', 'bool' );
		$attributes['showMeta']                           = Helpers::sanitize_attribute( $attributes, 'showMeta', 'bool' );
		$attributes['showMetaAuthor']                     = Helpers::sanitize_attribute( $attributes, 'showMetaAuthor', 'bool' );
		$attributes['showMetaDate']                       = Helpers::sanitize_attribute( $attributes, 'showMetaDate', 'bool' );
		$attributes['showMetaComments']                   = Helpers::sanitize_attribute( $attributes, 'showMetaComments', 'bool' );
		$attributes['showFeaturedImage']                  = Helpers::sanitize_attribute( $attributes, 'showFeaturedImage', 'bool' );
		$attributes['showReadMore']                       = Helpers::sanitize_attribute( $attributes, 'showReadMore', 'bool' );
		$attributes['showExcerpt']                        = Helpers::sanitize_attribute( $attributes, 'showExcerpt', 'bool' );
		$attributes['excerptFont']                        = Helpers::sanitize_attribute( $attributes, 'excerptFont', 'text' );
		$attributes['excerptLength']                      = Helpers::sanitize_attribute( $attributes, 'excerptLength', 'int' );
		$attributes['excerptFontSize']                    = Helpers::sanitize_attribute( $attributes, 'excerptFontSize', 'int' );
		$attributes['excerptTextColor']                   = Helpers::sanitize_attribute( $attributes, 'excerptTextColor', 'text' );
		$attributes['readMoreButtonText']                 = Helpers::sanitize_attribute( $attributes, 'readMoreButtonText', 'text' );
		$attributes['readMoreButtonFont']                 = Helpers::sanitize_attribute( $attributes, 'readMoreButtonFont', 'text' );
		$attributes['readMoreButtonTextColor']            = Helpers::sanitize_attribute( $attributes, 'readMoreButtonTextColor', 'text' );
		$attributes['readMoreButtonTextHoverColor']       = Helpers::sanitize_attribute( $attributes, 'readMoreButtonTextHoverColor', 'text' );
		$attributes['readMoreButtonBackgroundColor']      = Helpers::sanitize_attribute( $attributes, 'readMoreButtonBackgroundColor', 'text' );
		$attributes['readMoreButtonBackgroundHoverColor'] = Helpers::sanitize_attribute( $attributes, 'readMoreButtonBackgroundHoverColor', 'text' );
		$attributes['readMoreButtonBorder']               = Helpers::sanitize_attribute( $attributes, 'readMoreButtonBorder', 'int' );
		$attributes['readMoreButtonBorderColor']          = Helpers::sanitize_attribute( $attributes, 'readMoreButtonBorderColor', 'text' );
		$attributes['readMoreButtonBorderRadius']         = Helpers::sanitize_attribute( $attributes, 'readMoreButtonBorderRadius', 'int' );
		$attributes['showPagination']                     = Helpers::sanitize_attribute( $attributes, 'showPagination', 'bool' );

		/**
		 * Filter the post query.
		 *
		 * @since 4.5.0
		 *
		 * @param array  $post_args  The post arguments.
		 * @param array  $attributes The passed attributes.
		 * @param string $post_type  The post type.
		 * @param int    $term       The term ID.
		 * @param string $taxonomy   The taxonomy.
		 */
		$post_args = apply_filters( 'ptam_featured_post_by_term_query', $post_args, $attributes, $post_type, $term, $taxonomy );
		// Front page pagination fix.
		$recent_posts     = new \WP_Query( $post_args ); // phpcs:ignore
		?>
		<div class="ptam-fp-wrapper" id="<?php echo esc_attr( $attributes['containerId'] ); ?>">
		<?php
		if ( ! $attributes['disableStyles'] ) :
			?>
		<style>
			#<?php echo esc_html( $attributes['containerId'] ); ?> .ptam-fp-term {
				border-bottom: 2px solid <?php echo esc_html( $attributes['termBackgroundColor'] ); ?>;
				marginBottom: 20px;
			}
			#<?php echo esc_html( $attributes['containerId'] ); ?> .ptam-fp-term span {
				padding-bottom: <?php echo absint( $attributes['termDisplayPaddingBottom'] ); ?>px;
				padding-top: <?php echo absint( $attributes['termDisplayPaddingTop'] ); ?>px;
				padding-left: <?php echo absint( $attributes['termDisplayPaddingLeft'] ); ?>px;
				padding-right: <?php echo absint( $attributes['termDisplayPaddingRight'] ); ?>px;
				background-color: <?php echo esc_html( $attributes['termBackgroundColor'] ); ?>;
				color: <?php echo esc_html( $attributes['termTextColor'] ); ?>;
				font-family: '<?php echo esc_html( $attributes['termFont'] ); ?>';
				font-size: <?php echo absint( $attributes['termFontSize'] ); ?>px;
			}
			#<?php echo esc_html( $attributes['containerId'] ); ?> .ptam-fp-term span {
				padding-bottom: <?php echo absint( $attributes['termDisplayPaddingBottom'] ); ?>px;
				padding-top: <?php echo absint( $attributes['termDisplayPaddingTop'] ); ?>px;
				padding-left: <?php echo absint( $attributes['termDisplayPaddingLeft'] ); ?>px;
				padding-right: <?php echo absint( $attributes['termDisplayPaddingRight'] ); ?>px;
				background-color: <?php echo esc_html( $attributes['termBackgroundColor'] ); ?>;
				color: <?php echo esc_html( $attributes['termTextColor'] ); ?>;
				font-family: '<?php echo esc_html( $attributes['termFont'] ); ?>';
				font-size: <?php echo absint( $attributes['termFontSize'] ); ?>px;
			}
			#<?php echo esc_html( $attributes['containerId'] ); ?> .entry-title a {
				font-family: '<?php echo esc_html( $attributes['titleFont'] ); ?>';
				font-size: <?php echo absint( $attributes['titleFontSize'] ); ?>px;
				color: <?php echo esc_html( $attributes['titleColor'] ); ?>;
			}
			#<?php echo esc_html( $attributes['containerId'] ); ?> .entry-title a:hover {
				color: <?php echo esc_html( $attributes['titleColorHover'] ); ?>;
			}
			#<?php echo esc_html( $attributes['containerId'] ); ?> .ptam-featured-post-content {
				font-family: '<?php echo esc_html( $attributes['excerptFont'] ); ?>';
				color: <?php echo esc_html( $attributes['excerptTextColor'] ); ?>;
				font-size: <?php echo absint( $attributes['excerptFontSize'] ); ?>px;
			}
			#<?php echo esc_html( $attributes['containerId'] ); ?> .ptam-featured-post-content {
				font-family: '<?php echo esc_html( $attributes['excerptFont'] ); ?>';
				color: <?php echo esc_html( $attributes['excerptTextColor'] ); ?>;
				font-size: <?php echo absint( $attributes['excerptFontSize'] ); ?>px;
			}
			#<?php echo esc_html( $attributes['containerId'] ); ?> .ptam-featured-post-button a {
				color: <?php echo esc_html( $attributes['readMoreButtonTextColor'] ); ?>;
				background-color: <?php echo esc_html( $attributes['readMoreButtonBackgroundColor'] ); ?>;
				border-width: <?php echo absint( $attributes['readMoreButtonBorder'] ); ?>px;
				border-radius: <?php echo absint( $attributes['readMoreButtonBorderRadius'] ); ?>px;
				border-color: <?php echo esc_attr( $attributes['readMoreButtonBorderColor'] ); ?>;
				font-family: '<?php echo esc_html( $attributes['readMoreButtonFont'] ); ?>';
				border-style: solid;
			}
			#<?php echo esc_html( $attributes['containerId'] ); ?> .ptam-featured-post-button a:hover {
				color: <?php echo esc_html( $attributes['readMoreButtonTextHoverColor'] ); ?>;
				background-color: <?php echo esc_html( $attributes['readMoreButtonBackgroundHoverColor'] ); ?>;
			}
		</style>
			<?php
		endif;

		$term_name   = _x( 'All', 'All Terms', 'post-type-archive-mapping' );
		$term_object = get_term_by( 'id', $term, $taxonomy );
		if ( ! is_wp_error( $term_object ) && 'all' !== $term && $term ) {
			if ( isset( $term_object->name ) ) {
				$term_name = sanitize_text_field( $term_object->name );
			}
			if ( ! empty( $attributes['termTitle'] ) ) {
				$term_name = $attributes['termTitle'];
			}
		} else {
			if ( ! empty( $attributes['termTitle'] ) ) {
				$term_name = $attributes['termTitle'];
			}
		}
		?>
		<h4 class="ptam-fp-term"><span><?php echo esc_html( $term_name ); ?></span></h4>
		<?php
		if ( $recent_posts->have_posts() ) :
			while ( $recent_posts->have_posts() ) {
				global $post;
				$recent_posts->the_post();
				$thumbnail = get_the_post_thumbnail( $post->ID, $attributes['imageTypeSize'] );
				if ( empty( $thumbnail ) ) {
					$thumbnail = wp_get_attachment_image( $attributes['fallbackImg'], $attributes['imageTypeSize'] );
				}
				$post->featured_image_src = $thumbnail;

				// Get author information.
				$display_name = get_the_author_meta( 'display_name', $post->post_author );
				$author_url   = get_author_posts_url( $post->post_author );

				$post->author_info               = new \stdClass();
				$post->author_info->display_name = $display_name;
				$post->author_info->author_link  = $author_url;

				$post->link = get_permalink( $post->ID );

				$post_excerpt = get_the_excerpt();

				$post->post_excerpt = wp_kses_post( wp_trim_words( $post->post_excerpt, absint( $attributes['excerptLength'] ) ) );

				?>
				<div class="ptam-featured-post-item">
					<div class="ptam-featured-post-meta">
						<h3 class="entry-title"><a href="<?php echo esc_attr( esc_url( $post->link ) ); ?>"><?php echo wp_kses_post( get_the_title( $post ) ); ?></a></h3>
						<?php
						if ( $attributes['showMeta'] ) :
							?>
							<div class="entry-meta">
								<?php
								if ( $attributes['showMetaAuthor'] ) :
									?>
									<span class="author-name"><a href="<?php echo esc_attr( esc_url( $post->author_info->author_link ) ); ?>"><?php echo esc_html( $post->author_info->display_name ); ?></a></span>
									<?php
								endif;
								if ( $attributes['showMetaDate'] ) :
									?>
									<span class="post-date">
										<time
											datetime="<?php echo esc_attr( get_the_date( 'c', $post->ID ) ); ?>"
											class="ptam-block-post-grid-date"
										>
										<?php echo esc_html( get_the_date( '', $post->ID ) ); ?>
										</time>
									</span>
									<?php
								endif;
								if ( $attributes['showMetaComments'] ) :
									?>
									<span class="post-comments">
										<?php echo absint( $post->comment_count ); ?> <?php echo esc_html( _n( 'Comment', 'Comments', $post->comment_count, 'post-type-archive-mapping' ) ); ?>
									</span>
									<?php
								endif;
								?>
							</div><!-- .entry-meta -->
							<?php
							endif;
						?>
					</div><!-- .ptam-featured-post-meta -->
					<?php
					if ( $attributes['showFeaturedImage'] && ! empty( $post->featured_image_src ) ) :
						?>
						<div class="ptam-featured-post-image">
							<a href="<?php echo esc_attr( esc_url( get_permalink( $post->ID ) ) ); ?>">
								<?php echo wp_kses_post( $post->featured_image_src ); ?>
							</a>
						</div>
						<?php
					endif;
					?>
					<?php
					if ( $attributes['showExcerpt'] ) {
						$post->post_excerpt = html_entity_decode( $post->post_excerpt, ENT_QUOTES, get_option( 'blog_charset' ) );
						?>
						<div class="ptam-featured-post-content">
							<?php echo esc_html( $post_excerpt ); ?>
						</div>
						<?php
					}
					?>
					<?php
					if ( $attributes['showReadMore'] ) {
						$permalink = get_permalink();
						?>
						<div class="ptam-featured-post-button">
							<a class="btn button" href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $attributes['readMoreButtonText'] ); ?></a>
						</div>
						<?php
					}
					?>
				</div><!-- .ptam-featured-post-item -->
				<?php
			}
		endif;
		if ( ! is_wp_error( $attributes['showPagination'] ) && $attributes['showPagination'] ) {
			$pagination = paginate_links(
				array(
					'total'        => $recent_posts->max_num_pages,
					'current'      => $paged,
					'format'       => 'page/%#%',
					'show_all'     => false,
					'type'         => 'list',
					'end_size'     => 4,
					'mid_size'     => 4,
					'prev_next'    => false,
					'prev_text'    => sprintf( '<i></i> %1$s', __( 'Newer Items', 'post-type-archive-mapping' ) ),
					'next_text'    => sprintf( '%1$s <i></i>', __( 'Older Items', 'post-type-archive-mapping' ) ),
					'add_args'     => false,
					'add_fragment' => '',
				)
			);
			echo wp_kses_post( '<div class="ptam-pagination">' . $pagination . '</div>' );
		}
		$wp_query = $temp; // phpcs:ignore
		?>
		</div><!-- .ptam-fp-wrapper -->
		<?php
		/**
		 * Override the Featured Posts Output.
		 *
		 * @since 4.5.0
		 *
		 * @param string $html             The grid HTML.
		 * @param array  $attributes       The passed and sanitized attributes.
		 */
		return apply_filters( 'ptam_featured_posts_by_term_output', ob_get_clean(), $attributes );
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
			Helpers::get_plugin_dir( 'build/block/featured-posts/block.json' ),
			array( 'render_callback' => array( $this, 'output' ) ),
		);
	}
}
