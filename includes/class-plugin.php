<?php

namespace MediaRon\PTAM;

use MediaRon\PTAM\Admin\Options;

/**
 * Main plugin class.
 */
class Plugin {
	/**
	 * Holds the class instance.
	 *
	 * @var Plugin $instance
	 */
	private static $instance = null;

	/**
	 * Holds the paged argument.
	 *
	 * @var int $paged
	 */
	private $paged = null;

	/**
	 * Holds the paged reset argument.
	 *
	 * @var bool $paged_reset
	 */
	private $paged_reset = false;

	/**
	 * Return an instance of the class
	 *
	 * Return an instance of the Plugin class.
	 *
	 * @since 1.0.0
	 * @access public static
	 *
	 * @return Plugin class instance.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	} //end get_instance

	/**
	 * Class constructor.
	 *
	 * Initialize plugin and load text domain for internationalization
	 *
	 * @since 1.0.0
	 * @access private
	 */
	private function __construct() {
		add_action( 'init', array( $this, 'init' ), 9 );
		load_plugin_textdomain( 'post-type-archive-mapping', false, basename( dirname( PTAM_FILE ) ) . '/languages' );

		// Register scripts/styles for the plugin.
		$this->enqueue = new Enqueue();
		$this->enqueue->run();

		// Run if blocks are enabled.
		if ( false === Options::is_blocks_disabled() ) {
			// Register REST for the plugin.
			$this->rest = new Rest\Rest();
			$this->rest->run();

			// Register Custom Post Type Block.
			$this->cpt_block_one = new Blocks\Custom_Post_Types\Custom_Post_Types();
			$this->cpt_block_one->run();

			// Register Term Grid Block.
			$this->term_grid = new Blocks\Term_Grid\Terms();
			$this->term_grid->run();

			// Register Featured Post Block.
			$this->featured_posts = new Blocks\Featured_Posts\Posts();
			$this->featured_posts->run();

			// Gutenberg Helper which sets the block categories.
			$this->gutenberg = new Admin\Gutenberg();
			$this->gutenberg->run();
		}

		// Run if page columns are enabled.
		if ( false === Options::is_page_columns_disabled() && false === Options::is_archive_mapping_disabled() ) {
			// Page columns.
			$this->page_columns = new Admin\Page_Columns();
			$this->page_columns->run();
		}

		// Yoast Compatibility.
		$this->yoast = new Yoast();
		$this->yoast->run();

		// Admin settings.
		$this->admin_settings = new Admin\Admin_Settings();
	} //end constructor

	/**
	 * Main plugin initialization
	 *
	 * Initialize admin menus, options,and scripts
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @see __construct
	 */
	public function init() {

		// Check if archive mapping is disabled.
		if ( false === Options::is_archive_mapping_disabled() ) {
			// Archive mapping settings.
			add_action( 'admin_init', array( $this, 'init_admin_settings' ) );
			add_action( 'pre_get_posts', array( $this, 'maybe_override_archive' ) );

			// Output admin notices once when saving archive mapping.
			add_action( 'admin_notices', array( $this, 'admin_notices' ) );

			// 404 page detection.
			add_filter( 'template_include', array( $this, 'maybe_force_404_template' ), 1 );
		}
	} //end init

	/**
	 * This is a catch-all. Any 404 error not caught will be directed here.
	 * If a 404 error is caught, will load the page template instead.
	 *
	 * @param string $template The regular template.
	 *
	 * @return string $template The updated template.
	 */
	public function maybe_force_404_template( $template ) {
		if ( is_404() ) {
			$page_id_404 = absint( get_option( 'post-type-archive-mapping-404', 0 ) );
			if ( $page_id_404 > 0 ) {
				$args = array(
					'post_type'      => 'page',
					'page_id'        => $page_id_404,
					'post_status'    => 'publish',
					'posts_per_page' => 1,
				);
				/* I wise woman once told me to never use query_posts. Like NEVER. I had no choice here. */
				query_posts( // phpcs:ignore
					$args
				);
				return get_page_template();
			}
		}
		return $template;
	}

