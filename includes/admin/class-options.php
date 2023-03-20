<?php
/**
 * Plugin Options.
 *
 * @package PTAM
 */

namespace MediaRon\PTAM\Admin;

/**
 * Class Options
 */
class Options {

	/**
	 * A list of options cached for saving.
	 *
	 * @var array $options
	 */
	private static $options = array();

	/**
	 * Get the options for Custom Query Blocks.
	 *
	 * @since 5.1.0
	 *
	 * @param bool   $force true to retrieve options directly, false to use cached version.
	 * @param string $key The option key to retrieve.
	 *
	 * @return string|array|bool Return a string if key is set, array of options (default), or false if key is set and option is not found.
	 */
	public static function get_options( $force = false, $key = '' ) {

		$options = self::$options;
		if ( ! is_array( $options ) || empty( $options ) || true === $force ) {
			$options       = get_option( 'ptam_options', array() );
			self::$options = $options;
		}
		if ( false === $options || empty( $options ) || ! is_array( $options ) ) {
			$options = self::get_defaults();
		} else {
			$options = wp_parse_args( $options, self::get_defaults() );
		}
		self::$options = $options;

		// Return a key if set.
		if ( ! empty( $key ) ) {
			if ( isset( $options[ $key ] ) ) {
				return $options[ $key ];
			} else {
				return false;
			}
		}

		return self::$options;
	}

	/**
	 * Save options for the plugin.
	 *
	 * @param array $options array of options.
	 *
	 * @return array|\WP_Error Options.
	 */
	public static function update_options( $options = array() ) {

		/**
		 * Filter for saving options.
		 *
		 * @since 5.1.0
		 *
		 * @param array $options Array of options.
		 */
		$filtered_options = apply_filters( 'ptam_options_save_pre', $options );
		if ( self::sanitize_options( $filtered_options ) ) {
			update_option( 'ptam_options', $filtered_options );

			self::$options = $filtered_options;

			return $filtered_options;
		}

		return new \WP_Error(
			'ptam_update_options_fail',
			__( 'Invalid options were not able to be sanitized' )
		);
	}

	/**
	 * Get the default options for Custom Query Blocks
	 *
	 * @since 5.1.0
	 */
	private static function get_defaults() {
		$defaults = array(
			'disable_blocks'          => 'off',
			'disable_archive_mapping' => 'off',
			'disable_page_columns'    => 'off',
			'disable_image_sizes'     => 'off',
		);

		/**
		 * Allow other plugins to add to the defaults.
		 *
		 * @since 5.1.0
		 *
		 * @param array $defaults An array of option defaults.
		 */
		$defaults = apply_filters( 'ptam_options_defaults', $defaults );
		return $defaults;
	}

	/**
	 * Sanitize options before saving.
	 *
	 * @param array $options Array of options to sanitize.
	 *
	 * @return bool true if valid, false if not.
	 */
	private static function sanitize_options( $options = array() ) {
		if ( ! is_array( $options ) ) {
			return false;
		}
		foreach ( $options as $option_name => $option_value ) {
			switch ( $option_name ) {
				default:
					if ( 'off' !== $option_value && 'on' !== $option_value ) {
						return false;
					}
					break;
			}
		}
		return true;
	}

	/**
	 * Checks if blocks are disabled or not.
	 *
	 * @return bool true if disabled, false if not.
	 */
	public static function is_blocks_disabled() {
		$maybe_disabled = self::get_options( false, 'disable_blocks' );
		if ( is_string( $maybe_disabled ) && 'on' === $maybe_disabled ) {
			return true;
		}
		return false;
	}

	/**
	 * Checks if page columns are disabled or not.
	 *
	 * @return bool true if disabled, false if not.
	 */
	public static function is_page_columns_disabled() {
		$maybe_disabled = self::get_options( false, 'disable_page_columns' );
		if ( is_string( $maybe_disabled ) && 'on' === $maybe_disabled ) {
			return true;
		}
		return false;
	}

	/**
	 * Checks if archive mapping is disabled or not.
	 *
	 * @return bool true if disabled, false if not.
	 */
	public static function is_archive_mapping_disabled() {
		$maybe_disabled = self::get_options( false, 'disable_archive_mapping' );
		if ( is_string( $maybe_disabled ) && 'on' === $maybe_disabled ) {
			return true;
		}
		return false;
	}

	/**
	 * Checks if custom image sizes are disabled or not.
	 *
	 * @return bool true if disabled, false if not.
	 */
	public static function is_custom_image_sizes_disabled() {
		$maybe_disabled = self::get_options( false, 'disable_image_sizes' );
		if ( is_string( $maybe_disabled ) && 'on' === $maybe_disabled ) {
			return true;
		}
		return false;
	}
}
