<?php
/**
 * Initialize various admin settings including the admin page, admin menu, and action links.
 *
 * @package PTAM
 */

namespace MediaRon\PTAM\Admin;

use MediaRon\PTAM\Helpers;

/**
 * Class Admin Settings.
 */
class Admin_Settings {

	/**
	 * Class constructor.
	 */
	public function __construct() {
		// For the admin interface.
		add_action( 'admin_menu', array( $this, 'register_settings_menu' ) );
		add_filter( 'plugin_action_links_' . Helpers::get_plugin_path(), array( $this, 'plugin_settings_link' ) );

		new \MediaRon\PTAM\Admin\Tabs\Settings();
		new \MediaRon\PTAM\Admin\Tabs\Support();
	}

	/**
	 * Adds a plugin settings link.
	 *
	 * Adds a plugin settings link.
	 *
	 * @param array $settings The settings array for the plugin.
	 *
	 * @return array Settings array.
	 */
	public function plugin_settings_link( $settings ) {
		$admin_settings_links = array();

		$admin_settings_links[] = sprintf(
			'<a href="%s">%s</a>',
			esc_url( Helpers::get_settings_url( 'settings' ) ),
			esc_html__( 'Settings', 'post-type-archive-mapping' )
		);
		$admin_settings_links[] = sprintf(
			'<a href="%s">%s</a>',
			esc_url( Helpers::get_settings_url( 'support' ) ),
			esc_html__( 'Support', 'post-type-archive-mapping' )
		);
		$admin_settings_links[] = sprintf(
			'<a href="%s" style="color: #f60098;" target="_blank">%s</a>',
			esc_url( PTAM_SPONSORS_URL ),
			esc_html__( 'Sponsor', 'post-type-archive-mapping' )
		);
		if ( ! is_array( $settings ) ) {
			return $admin_settings_links;
		} else {
			return array_merge( $settings, $admin_settings_links );
		}
	}

	/**
	 * Registers and outputs placeholder for settings.
	 *
	 * @since 1.0.0
	 */
	public static function settings_page() {
		?>
		<div class="wrap ptam-admin-wrap">
			<?php
			self::get_settings_header();
			self::get_settings_tabs();
			self::get_settings_footer();
			?>
		</div>
		<?php
	}