	/**
	 * Add admin notices when things go wrong.
	 *
	 * @since 3.3.5
	 */
	public function admin_notices() {
		// Check for any term errors.
		if ( get_option( 'ptam_error_message', '' ) ) {
			printf(
				'<div class="notice error"><strong><p>%s</p></strong></div>',
				esc_html( get_option( 'ptam_error_message', '' ) )
			);
			delete_option( 'ptam_error_message' );
		}
	}

	/**
	 * Override an archive page based on passed query arguments.
	 *
	 * @param WP_Query $query The query to check.
	 */
	public function maybe_override_archive( $query ) {
		if ( is_admin() ) {
			return $query;
		}
		// Maybe Redirect.
		if ( is_page() ) {
			$object_id = get_queried_object_id();
			$post_meta = get_post_meta( $object_id, '_post_type_mapped', true );
			if ( $post_meta ) {
				if ( $post_meta && ! get_query_var( 'redirected' ) ) {
					wp_safe_redirect( get_post_type_archive_link( $post_meta ) );
					exit;
				}
			} else {
				if ( get_query_var( 'paged' ) ) {
					$query->set( 'paged', get_query_var( 'paged' ) );
				}
				return;
			}
		}

		// trigger this once after running the main query.
		if ( true === $this->paged_reset ) {
			$query->set( 'paged', $this->paged );
			set_query_var( 'paged', $this->paged );

			$this->paged_reset = false;
		}

		$post_types = get_option( 'post-type-archive-mapping', array() );
		if ( empty( $post_types ) && is_admin() && ! is_tax() ) {
			return;
		}

		// trigger this the first time to get the current page.
		if ( is_null( $this->paged ) ) {
			$this->paged = get_query_var( 'paged' );
		}
		if ( is_array( $post_types ) && ! empty( $post_types ) ) {
			foreach ( $post_types as $post_type => $post_id ) {
				if ( is_post_type_archive( $post_type ) && 'default' !== $post_id && $query->is_main_query() ) {
					$post_id = absint( $post_id );
					$post_id = apply_filters( 'wpml_object_id', $post_id, 'page', true );
					$query->set( 'post_type', 'page' );
					$query->set( 'page_id', $post_id );
					$query->set( 'redirected', true );
					$query->set( 'original_archive_type', 'page' );
					$query->set( 'original_archive_id', $post_type );
					$query->set( 'term_tax', '' );
					$query->set( 'paged', $this->paged );
					$query->is_archive           = false;
					$query->is_single            = true;
					$query->is_singular          = true;
					$query->is_post_type_archive = false;
					$this->paged_reset           = true;
				}
			}
		}
		if ( is_tax() || $query->is_category || $query->is_tag ) {
			$post_id = get_term_meta( get_queried_object_id(), '_term_archive_mapping', true );
			$term    = get_queried_object();
			if ( $post_id && 'default' !== $post_id ) {
				$post_id = absint( $post_id );
				$query->set( 'post_type', 'page' );
				$query->set( 'page_id', $post_id );
				$query->set( 'redirected', true );
				$query->set( 'paged', $this->paged );
				$query->set( 'original_archive_type', 'term' );
				$query->set( 'original_archive_id', absint( $term->term_id ) );
				$query->set( 'term_tax', sanitize_text_field( $term->taxonomy ) );
				$query->is_page              = true;
				$query->is_archive           = false;
				$query->is_category          = false;
				$query->is_tag               = false;
				$query->is_tax               = false;
				$query->is_single            = true;
				$query->is_singular          = true;
				$query->is_post_type_archive = false;
				$query->queried_object_id    = $post_id;

				$query->queried_object = get_post( $post_id, OBJECT );
				$this->paged_reset     = true;
			}
		}
	}

