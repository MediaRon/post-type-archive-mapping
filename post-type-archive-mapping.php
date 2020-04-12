<?php // phpcs:ignore
/*
Plugin Name: Custom Post Types Block
Plugin URI: https://mediaron.com/custom-post-types-block/
Description: Map your post type archives to a page and use our Gutenberg blocks to show posts or terms.
Author: MediaRon LLC
Version: 4.0.1
Requires at least: 5.3
Author URI: https://mediaron.com
Contributors: MediaRon LLC
Text Domain: post-type-archive-mapping
Domain Path: /languages
Credit: Forked from https://github.com/bigwing/post-type-archive-mapping
Credit: Gutenberg block based on Atomic Blocks
*/
define( 'PTAM_VERSION', '4.0.1' );
require_once 'autoloader.php';

/**
 * Main plugin class.
 */
class PostTypeArchiveMapping {
	/**
	 * Holds the class instance.
	 *
	 * @var PostTypeArchiveMapping $instance
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
	 * Return an instance of the PostTypeArchiveMapping Class.
	 *
	 * @since 1.0.0
	 * @access public static
	 *
	 * @return PostTypeArchiveMapping class instance.
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
		load_plugin_textdomain( 'post-type-archive-mapping', false, basename( dirname( __FILE__ ) ) . '/languages' );

		// Register scripts/styles for the plugin.
		$this->enqueue = new PTAM\Includes\Enqueue();
		$this->enqueue->run();

		// Register REST for the plugin.
		$this->rest = new PTAM\Includes\Rest\Rest();
		$this->rest->run();

		// Register Custom Post Type Block.
		$this->cpt_block_one = new PTAM\Includes\Blocks\Custom_Post_Types\Custom_Post_Types();
		$this->cpt_block_one->run();

		// Register Custom Post Type Block.
		$this->term_grid = new PTAM\Includes\Blocks\Term_Grid\Terms();
		$this->term_grid->run();

		// Page columns.
		$this->page_columns = new PTAM\Includes\Admin\Page_Columns();
		$this->page_columns->run();
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
		// Admin Settings.
		add_action( 'admin_init', array( $this, 'init_admin_settings' ) );
		add_action( 'pre_get_posts', array( $this, 'maybe_override_archive' ) );

		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
	} //end init

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
			$term_meta = get_post_meta( $object_id, '_term_mapped', true );
			if ( $post_meta || $term_meta ) {
				if ( $post_meta && $term_meta ) { // phpcs:ignore
					// Fail silently as this will cause a redirect loop.
				} elseif ( $post_meta && ! get_query_var( 'redirected' ) ) {
					wp_safe_redirect( get_post_type_archive_link( $post_meta ) );
					exit;
				} elseif ( $term_meta && ! get_query_var( 'redirected' ) ) {
					wp_safe_redirect( get_term_link( absint( $term_meta ) ) );
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
					$query->set( 'post_type', 'page' );
					$query->set( 'page_id', $post_id );
					$query->set( 'redirected', true );
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
			if ( $post_id && 'default' !== $post_id ) {
				$post_id = absint( $post_id );
				$query->set( 'post_type', 'page' );
				$query->set( 'page_id', $post_id );
				$query->set( 'redirected', true );
				$query->set( 'paged', $this->paged );
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
		$dir = rtrim( plugin_dir_path( __FILE__ ), '/' );
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
		$dir = rtrim( plugin_dir_url( __FILE__ ), '/' );
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

		add_settings_section( 'post-type-archive-mapping', _x( 'Post Type Archive Mapping', 'plugin settings heading', 'post-type-archive-mapping' ), array( $this, 'settings_section' ), 'reading' );

		add_settings_field(
			'post-type-archive-mapping',
			__( 'Post Type Archive Mapping', 'post-type-archive-mapping' ),
			array( $this, 'add_settings_post_types' ),
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
		$posts      = get_posts(
			array(
				'post_status'    => array( 'publish' ),
				'posts_per_page' => 200, // phpcs:ignore
				'post_type'      => 'page',
				'orderby'        => 'name',
				'order'          => 'ASC',
			)
		);
		$post_types = get_post_types(
			array(
				'public'      => true,
				'has_archive' => true,
			)
		);
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
			<p><strong><?php echo esc_html( $post_type_label ); ?></strong></p>
			<select name="post-type-archive-mapping[<?php echo esc_html( $post_type ); ?>]">
				<option value="default"><?php esc_html_e( 'Default', 'post-type-archive-mapping' ); ?></option>
				<?php
				foreach ( $posts as $post ) {
					printf( '<option value="%d" %s>%s</option>', absint( $post->ID ), selected( $selection, $post->ID, false ), esc_html( $post->post_title ) );
				}
				?>
			</select>
			<?php
		}
		?>
		<?php
	}

	/**
	 * Map Term Archives to Posts Options.
	 *
	 * @param object $tag The term object.
	 * @param string $taxonomy The taxonomy.
	 */
	public function map_term_interface( $tag, $taxonomy = '' ) {
		$posts   = get_posts(
			array(
				'post_status'    => array( 'publish' ),
				'posts_per_page' => 200, // phpcs:ignore
				'post_type'      => 'page',
				'orderby'        => 'name',
				'order'          => 'ASC',
			)
		);
		$post_id = get_term_meta( $tag->term_id, '_term_archive_mapping', true );
		?>
		<h2><?php esc_html_e( 'Archive Mapping', 'post-type-archive-mapping' ); ?></h2>
		<select name="term_post_type">
			<option value="default"><?php esc_html_e( 'Default', 'post-type-archive-mapping' ); ?></option>
			<?php
			foreach ( $posts as $post ) {
				printf( '<option value="%d" %s>%s</option>', absint( $post->ID ), selected( $post_id, $post->ID, false ), esc_html( $post->post_title ) );
			}
			?>
		</select>
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
			if ( $maybe_post_id ) {
				// Check if post is already mapped.
				$maybe_mapped = get_post_meta( $maybe_post_id, '_post_type_mapped', true );
				if ( $maybe_mapped ) {
					update_option( 'ptam_error_message', __( 'The page you selected to map to a term is already mapped.', 'post-type-archive-mapping' ) );
				} else {
					update_post_meta( $maybe_post_id, '_term_mapped', $term_id );
					update_term_meta( $term_id, '_term_archive_mapping', $maybe_post_id );
				}
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

add_action(
	'plugins_loaded',
	function() {
		PostTypeArchiveMapping::get_instance();
	}
);

global $wp_embed;
add_filter( 'ptam_the_content', array( $wp_embed, 'run_shortcode' ), 8 );
add_filter( 'ptam_the_content', array( $wp_embed, 'autoembed' ), 8 );
add_filter( 'ptam_the_content', 'wptexturize' );
add_filter( 'ptam_the_content', 'convert_chars' );
add_filter( 'ptam_the_content', 'wpautop' );
add_filter( 'ptam_the_content', 'shortcode_unautop' );
add_filter( 'ptam_the_content', 'do_shortcode' );
