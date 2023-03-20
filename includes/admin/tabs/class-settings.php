<?php
/**
 * Register the Settings tab and any sub-tabs.
 *
 * @package user-profile-picture
 */

namespace MediaRon\PTAM\Admin\Tabs;

use MediaRon\PTAM\Helpers;
use MediaRon\PTAM\Admin\Options;

/**
 * Output the settings tab and content.
 */
class Settings extends Tabs {

	/**
	 * Tab to run actions against.
	 *
	 * @var $tab Settings tab.
	 */
	private $tab = 'settings';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'ptam_admin_tabs', array( $this, 'add_tab' ), 1, 1 );
		add_filter( 'ptam_admin_sub_tabs', array( $this, 'add_sub_tab' ), 1, 3 );
		add_action( 'ptam_output_' . $this->tab, array( $this, 'output_settings' ), 1, 2 );
	}

	/**
	 * Add the settings tab and callback actions.
	 *
	 * @param array $tabs Array of tabs.
	 *
	 * @return array of tabs.
	 */
	public function add_tab( $tabs ) {
		$tabs[] = array(
			'get'    => $this->tab,
			'action' => 'ptam_output_' . $this->tab,
			'url'    => Helpers::get_settings_url( $this->tab ),
			'label'  => _x( 'Settings', 'Tab label as settings', 'post-type-archive-mapping' ),
			'icon'   => 'home-heart',
		);
		return $tabs;
	}

	/**
	 * Add the settings main tab and callback actions.
	 *
	 * @param array  $tabs        Array of tabs.
	 * @param string $current_tab The current tab selected.
	 * @param string $sub_tab     The current sub-tab selected.
	 *
	 * @return array of tabs.
	 */
	public function add_sub_tab( $tabs, $current_tab, $sub_tab ) {
		if ( ( ! empty( $current_tab ) || ! empty( $sub_tab ) ) && $this->tab !== $current_tab ) {
			return $tabs;
		}
		return $tabs;
	}

	/**
	 * Begin settings routing for the various outputs.
	 *
	 * @param string $tab     Current tab.
	 * @param string $sub_tab Current sub tab.
	 */
	public function output_settings( $tab, $sub_tab = '' ) {
		if ( $this->tab === $tab ) {
			if ( empty( $sub_tab ) || $this->tab === $sub_tab ) {
				if ( isset( $_POST['submit'] ) && isset( $_POST['options'] ) ) {
					check_admin_referer( 'save_ptam_' . $this->tab );
					$options = wp_unslash( $_POST['options'] ); // phpcs:ignore
					Options::update_options( $options );
					printf( '<div class="updated"><p><strong>%s</strong></p></div>', esc_html__( 'Your options have been saved.', 'post-type-archive-mapping' ) );
				}
				// Get options and defaults.
				$options = Options::get_options( true );
				?>
				<form action="<?php echo esc_url( Helpers::get_settings_url( $this->tab ) ); ?>" method="POST">
					<?php wp_nonce_field( 'save_ptam_' . $this->tab ); ?>
					<table class="form-table">
						<tbody>
							<tr>
								<th scope="row"><?php esc_html_e( 'Disable Blocks', 'post-type-archive-mapping' ); ?></th>
								<td>
									<input type="hidden" name="options[disable_blocks]" value="off" />
									<input id="ptam-disable-blocks" type="checkbox" value="on" name="options[disable_blocks]" <?php checked( 'on', $options['disable_blocks'] ); ?> /> <label for="ptam-disable-blocks"><?php esc_html_e( 'Disable All Blocks', 'post-type-archive-mapping' ); ?></label>
									<p class="description"><?php esc_html_e( 'Select this option if you would like to disable blocks and use only the archive mapping', 'post-type-archive-mapping' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Disable Archive Mapping', 'post-type-archive-mapping' ); ?></th>
								<td>
									<input type="hidden" name="options[disable_archive_mapping]" value="off" />
									<input id="ptam-disable-archive-mapping" type="checkbox" value="on" name="options[disable_archive_mapping]" <?php checked( 'on', $options['disable_archive_mapping'] ); ?> /> <label for="ptam-disable-archive-mapping"><?php esc_html_e( 'Disable Archive Mapping', 'post-type-archive-mapping' ); ?></label>
									<p class="description"><?php esc_html_e( 'Select this option if you would like to disable archive mapping for this plugin.', 'post-type-archive-mapping' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Disable Page Columns', 'post-type-archive-mapping' ); ?></th>
								<td>
									<input type="hidden" name="options[disable_page_columns]" value="off" />
									<input id="ptam-disable-page-columns" type="checkbox" value="on" name="options[disable_page_columns]" <?php checked( 'on', $options['disable_page_columns'] ); ?> /> <label for="ptam-disable-page-columns"><?php esc_html_e( 'Disable Page Columns', 'post-type-archive-mapping' ); ?></label>
									<p class="description"><?php esc_html_e( 'This plugin creates an extra column in the "All Pages" admin screen to show which pages are mapped. Disabling page columns removes the column.', 'post-type-archive-mapping' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Disable Image Sizes', 'post-type-archive-mapping' ); ?></th>
								<td>
									<input type="hidden" name="options[disable_image_sizes]" value="off" />
									<input id="ptam-disable-image-sizes" type="checkbox" value="on" name="options[disable_image_sizes]" <?php checked( 'on', $options['disable_image_sizes'] ); ?> /> <label for="ptam-disable-image-sizes"><?php esc_html_e( 'Disable Image Sizes', 'post-type-archive-mapping' ); ?></label>
									<p class="description"><?php esc_html_e( 'This plugin creates additional image sizes which can be seen in the "Featured Image Size" drop down when using the blocks this plugin provides. Disabling image sizes prevents the plugin from creating these each time you upload an image.', 'post-type-archive-mapping' ); ?></p>
								</td>
							</tr>
							<?php
							/**
							 * Allow other plugins to run code after the PTAM Table Row.
							 *
							 * @since 5.1.0
							 *
							 * @param array $options Array of options.
							 */
							$action = sprintf(
								'ptam_admin_%s_after_row',
								$this->tab
							);
							do_action( $action, $options );
							?>
						</tbody>
					</table>
					<?php
					/**
					 * Allow other plugins to run code after the user profile admin Table.
					 *
					 * @since 2.3.0
					 *
					 * @param array $options Array of options.
					 */
					$action = sprintf(
						'ptam_admin_%s_after_table',
						$this->tab
					);
					do_action( $action, $options );
					?>
					<?php submit_button( __( 'Save Options', 'post-type-archive-mapping' ) ); ?>
				</form>
				<?php
			}
		}
	}
}
