<?php
/**
 * Helper fuctions.
 *
 * @package PTAM
 */

namespace MediaRon\PTAM;

/**
 * Class functions
 */
class Helpers {
	/**
	 * Get all the registered image sizes along with their dimensions
	 *
	 * @global array $_wp_additional_image_sizes
	 *
	 * @link http://core.trac.wordpress.org/ticket/18947 Reference ticket
	 *
	 * @return array $image_sizes The image sizes
	 */
	public static function get_all_image_sizes() {
		global $_wp_additional_image_sizes;

		$default_image_sizes = get_intermediate_image_sizes();

		foreach ( $default_image_sizes as $size ) {
			$image_sizes[ $size ]['width']  = intval( get_option( "{$size}_size_w" ) );
			$image_sizes[ $size ]['height'] = intval( get_option( "{$size}_size_h" ) );
			$image_sizes[ $size ]['crop']   = get_option( "{$size}_crop" ) ? get_option( "{$size}_crop" ) : false;
		}

		if ( isset( $_wp_additional_image_sizes ) && count( $_wp_additional_image_sizes ) ) {
			$image_sizes = array_merge( $image_sizes, $_wp_additional_image_sizes );
		}

		return $image_sizes;
	}

	/**
	 * Sanitize an attribute based on type.
	 *
	 * @param array  $attributes Array of attributes.
	 * @param string $attribute  The attribute to sanitize.
	 * @param string $type       The type of sanitization you need (values can be int, text, float, bool, url).
	 *
	 * @return mixed Sanitized attribute. wp_error on failure.
	 */
	public static function sanitize_attribute( $attributes, $attribute, $type = 'text' ) {
		if ( isset( $attributes[ $attribute ] ) ) {
			switch ( $type ) {
				case 'text':
					return sanitize_text_field( $attributes[ $attribute ] );
				case 'bool':
					return filter_var( $attributes[ $attribute ], FILTER_VALIDATE_BOOLEAN );
				case 'int':
					return absint( $attributes[ $attribute ] );
				case 'float':
					if ( is_float( $attributes[ $attribute ] ) ) {
						return $attributes[ $attribute ];
					}
					return 0;
				case 'url':
					return esc_url( $attributes[ $attribute ] );
				case 'default':
					return new \WP_Error( 'ptam_unknown_type', __( 'Unknown type.', 'post-type-archive-mapping' ) );
			}
		}
		return new \WP_Error( 'ptam_attribute_not_found', __( 'Attribute not found.', 'post-type-archive-mapping' ) );
	}

	/**
	 * Convert Hex to RGBA
	 *
	 * @param string $color   The color to convert.
	 * @param int    $opacity The opacity.
	 *
	 * @return string rgba attribute.
	 */
	public static function hex2rgba( $color, $opacity = false ) {

		$default = 'rgb(0,0,0)';

		// Return default if no color provided.
		if ( empty( $color ) ) {
			return $default;
		}

		// Sanitize $color if "#" is provided.
		if ( '#' === $color[0] ) {
			$color = substr( $color, 1 );
		}

		// Check if color has 6 or 3 characters and get values.
		if ( strlen( $color ) === 6 ) {
				$hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
		} elseif ( strlen( $color ) === 3 ) {
				$hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
		} else {
				return $default;
		}

		// Convert hexadec to rgb.
		$rgb = array_map( 'hexdec', $hex );

		// Check if opacity is set(rgba or rgb).
		if ( $opacity ) {
			if ( abs( $opacity ) > 1 ) {
				$opacity = 1.0;
			}
			$output = 'rgba(' . implode( ',', $rgb ) . ',' . $opacity . ')';
		} else {
			$output = 'rgb(' . implode( ',', $rgb ) . ')';
		}

		// Return rgb(a) color string.
		return $output;
	}

	/**
	 * Return an image URL.
	 *
	 * @param int    $attachment_id The attachment ID.
	 * @param string $size          The image size to retrieve.
	 *
	 * @return string Image URL or empty string if not found.
	 */
	public static function get_image( $attachment_id = 0, $size = 'large' ) {
		$maybe_image = wp_get_attachment_image_src( $attachment_id, $size );
		if ( ! $maybe_image ) {
			return '';
		}
		if ( isset( $maybe_image[0] ) ) {
			return esc_url( $maybe_image[0] );
		}
		return '';
	}