	/**
	 * Get an absolute path for a plugin asset.
	 *
	 * @param string $path The relative path to the asset.
	 */
	public static function get_plugin_dir( $path = '' ) {
		$dir = rtrim( plugin_dir_path( PTAM_FILE ), '/' );
		if ( ! empty( $path ) && is_string( $path ) ) {
			$dir .= '/' . ltrim( $path, '/' );
		}
		return $dir;
	}

	/**
	 * Get an absolute path for a plugin asset.
	 *
	 * @param string $path The relative path to the asset.
	 */
	public static function get_plugin_url( $path = '' ) {
		$dir = rtrim( plugin_dir_url( PTAM_FILE ), '/' );
		if ( ! empty( $path ) && is_string( $path ) ) {
			$dir .= '/' . ltrim( $path, '/' );
		}
		return $dir;
	}

	/**
	 * Save post meta if selected on the reading screen.
	 *
	 * @param array $args Post Type arguments.
	 */
	public function post_type_save( $args ) {
		if ( ! is_array( $args ) ) {
			return $args;
		}
		global $wpdb;
		$query = "delete from {$wpdb->postmeta} where meta_key = '_post_type_mapped'";
		$wpdb->query( $query ); // phpcs:ignore
		foreach ( $args as $post_type => $page_id ) {
			$maybe_mapped = get_post_meta( $page_id, '_term_mapped', true );
			if ( $maybe_mapped ) {
				update_option(
					'ptam_error_message',
					sprintf(
						/* Translators: %s is the page title */
						__( 'The page %s to map to a post type archive is already mapped to a term.', 'post-type-archive-mapping' ),
						esc_html( get_the_title( $page_id ) )
					)
				);
				unset( $args[ $post_type ] );
			} else {
				update_post_meta( $page_id, '_post_type_mapped', $post_type );
			}
		}
		return $args;
	}

	/**
	 * Initialize options
	 *
	 * Initialize page settings, fields, and sections and their callbacks
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @see init
	 */
	public function init_admin_settings() {

		// Get taxonomies.
		$taxonomies = get_taxonomies(
			array(
				'public' => true,
			),
			'objects'
		);
		foreach ( $taxonomies as $taxonomy ) {
			add_action( "{$taxonomy->name}_edit_form", array( $this, 'map_term_interface' ) );
		}
		add_action( 'edit_term', array( $this, 'save_mapped_term' ) );

		// Register post type mapping settings.
		register_setting(
			'reading',
			'post-type-archive-mapping',
			array(
				'sanitize_callback' => array( $this, 'post_type_save' ),
			)
		);
		register_setting(
			'reading',
			'post-type-archive-mapping-404',
			array(
				'sanitize_callback' => 'absint',
			)
		);

		add_settings_section( 'post-type-archive-mapping', _x( 'Page/Item Mapping', 'plugin settings heading', 'post-type-archive-mapping' ), array( $this, 'settings_section' ), 'reading' );

		add_settings_field(
			'post-type-archive-mapping',
			__( 'Post Type Archive Mapping', 'post-type-archive-mapping' ),
			array( $this, 'add_settings_post_types' ),
			'reading',
			'post-type-archive-mapping'
		);

		add_settings_field(
			'post-type-archive-mapping-404',
			__( '404 Page', 'post-type-archive-mapping' ),
			array( $this, 'add_settings_404_page' ),
			'reading',
			'post-type-archive-mapping'
		);

	}