	/**
	 * Output the top-level admin tabs.
	 */
	public static function get_settings_tabs() {
		$settings_url_base = Helpers::get_settings_url( 'settings' )
		?>
			<?php
			$tabs = array();
			/**
			 * Filer the output of the tab output.
			 *
			 * Potentially modify or add your own tabs.
			 *
			 * @since 5.1.0
			 *
			 * @param array $tabs Associative array of tabs.
			 */
			$tabs       = apply_filters( 'ptam_admin_tabs', $tabs );
			$tab_html   = '<nav class="nav-tab-wrapper">';
			$tabs_count = count( $tabs );
			if ( $tabs && ! empty( $tabs ) && is_array( $tabs ) ) {
				$active_tab = Helpers::get_admin_tab();
				if ( null === $active_tab ) {
					$active_tab = 'settings';
				}
				$is_tab_match = false;
				if ( 'settings' === $active_tab ) {
					$active_tab = 'settings';
				} else {
					foreach ( $tabs as $tab ) {
						$tab_get = isset( $tab['get'] ) ? $tab['get'] : '';
						if ( $active_tab === $tab_get ) {
							$is_tab_match = true;
						}
					}
					if ( ! $is_tab_match ) {
						$active_tab = 'settings';
					}
				}
				$do_action = false;
				foreach ( $tabs as $tab ) {
					$classes = array( 'nav-tab' );
					$tab_get = isset( $tab['get'] ) ? $tab['get'] : '';
					if ( $active_tab === $tab_get ) {
						$classes[] = 'nav-tab-active';
						$do_action = isset( $tab['action'] ) ? $tab['action'] : false;
					} elseif ( ! $is_tab_match && 'setup' === $tab_get ) {
						$classes[] = 'nav-tab-active';
						$do_action = isset( $tab['action'] ) ? $tab['action'] : false;
					}
					$tab_url   = isset( $tab['url'] ) ? $tab['url'] : '';
					$tab_label = isset( $tab['label'] ) ? $tab['label'] : '';
					$tab_html .= sprintf(
						'<a href="%s" class="%s" id="ptam-%s"><span>%s</span></a>',
						esc_url( $tab_url ),
						esc_attr( implode( ' ', $classes ) ),
						esc_attr( $tab_get ),
						esc_html( $tab['label'] )
					);
				}
				$tab_html .= '</nav>';
				if ( $tabs_count > 0 ) {
					echo wp_kses( $tab_html, Helpers::get_kses_allowed_html() );
				}

				$current_tab     = Helpers::get_admin_tab();
				$current_sub_tab = Helpers::get_admin_sub_tab();

				/**
				 * Filer the output of the sub-tab output.
				 *
				 * Potentially modify or add your own sub-tabs.
				 *
				 * @since 5.1.0
				 *
				 * @param array $tabs Associative array of tabs.
				 * @param string $tab Tab
				 * @param string $sub_tab Sub Tab
				 */
				$sub_tabs = apply_filters( 'ptam_admin_sub_tabs', array(), $current_tab, $current_sub_tab );

				// Check to see if no tabs are available for this view.
				if ( null === $current_tab && null === $current_sub_tab ) {
					$current_tab = 'settings';
				}
				if ( $sub_tabs && ! empty( $sub_tabs ) && is_array( $sub_tabs ) ) {
					if ( null === $current_sub_tab ) {
						$current_sub_tab = '';
					}
					$is_tab_match      = false;
					$first_sub_tab     = current( $sub_tabs );
					$first_sub_tab_get = $first_sub_tab['get'];
					if ( $first_sub_tab_get === $current_sub_tab ) {
						$active_tab = $current_sub_tab;
					} else {
						$active_tab = $current_sub_tab;
						foreach ( $sub_tabs as $tab ) {
							$tab_get = isset( $tab['get'] ) ? $tab['get'] : '';
							if ( $active_tab === $tab_get ) {
								$is_tab_match = true;
							}
						}
						if ( ! $is_tab_match ) {
							$active_tab = $first_sub_tab_get;
						}
					}
					$sub_tab_html_array = array();
					$do_subtab_action   = false;
					$maybe_sub_tab      = '';
					foreach ( $sub_tabs as $sub_tab ) {
						$classes = array( 'ptam-sub-tab' );
						$tab_get = isset( $sub_tab['get'] ) ? $sub_tab['get'] : '';
						if ( $active_tab === $tab_get ) {
							$classes[]        = 'ptam-sub-tab-active';
							$do_subtab_action = true;
							$current_sub_tab  = $tab_get;
						} elseif ( ! $is_tab_match && $first_sub_tab_get === $tab_get ) {
							$classes[]        = 'ptam-sub-tab-active';
							$do_subtab_action = true;
							$current_sub_tab  = $first_sub_tab_get;
						}
						$tab_url   = isset( $sub_tab['url'] ) ? $sub_tab['url'] : '';
						$tab_label = isset( $sub_tab['label'] ) ? $sub_tab['label'] : '';
						if ( $current_sub_tab === $tab_get ) {
							$sub_tab_html_array[] = sprintf( '<span class="%s" id="mpp-tab-%s">%s</span>', esc_attr( implode( ' ', $classes ) ), esc_attr( $tab_get ), esc_html( $sub_tab['label'] ) );
						} else {
							$sub_tab_html_array[] = sprintf( '<a href="%s" class="%s" id="mpp-tab-%s">%s</a>', esc_url( $tab_url ), esc_attr( implode( ' ', $classes ) ), esc_attr( $tab_get ), esc_html( $sub_tab['label'] ) );
						}
					}
					if ( ! empty( $sub_tab_html_array ) ) {
						echo '<nav class="mpp-sub-links">' . wp_kses_post( rtrim( implode( ' | ', $sub_tab_html_array ), ' | ' ) ) . '</nav>';
					}
					if ( $do_subtab_action ) {
						/**
						 * Perform a sub tab action.
						 *
						 * Perform a sub tab action. Useful for loading scripts or inline styles as necessary.
						 *
						 * @since 5.1.0
						 *
						 * mpp_admin_sub_tab_{current_tab}_{current_sub_tab}
						 */
						do_action(
							sprintf( // phpcs:ignore
								'ptam_admin_sub_tab_%s_%s',
								sanitize_title( $current_tab ),
								sanitize_title( $current_sub_tab )
							)
						);
					}
				}
				if ( $do_action ) {

					/**
					 * Perform a tab action.
					 *
					 * Perform a tab action.
					 *
					 * @since 5.1.0
					 *
					 * @param string $current_tab Tab
					 * @param string $current_sub_tab Sub Tab
					 */
					do_action( $do_action, $current_tab, $current_sub_tab );
				}
			}
			?>
		<?php
	}

	/**
	 * Output Admin Page Header.
	 */
	public static function get_settings_header() {
		?>
		<div class="wrap ptam-admin-wrap">
			<h1>
				<a href="<?php echo esc_url( Helpers::get_settings_url() ); ?>" class="ptam-admin-logo"><img src="<?php echo esc_url( Helpers::get_plugin_logo() ); ?>" alt="Custom Query Blocks" /></a>
				<strong style="font-weight: 500;"><?php echo esc_html( _x( 'Custom Query Blocks', 'Custom Query Blocks', 'post-type-archive-mapping' ) ); ?></strong>
			</h1>
			<p class="ptam-info-text"><?php esc_html_e( 'This plugin provides several helper Query Blocks as well as archive mapping for post type archives, terms, and a 404 page.', 'post-type-archive-mapping' ); ?></p>
		<?php
	}

	/**
	 * Run script and enqueue stylesheets and stuff like that.
	 */
	public static function get_settings_footer() {
		?>
		</div><!-- .wrap.ptam-admin-wrap -->
		<?php
	}

	/**
	 * Register the settings menu for User Profile Picture
	 *
	 * @since 2.3.0
	 */
	public function register_settings_menu() {
		$hook = add_options_page(
			__( 'Custom Query Blocks', 'post-type-archive-mapping' ),
			__( 'Custom Query Blocks', 'post-type-archive-mapping' ),
			'manage_options',
			'custom-query-blocks',
			array( '\MediaRon\PTAM\Admin\Admin_Settings', 'settings_page' )
		);
		return $hook;
	}
}
