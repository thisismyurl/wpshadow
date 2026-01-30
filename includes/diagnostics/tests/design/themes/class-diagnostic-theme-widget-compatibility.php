<?php
/**
 * Theme Widget Compatibility Diagnostic
 *
 * Checks if all theme widgets load and display properly.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5030.1045
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Widget Compatibility Class
 *
 * Validates widget areas and widget functionality.
 *
 * @since 1.5030.1045
 */
class Diagnostic_Theme_Widget_Compatibility extends Diagnostic_Base {

	protected static $slug        = 'theme-widget-compatibility';
	protected static $title       = 'Theme Widget Compatibility';
	protected static $description = 'Checks widget functionality';
	protected static $family      = 'themes';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.5030.1045
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$cache_key = 'wpshadow_widget_compatibility';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		global $wp_registered_sidebars, $wp_registered_widgets;

		$issues = array();

		// Check if theme has registered sidebars.
		if ( empty( $wp_registered_sidebars ) ) {
			$issues[] = 'Theme has no registered widget areas';
		} else {
			// Check for inactive widgets.
			$sidebars_widgets = wp_get_sidebars_widgets();
			
			foreach ( $wp_registered_sidebars as $sidebar_id => $sidebar ) {
				if ( empty( $sidebars_widgets[ $sidebar_id ] ) ) {
					$issues[] = sprintf( 'Widget area "%s" is empty', $sidebar['name'] );
				}
			}
		}

		// Check for broken widgets.
		$inactive_widgets = get_option( 'sidebars_widgets', array() );
		if ( isset( $inactive_widgets['wp_inactive_widgets'] ) && ! empty( $inactive_widgets['wp_inactive_widgets'] ) ) {
			$inactive_count = count( $inactive_widgets['wp_inactive_widgets'] );
			$issues[] = sprintf( '%d inactive widgets (may be from old theme)', $inactive_count );
		}

		// Check if widgets match theme's style.
		$current_theme = wp_get_theme();
		$theme_dir     = $current_theme->get_stylesheet_directory();
		
		// Check for widgets.php or widget-related files.
		$has_widget_styles = file_exists( $theme_dir . '/widgets.php' ) || 
		                     file_exists( $theme_dir . '/inc/widgets.php' ) ||
		                     file_exists( $theme_dir . '/css/widgets.css' );
		
		if ( ! $has_widget_styles && ! empty( $wp_registered_sidebars ) ) {
			$issues[] = 'Theme may lack widget-specific styling';
		}

		// Check for legacy widget usage.
		foreach ( $wp_registered_widgets as $widget_id => $widget ) {
			if ( isset( $widget['callback'] ) ) {
				// Check if using old-style widget registration.
				if ( is_string( $widget['callback'] ) && strpos( $widget['callback'], 'WP_Widget' ) === false ) {
					$issues[] = sprintf( 'Widget "%s" uses deprecated registration method', $widget['name'] ?? $widget_id );
					break; // Only report once.
				}
			}
		}

		if ( ! empty( $issues ) ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues */
					__( '%d widget compatibility issues found. Review widget configuration.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/themes-widget-compatibility',
				'data'         => array(
					'issues'            => $issues,
					'total_issues'      => count( $issues ),
					'registered_areas'  => count( $wp_registered_sidebars ),
					'registered_widgets' => count( $wp_registered_widgets ),
				),
			);

			set_transient( $cache_key, $result, 24 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}
}