	/**
	 * Add post type options to Settings->Reading screen.
	 *
	 * @param array $args Post Type arguments.
	 */
	public function add_settings_post_types( $args ) {
		$output     = get_option( 'post-type-archive-mapping', array() );
		$post_types = get_post_types(
			array(
				'public'      => true,
				'has_archive' => true,
			)
		);
		if ( empty( $post_types ) ) {
			?>
			<p><?php esc_html_e( 'There are no post types to map.', 'post-type-archive-mapping' ); ?></p>
			<?php
			return;
		}
		foreach ( $post_types as $index => $post_type ) {
			$selection = 'default';
			if ( isset( $output[ $post_type ] ) ) {
				$selection = $output[ $post_type ];
			}
			$post_type_label = $post_type;
			$post_type_data  = get_post_type_object( $post_type );
			if ( isset( $post_type_data->label ) && ! empty( $post_type_data->label ) ) {
				$post_type_label = $post_type_data->label;
			}
			?>
			<div class="ptam-admin-reading-cpt">
				<h3><?php esc_html_e( 'Post Type:', 'post-type-archive-mapping' ); ?> <?php echo esc_html( $post_type_label ); ?></h3>
				<?php
				wp_dropdown_pages(
					array(
						'selected'          => esc_attr( $selection ),
						'name'              => esc_html( "post-type-archive-mapping[{$post_type}]" ),
						'value_field'       => 'ID',
						'option_none_value' => 'default',
						'show_option_none'  => esc_html__( 'Default', 'post-type-archive-mapping' ),
					)
				);
				?>
			</div>
			<?php
		}
		?>
		<?php
	}

	/**
	 * Add 404 page options to Settings->Reading screen.
	 *
	 * @param array $args No idea what these are.
	 */
	public function add_settings_404_page( $args ) {
		$page_id_404 = get_option( 'post-type-archive-mapping-404', 0 );
		if ( ! $page_id_404 ) {
			$page_id_404 = -1;
		}
		?>
		<div class="ptam-admin-reading-cpt">
			<p class="description"><?php esc_html_e( 'Please select a page to map to your 404 page.', 'post-type-archive-mapping' ); ?></p>
			<?php
			wp_dropdown_pages(
				array(
					'selected'          => intval( $page_id_404 ),
					'name'              => 'post-type-archive-mapping-404',
					'value_field'       => 'ID',
					'option_none_value' => 'default',
					'show_option_none'  => esc_html__( 'Default', 'post-type-archive-mapping' ),
				)
			);
			?>
		</div>
		<?php
	}

	/**
	 * Map Term Archives to Posts Options.
	 *
	 * @param object $tag The term object.
	 * @param string $taxonomy The taxonomy.
	 */
	public function map_term_interface( $tag, $taxonomy = '' ) {
		$post_id = get_term_meta( $tag->term_id, '_term_archive_mapping', true );
		if ( ! $post_id ) {
			$post_id = -1;
		}
		?>
		<h2><?php esc_html_e( 'Archive Mapping', 'post-type-archive-mapping' ); ?></h2>
		<?php
		wp_dropdown_pages(
			array(
				'selected'          => intval( $post_id ),
				'name'              => 'term_post_type',
				'value_field'       => 'ID',
				'option_none_value' => 'default',
				'show_option_none'  => esc_html__( 'Default', 'post-type-archive-mapping' ),
			)
		);
		?>
		<p class="description"><?php esc_html_e( 'Map a term archive to a page.', 'post-type-archive-mapping' ); ?></p>
		<?php
	}

	/**
	 * Map a saved term to a term ID.
	 *
	 * @param int $term_id The term ID to map.
	 */
	public function save_mapped_term( $term_id ) {
		if ( current_user_can( 'edit_term', $term_id ) ) {
			$maybe_post_id = filter_input( INPUT_POST, 'term_post_type' );
			if ( 'default' === $maybe_post_id ) {
				delete_post_meta( $maybe_post_id, '_term_mapped' );
				delete_term_meta( $term_id, '_term_archive_mapping' );
			} elseif ( $maybe_post_id ) {
				update_post_meta( $maybe_post_id, '_term_mapped', $term_id );
				update_term_meta( $term_id, '_term_archive_mapping', $maybe_post_id );
			}
		}
	}

	/**
	 * Output settings HTML
	 *
	 * Output any HTML required to go into a settings section
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @see init_admin_settings
	 */
	public function settings_section() {
	}

}