	/**
	 * Get an image from term meta.
	 *
	 * @param string $size       The image size.
	 * @param string $meta_field The meta field to query.
	 * @param string $type       The type of meta to retrieve (meta, acf, pods).
	 * @param string $taxonomy   The taxonomy slug to retrieve images for.
	 * @param int    $term_id    The term to retrieve data for.
	 *
	 * @return string Image URL or blank if not found.
	 */
	public static function get_term_image( $size = 'large', $meta_field = '', $type = 'meta', $taxonomy = 'category', $term_id = 0 ) {
		if ( 'none' === $type ) {
			return '';
		}
		if ( 'acf' === $type && function_exists( 'get_field' ) ) {
			$acf_term_id    = $taxonomy . '_' . $term_id;
			$acf_term_value = get_field( $meta_field, $acf_term_id );
			if ( ! $acf_term_value ) {
				return '';
			}
			if ( is_numeric( $acf_term_value ) ) {
				$image = self::get_image( $acf_term_value, $size );
				return $image;
			} elseif ( is_array( $acf_term_value ) && isset( $acf_term_value['url'] ) ) {
				return esc_url( $acf_term_value['url'] );
			} elseif ( is_string( $acf_term_value ) ) {
				return esc_url( $acf_term_value );
			} else {
				return '';
			}
		}
		if ( 'meta' === $type ) {
			$term_value = get_term_meta( $term_id, $meta_field, true );
			if ( is_numeric( $term_value ) ) {
				$image = self::get_image( $term_value, $size );
				return $image;
			} elseif ( is_array( $term_value ) && isset( $term_value['url'] ) ) {
				return esc_url( $term_value['url'] );
			} elseif ( is_string( $term_value ) ) {
				return esc_url( $term_value );
			} else {
				return '';
			}
		}
		if ( 'pods' === $type ) {
			$term_value = get_term_meta( $term_id, $meta_field, true );
			if ( is_numeric( $term_value ) ) {
				$image = self::get_image( $term_value, $size );
				return $image;
			} elseif ( is_array( $term_value ) && isset( $term_value['ID'] ) ) {
				return self::get_image( $term_value['ID'], $size );
			} elseif ( is_string( $term_value ) ) {
				return esc_url( $term_value );
			} else {
				return '';
			}
		}
		return '';
	}

	/**
	 * Get web safe fonts
	 *
	 * @return array<string,string> $fonts Fonts to Use
	 */
	public static function get_fonts() {
		/**
		 * Filter the fonts that are available.
		 *
		 * @since 3.5.0
		 *
		 * @param array<string,string> $fonts Associative array of key/value pairs of fonts.
		 */
		$fonts     = apply_filters(
			'ptam_fonts',
			array(
				'inherit'         => 'Default',
				'arial'           => 'Arial',
				'helvetica'       => 'Helvetica',
				'times new roman' => 'Times New Roman',
				'times'           => 'Times',
				'courier new'     => 'Courier New',
				'courier'         => 'Courier',
				'verdana'         => 'Verdana',
				'georgia'         => 'Georgia',
				'palatino'        => 'Palatino',
				'garamond'        => 'Garamond',
				'bookman'         => 'Bookman',
				'trebuchet ms'    => 'Trebuchet MS',
				'arial black'     => 'Arial Black',
				'impact'          => 'Impact',
			)
		);
		$pro_fonts = array();
		// Add Typekit Fonts.
		if ( defined( 'CUSTOM_TYPEKIT_FONTS_FILE' ) ) {
			$adobe_fonts = get_option( 'custom-typekit-fonts', array() );
			if ( isset( $adobe_fonts['custom-typekit-font-details'] ) ) {
				foreach ( $adobe_fonts['custom-typekit-font-details'] as $font_name => $font_details ) {
					$pro_fonts[ $font_details['slug'] ] = $font_details['family'];
				}
			}
		}

		return array_merge( $fonts, $pro_fonts );
	}

	/**
	 * Return the URL to the admin screen
	 *
	 * @param string $tab     Tab path to load.
	 * @param string $sub_tab Subtab path to load.
	 *
	 * @return string URL to admin screen. Output is not escaped.
	 */
	public static function get_settings_url( $tab = '', $sub_tab = '' ) {
		$options_url = admin_url( 'options-general.php?page=custom-query-blocks' );
		if ( ! empty( $tab ) ) {
			$options_url = add_query_arg( array( 'tab' => sanitize_title( $tab ) ), $options_url );
			if ( ! empty( $sub_tab ) ) {
				$options_url = add_query_arg( array( 'subtab' => sanitize_title( $sub_tab ) ), $options_url );
			}
		}
		return $options_url;
	}

