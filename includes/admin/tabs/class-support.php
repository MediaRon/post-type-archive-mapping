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
class Support extends Tabs {

	/**
	 * Tab to run actions against.
	 *
	 * @var $tab Settings tab.
	 */
	protected $tab = 'support';

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
			'label'  => _x( 'Support', 'Tab label as support', 'post-type-archive-mapping' ),
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
				?>
				<svg width="0" height="0" class="hidden" aria-hidden="true">
					<symbol aria-hidden="true" data-prefix="fas" data-icon="hands-helping" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512" id="ptam-support-icon">
						<path fill="currentColor" d="M488 192H336v56c0 39.7-32.3 72-72 72s-72-32.3-72-72V126.4l-64.9 39C107.8 176.9 96 197.8 96 220.2v47.3l-80 46.2C.7 322.5-4.6 342.1 4.3 357.4l80 138.6c8.8 15.3 28.4 20.5 43.7 11.7L231.4 448H368c35.3 0 64-28.7 64-64h16c17.7 0 32-14.3 32-32v-64h8c13.3 0 24-10.7 24-24v-48c0-13.3-10.7-24-24-24zm147.7-37.4L555.7 16C546.9.7 527.3-4.5 512 4.3L408.6 64H306.4c-12 0-23.7 3.4-33.9 9.7L239 94.6c-9.4 5.8-15 16.1-15 27.1V248c0 22.1 17.9 40 40 40s40-17.9 40-40v-88h184c30.9 0 56 25.1 56 56v28.5l80-46.2c15.3-8.9 20.5-28.4 11.7-43.7z"></path>
					</symbol>
					<symbol aria-hidden="true" data-prefix="fab" data-icon="github" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 496 512" id="ptam-github-icon">
						<path fill="currentColor" d="M165.9 397.4c0 2-2.3 3.6-5.2 3.6-3.3.3-5.6-1.3-5.6-3.6 0-2 2.3-3.6 5.2-3.6 3-.3 5.6 1.3 5.6 3.6zm-31.1-4.5c-.7 2 1.3 4.3 4.3 4.9 2.6 1 5.6 0 6.2-2s-1.3-4.3-4.3-5.2c-2.6-.7-5.5.3-6.2 2.3zm44.2-1.7c-2.9.7-4.9 2.6-4.6 4.9.3 2 2.9 3.3 5.9 2.6 2.9-.7 4.9-2.6 4.6-4.6-.3-1.9-3-3.2-5.9-2.9zM244.8 8C106.1 8 0 113.3 0 252c0 110.9 69.8 205.8 169.5 239.2 12.8 2.3 17.3-5.6 17.3-12.1 0-6.2-.3-40.4-.3-61.4 0 0-70 15-84.7-29.8 0 0-11.4-29.1-27.8-36.6 0 0-22.9-15.7 1.6-15.4 0 0 24.9 2 38.6 25.8 21.9 38.6 58.6 27.5 72.9 20.9 2.3-16 8.8-27.1 16-33.7-55.9-6.2-112.3-14.3-112.3-110.5 0-27.5 7.6-41.3 23.6-58.9-2.6-6.5-11.1-33.3 2.6-67.9 20.9-6.5 69 27 69 27 20-5.6 41.5-8.5 62.8-8.5s42.8 2.9 62.8 8.5c0 0 48.1-33.6 69-27 13.7 34.7 5.2 61.4 2.6 67.9 16 17.7 25.8 31.5 25.8 58.9 0 96.5-58.9 104.2-114.8 110.5 9.2 7.9 17 22.9 17 46.4 0 33.7-.3 75.4-.3 83.6 0 6.5 4.6 14.4 17.3 12.1C428.2 457.8 496 362.9 496 252 496 113.3 383.5 8 244.8 8zM97.2 352.9c-1.3 1-1 3.3.7 5.2 1.6 1.6 3.9 2.3 5.2 1 1.3-1 1-3.3-.7-5.2-1.6-1.6-3.9-2.3-5.2-1zm-10.8-8.1c-.7 1.3.3 2.9 2.3 3.9 1.6 1 3.6.7 4.3-.7.7-1.3-.3-2.9-2.3-3.9-2-.6-3.6-.3-4.3.7zm32.4 35.6c-1.6 1.3-1 4.3 1.3 6.2 2.3 2.3 5.2 2.6 6.5 1 1.3-1.3.7-4.3-1.3-6.2-2.2-2.3-5.2-2.6-6.5-1zm-11.4-14.7c-1.6 1-1.6 3.6 0 5.9 1.6 2.3 4.3 3.3 5.6 2.3 1.6-1.3 1.6-3.9 0-6.2-1.4-2.3-4-3.3-5.6-2z"></path>
					</symbol>
					<symbol aria-hidden="true" data-prefix="fas" data-icon="heart" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" id="ptam-heart-icon">
						<path fill="currentColor" d="M462.3 62.6C407.5 15.9 326 24.3 275.7 76.2L256 96.5l-19.7-20.3C186.1 24.3 104.5 15.9 49.7 62.6c-62.8 53.6-66.1 149.8-9.9 207.9l193.5 199.8c12.5 12.9 32.8 12.9 45.3 0l193.5-199.8c56.3-58.1 53-154.3-9.8-207.9z"></path>
					</symbol>
					<symbol aria-hidden="true" data-prefix="fas" data-icon="book" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" id="ptam-book-icon">
						<path fill="currentColor" d="M448 360V24c0-13.3-10.7-24-24-24H96C43 0 0 43 0 96v320c0 53 43 96 96 96h328c13.3 0 24-10.7 24-24v-16c0-7.5-3.5-14.3-8.9-18.7-4.2-15.4-4.2-59.3 0-74.7 5.4-4.3 8.9-11.1 8.9-18.6zM128 134c0-3.3 2.7-6 6-6h212c3.3 0 6 2.7 6 6v20c0 3.3-2.7 6-6 6H134c-3.3 0-6-2.7-6-6v-20zm0 64c0-3.3 2.7-6 6-6h212c3.3 0 6 2.7 6 6v20c0 3.3-2.7 6-6 6H134c-3.3 0-6-2.7-6-6v-20zm253.4 250H96c-17.7 0-32-14.3-32-32 0-17.6 14.4-32 32-32h285.4c-1.9 17.1-1.9 46.9 0 64z"></path>
					</symbol>
					<symbol aria-hidden="true" data-prefix="fas" data-icon="star" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" id="ptam-star-icon">
						<path fill="currentColor" d="M259.3 17.8L194 150.2 47.9 171.5c-26.2 3.8-36.7 36.1-17.7 54.6l105.7 103-25 145.5c-4.5 26.3 23.2 46 46.4 33.7L288 439.6l130.7 68.7c23.2 12.2 50.9-7.4 46.4-33.7l-25-145.5 105.7-103c19-18.5 8.5-50.8-17.7-54.6L382 150.2 316.7 17.8c-11.7-23.6-45.6-23.9-57.4 0z"></path>
					</symbol>
				</svg>
				<div class="ptam-admin-panel-area">
					<h3 class="ptam-panel-heading">
						<?php esc_html_e( 'Get Support On the WordPress Plugin Directory', 'post-type-archive-mapping' ); ?>
					</h3>
					<div class="ptam-panel-row">
						<p class="description">
							<?php esc_html_e( 'The best way to receive support is via the official WordPress Plugin Directory support forum.', 'post-type-archive-mapping' ); ?>
						</p>
					</div>
					<div class="ptam-panel-row">
						<a class="ptam-button ptam-button-info" href="https://wordpress.org/support/plugin/post-type-archive-mapping/" target="_blank"><svg class="ptam-icon"><use xlink:href="#ptam-support-icon"></use></svg>&nbsp;&nbsp;<?php esc_html_e( 'Open a Support Topic', 'post-type-archive-mapping' ); ?></a>
					</div>
				</div>
				<div class="ptam-admin-panel-area">
					<h3 class="ptam-panel-heading">
						<?php esc_html_e( 'File a GitHub Issue', 'post-type-archive-mapping' ); ?>
					</h3>
					<div class="ptam-panel-row">
						<p class="description">
							<?php esc_html_e( 'Feature requests or modifications to existing behavior can be opened on GitHub.', 'post-type-archive-mapping' ); ?>
						</p>
					</div>
					<div class="ptam-panel-row">
						<a class="ptam-button ptam-button-info" href="https://github.com/MediaRon/post-type-archive-mapping/issues" target="_blank"><svg class="ptam-icon"><use xlink:href="#ptam-github-icon"></use></svg>&nbsp;&nbsp;<?php esc_html_e( 'Open a GitHub Issue', 'post-type-archive-mapping' ); ?></a>
					</div>
				</div>
				<div class="ptam-admin-panel-area">
					<h3 class="ptam-panel-heading">
						<?php esc_html_e( 'Show Your Support', 'post-type-archive-mapping' ); ?>
					</h3>
					<div class="ptam-panel-row">
						<p class="description">
							<?php esc_html_e( 'Every cent counts and will help this project monetarily.', 'post-type-archive-mapping' ); ?>
						</p>
					</div>
					<div class="ptam-panel-row">
						<a class="ptam-button ptam-button-info" href="https://github.com/sponsors/MediaRon" target="_blank"><svg class="ptam-icon"><use xlink:href="#ptam-heart-icon"></use></svg>&nbsp;&nbsp;<?php esc_html_e( 'Sponsor This Plugin', 'post-type-archive-mapping' ); ?></a>
					</div>
				</div>
				<div class="ptam-admin-panel-area">
					<h3 class="ptam-panel-heading">
						<?php esc_html_e( 'Documentation', 'post-type-archive-mapping' ); ?>
					</h3>
					<div class="ptam-panel-row">
						<p class="description">
							<?php esc_html_e( 'The documentation for the plugin displays its capabilities.', 'post-type-archive-mapping' ); ?>
						</p>
					</div>
					<div class="ptam-panel-row">
						<a class="ptam-button ptam-button-info" href="https://mediaron.com/custom-query-blocks/" target="_blank"><svg class="ptam-icon"><use xlink:href="#ptam-book-icon"></use></svg>&nbsp;&nbsp;<?php esc_html_e( 'View the Documentation', 'post-type-archive-mapping' ); ?></a>
					</div>
				</div>
				<div class="ptam-admin-panel-area">
					<h3 class="ptam-panel-heading">
						<?php esc_html_e( 'Help Rate This Plugin', 'post-type-archive-mapping' ); ?>
					</h3>
					<div class="ptam-panel-row">
						<p class="description">
							<?php esc_html_e( 'If you find this plugin useful, please consider leaving a star rating on WordPress.org.', 'post-type-archive-mapping' ); ?>
						</p>
					</div>
					<div class="ptam-panel-row">
						<a class="ptam-button ptam-button-info" href="https://wordpress.org/support/plugin/post-type-archive-mapping/reviews/#new-post" target="_blank"><svg class="ptam-icon"><use xlink:href="#ptam-star-icon"></use></svg>&nbsp;&nbsp;<?php esc_html_e( 'Help Rate This Plugin', 'post-type-archive-mapping' ); ?></a>
					</div>
				</div>
				<?php
			}
		}
	}
}
