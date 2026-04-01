<?php
/**
 * Theme Widget Compatibility Diagnostic
 *
 * Detects widget compatibility issues with the current theme.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Widget Compatibility Diagnostic Class
 *
 * Checks if theme properly supports WordPress widgets.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Theme_Widget_Compatibility extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-widget-compatibility';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Widget Compatibility';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks theme widget support';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_registered_sidebars, $wp_registered_widgets;

		$theme = wp_get_theme();
		$issues = array();

		// Check if widgets are supported.
		if ( empty( $wp_registered_sidebars ) ) {
			$issues[] = __( 'Theme does not register any widget areas', 'wpshadow' );
		}

		// Check for block widget editor support (WP 5.8+).
		if ( version_compare( get_bloginfo( 'version' ), '5.8', '>=' ) ) {
			if ( ! current_theme_supports( 'widgets' ) && ! current_theme_supports( 'widgets-block-editor' ) ) {
				$issues[] = __( 'Theme lacks block widget editor support', 'wpshadow' );
			}
		}

		// Check if any widgets are actually used.
		$active_widgets = false;
		if ( ! empty( $wp_registered_sidebars ) ) {
			foreach ( array_keys( $wp_registered_sidebars ) as $sidebar_id ) {
				if ( is_active_sidebar( $sidebar_id ) ) {
					$active_widgets = true;
					break;
				}
			}
		}

		// Check for widgets.php (legacy).
		$theme_dir = get_stylesheet_directory();
		$has_widgets_file = file_exists( $theme_dir . '/widgets.php' );

		if ( $has_widgets_file ) {
			$issues[] = __( 'Theme uses deprecated widgets.php file', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Theme has widget compatibility issues', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'details'     => array(
					'theme'          => $theme->get( 'Name' ),
					'sidebar_count'  => ! empty( $wp_registered_sidebars ) ? count( $wp_registered_sidebars ) : 0,
					'active_widgets' => $active_widgets,
					'issues'         => $issues,
				),
				'kb_link'     => 'https://wpshadow.com/kb/theme-widget-compatibility?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