	/**
	 * Get the current admin tab.
	 *
	 * @return null|string Current admin tab.
	 */
	public static function get_admin_tab() {
		$tab = filter_input( INPUT_GET, 'tab', FILTER_DEFAULT );
		if ( $tab && is_string( $tab ) ) {
			return sanitize_text_field( sanitize_title( $tab ) );
		}
		return null;
	}

	/**
	 * Get the current admin sub-tab.
	 *
	 * @return null|string Current admin sub-tab.
	 */
	public static function get_admin_sub_tab() {
		$tab = filter_input( INPUT_GET, 'tab', FILTER_DEFAULT );
		if ( $tab && is_string( $tab ) ) {
			$subtab = filter_input( INPUT_GET, 'subtab', FILTER_DEFAULT );
			if ( $subtab && is_string( $subtab ) ) {
				return sanitize_text_field( sanitize_title( $subtab ) );
			}
		}
		return null;
	}

	/**
	 * Return the plugin slug.
	 *
	 * @return string plugin slug.
	 */
	public static function get_plugin_slug() {
		return dirname( plugin_basename( PTAM_FILE ) );
	}

	/**
	 * Return the plugin path.
	 *
	 * @return string plugin path.
	 */
	public static function get_plugin_path() {
		return plugin_basename( PTAM_FILE );
	}

	/**
	 * Return the basefile for the plugin.
	 *
	 * @return string base file for the plugin.
	 */
	public static function get_plugin_file() {
		return plugin_basename( PTAM_FILE );
	}

	/**
	 * Return the version for the plugin.
	 *
	 * @return float version for the plugin.
	 */
	public static function get_plugin_version() {
		return PTAM_VERSION;
	}

	/**
	 * Get the Plugin Logo.
	 */
	public static function get_plugin_logo() {
		/**
		 * Filer the output of the plugin logo.
		 *
		 * Potentially change branding of the plugin.
		 *
		 * @since 5.1.0
		 *
		 * @param string $url URL to the plugin logo.
		 */
		return apply_filters( 'ptam_plugin_logo_full', self::get_plugin_url( '/img/logo.png' ) );
	}

	/**
	 * Get the plugin author name.
	 */
	public static function get_plugin_author() {
		/**
		 * Filer the output of the plugin Author.
		 *
		 * Potentially change branding of the plugin.
		 *
		 * @since 5.1.0
		 *
		 * @param string $name Plugin Author name.
		 */
		$plugin_author = apply_filters( 'ptam_plugin_author', 'MediaRon LLC' );
		return $plugin_author;
	}

	/**
	 * Return the Plugin author URI.
	 */
	public static function get_plugin_author_uri() {
		/**
		 * Filer the output of the plugin Author URI.
		 *
		 * Potentially change branding of the plugin.
		 *
		 * @since 5.1.0
		 *
		 * @param string $uri Plugin Author URI.
		 */
		$plugin_author = apply_filters( 'ptam_plugin_author_uri', 'https://mediaron.com' );
		return $plugin_author;
	}

	/**
	 * Get the Plugin Icon.
	 */
	public static function get_plugin_icon() {
		/**
		 * Filer the output of the plugin icon.
		 *
		 * Potentially change branding of the plugin.
		 *
		 * @since 5.1.0
		 *
		 * @param string $icon URL to the plugin icon.
		 */
		return apply_filters( 'ptam_plugin_icon', self::get_plugin_url( '/img/logo.png' ) );
	}

	/**
	 * Return the plugin name for the plugin.
	 *
	 * @return string Plugin name.
	 */
	public static function get_plugin_name() {
		/**
		 * Filer the output of the plugin name.
		 *
		 * Potentially change branding of the plugin.
		 *
		 * @since 5.1.0
		 *
		 * @param string $name Plugin name.
		 */
		return apply_filters( 'ptam_plugin_name', __( 'Custom Query Blocks', 'post-type-archive-mapping' ) );
	}

