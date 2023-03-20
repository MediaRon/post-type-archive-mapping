<?php
/**
 * Abstract class for tabs.
 *
 * @package PTAM
 */

namespace MediaRon\PTAM\Admin\Tabs;

/**
 * Tabs boilerplate.
 */
abstract class Tabs {

	/**
	 * Tab to run actions against.
	 *
	 * @var $tab Current tab.
	 */
	private $tab;

	/**
	 * Get tab content.
	 *
	 * @param array $tabs Array of tabs.
	 */
	abstract public function add_tab( $tabs );

	/**
	 * Add a sub tab for settings.
	 *
	 * @param array  $tabs        Array of tabs.
	 * @param string $current_tab The current tab selected.
	 * @param string $sub_tab     The current sub-tab selected.
	 */
	abstract public function add_sub_tab( $tabs, $current_tab, $sub_tab );

	/**
	 * Output admin content.
	 *
	 * @param string $tab     Current tab.
	 * @param string $sub_tab Current sub tab.
	 */
	abstract public function output_settings( $tab, $sub_tab );
}
