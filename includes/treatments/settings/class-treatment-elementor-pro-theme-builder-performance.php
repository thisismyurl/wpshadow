<?php
/**
 * Elementor Pro Theme Builder Performance Impact Treatment
 *
 * Checks if Elementor Pro Theme Builder templates are causing performance issues.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6031.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Elementor Pro Theme Builder Performance Treatment Class
 *
 * Verifies Theme Builder templates are not causing site-wide performance issues.
 *
 * @since 1.6031.1200
 */
class Treatment_Elementor_Pro_Theme_Builder_Performance extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'elementor-pro-theme-builder-performance';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Elementor Pro Theme Builder Performance Impact';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies Theme Builder templates not causing performance issues';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'plugins';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6031.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Elementor_Pro_Theme_Builder_Performance' );
	}

	/**
	 * Count widgets recursively in Elementor data.
	 *
	 * @since  1.6031.1200
	 * @param  array $elements Elementor elements array.
	 * @return int Widget count.
	 */
	private static function count_widgets_recursively( $elements ) {
		$count = 0;
		foreach ( $elements as $element ) {
			if ( isset( $element['elType'] ) && 'widget' === $element['elType'] ) {
				++$count;
			}
			if ( ! empty( $element['elements'] ) && is_array( $element['elements'] ) ) {
				$count += self::count_widgets_recursively( $element['elements'] );
			}
		}
		return $count;
	}

	/**
	 * Check if data contains specific widget types.
	 *
	 * @since  1.6031.1200
	 * @param  array $elements    Elementor elements array.
	 * @param  array $widget_types Widget types to search for.
	 * @return bool True if found, false otherwise.
	 */
	private static function has_widget_type( $elements, $widget_types ) {
		foreach ( $elements as $element ) {
			if ( isset( $element['widgetType'] ) && in_array( $element['widgetType'], $widget_types, true ) ) {
				return true;
			}
			if ( ! empty( $element['elements'] ) && is_array( $element['elements'] ) ) {
				if ( self::has_widget_type( $element['elements'], $widget_types ) ) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Get posts_per_page setting from loop widgets.
	 *
	 * @since  1.6031.1200
	 * @param  array $elements Elementor elements array.
	 * @return int Maximum posts_per_page found.
	 */
	private static function get_loop_posts_per_page( $elements ) {
		$max = 0;
		foreach ( $elements as $element ) {
			if ( isset( $element['settings']['posts_per_page'] ) ) {
				$max = max( $max, (int) $element['settings']['posts_per_page'] );
			}
			if ( ! empty( $element['elements'] ) && is_array( $element['elements'] ) ) {
				$max = max( $max, self::get_loop_posts_per_page( $element['elements'] ) );
			}
		}
		return $max;
	}
}