	/**
	 * Return the plugin description for the plugin.
	 *
	 * @return string plugin description.
	 */
	public static function get_plugin_description() {
		/**
		 * Filer the output of the plugin name.
		 *
		 * Potentially change branding of the plugin.
		 *
		 * @since 5.1.0
		 *
		 * @param string $description Plugin description.
		 */
		return apply_filters( 'ptam_plugin_description', __( 'Map your post type and term archives to a page and use our Gutenberg blocks to show posts or terms.', 'post-type-archive-mapping' ) );
	}

	/**
	 * Retrieve the plugin URI.
	 */
	public static function get_plugin_uri() {
		/**
		 * Filer the output of the plugin URI.
		 *
		 * Potentially change branding of the plugin.
		 *
		 * @since 5.1.0
		 *
		 * @param string $uri Plugin URI.
		 */
		return apply_filters( 'ptam_plugin_uri', 'https://mediaron.com/custom-query-blocks/' );
	}

	/**
	 * Retrieve the plugin Menu Name.
	 */
	public static function get_plugin_menu_name() {
		/**
		 * Filer the output of the plugin menu name.
		 *
		 * Potentially change branding of the plugin.
		 *
		 * @since 5.1.0
		 *
		 * @param string $name Plugin Menu Name.
		 */
		return apply_filters( 'ptam_plugin_menu_name', __( 'Custom Query Blocks', 'post-type-archive-mapping' ) );
	}

	/**
	 * Retrieve the plugin title.
	 */
	public static function get_plugin_title() {
		/**
		 * Filer the output of the plugin title.
		 *
		 * Potentially change branding of the plugin.
		 *
		 * @since 5.1.0
		 *
		 * @param string $name Plugin Menu Name.
		 */
		return apply_filters( 'ptam_plugin_menu_title', self::get_plugin_name() );
	}

	/**
	 * Returns appropriate html for KSES.
	 *
	 * @param bool $svg Whether to add SVG data to KSES.
	 */
	public static function get_kses_allowed_html( $svg = true ) {
		$allowed_tags = wp_kses_allowed_html();

		$allowed_tags['nav']        = array(
			'class' => array(),
		);
		$allowed_tags['a']['class'] = array();

		if ( ! $svg ) {
			return $allowed_tags;
		}
		$allowed_tags['svg'] = array(
			'xmlns'       => array(),
			'fill'        => array(),
			'viewbox'     => array(),
			'role'        => array(),
			'aria-hidden' => array(),
			'focusable'   => array(),
			'class'       => array(),
		);

		$allowed_tags['path'] = array(
			'd'       => array(),
			'fill'    => array(),
			'opacity' => array(),
		);

		$allowed_tags['g'] = array();

		$allowed_tags['use'] = array(
			'xlink:href' => array(),
		);

		$allowed_tags['symbol'] = array(
			'aria-hidden' => array(),
			'viewBox'     => array(),
			'id'          => array(),
			'xmls'        => array(),
		);

		return $allowed_tags;
	}

	/**
	 * Get the plugin directory for a path.
	 *
	 * @param string $path The path to the file.
	 *
	 * @return string The new path.
	 */
	public static function get_plugin_dir( $path = '' ) {
		$dir = rtrim( plugin_dir_path( PTAM_FILE ), '/' );
		if ( ! empty( $path ) && is_string( $path ) ) {
			$dir .= '/' . ltrim( $path, '/' );
		}
		return $dir;
	}

	/**
	 * Return a plugin URL path.
	 *
	 * @param string $path Path to the file.
	 *
	 * @return string URL to to the file.
	 */
	public static function get_plugin_url( $path = '' ) {
		$dir = rtrim( plugin_dir_url( PTAM_FILE ), '/' );
		if ( ! empty( $path ) && is_string( $path ) ) {
			$dir .= '/' . ltrim( $path, '/' );
		}
		return $dir;
	}

	/**
	 * Gets the highest priority for a filter.
	 *
	 * @param int $subtract The amount to subtract from the high priority.
	 *
	 * @return int priority.
	 */
	public static function get_highest_priority( $subtract = 0 ) {
		$highest_priority = PHP_INT_MAX;
		$subtract         = absint( $subtract );
		if ( 0 === $subtract ) {
			--$highest_priority;
		} else {
			$highest_priority = absint( $highest_priority - $subtract );
		}
		return $highest_priority;
	}
}
