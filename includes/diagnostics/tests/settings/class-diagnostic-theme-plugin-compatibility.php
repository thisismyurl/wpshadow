<?php
/**
 * Theme-Plugin Compatibility Diagnostic
 *
 * Detects compatibility issues between theme and active plugins.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2230
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme-Plugin Compatibility Diagnostic
 *
 * Identifies potential conflicts between current theme and plugins.
 *
 * @since 1.6030.2230
 */
class Diagnostic_Theme_Plugin_Compatibility extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-plugin-compatibility';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme-Plugin Compatibility';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects compatibility issues between theme and active plugins';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'plugins';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2230
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$conflicts = array();
		$warnings  = array();

		$theme_obj = wp_get_theme();
		$theme     = $theme_obj->get( 'Name' );

		// Known theme-specific plugin conflicts
		$theme_conflicts = array(
			'Divi'       => array( 'elementor/elementor.php', 'beaver-builder-lite-version/fl-builder.php' ),
			'Avada'      => array( 'elementor/elementor.php', 'beaver-builder-lite-version/fl-builder.php' ),
			'Enfold'     => array( 'elementor/elementor.php', 'beaver-builder-lite-version/fl-builder.php' ),
			'GeneratePress' => array( 'elementor/elementor.php' ),
			'OceanWP'    => array( 'wp-rocket/wp-rocket.php', 'autoptimize/autoptimize.php' ),
		);

		// Check for known conflicts
		$active_plugins = get_option( 'active_plugins', array() );

		foreach ( $theme_conflicts as $conflict_theme => $problematic_plugins ) {
			if ( stripos( $theme, $conflict_theme ) !== false ) {
				foreach ( $problematic_plugins as $plugin ) {
					if ( in_array( $plugin, $active_plugins, true ) ) {
						$plugin_name = basename( dirname( $plugin ) );
						$conflicts[] = sprintf(
							/* translators: %s: plugin name */
							__( '%s may conflict with your theme', 'wpshadow' ),
							$plugin_name
						);
					}
				}
			}
		}

		// Check for cache plugin conflicts with theme
		$cache_plugins = array(
			'wp-rocket/wp-rocket.php'    => 'WP Rocket',
			'w3-total-cache/w3-total-cache.php' => 'W3 Total Cache',
			'wpsc/wp-super-cache.php'    => 'WP Super Cache',
		);

		foreach ( $cache_plugins as $plugin => $name ) {
			if ( in_array( $plugin, $active_plugins, true ) ) {
				$warnings[] = sprintf(
					/* translators: %s: cache plugin name */
					__( '%s may cause theme customizations to not display correctly', 'wpshadow' ),
					$name
				);
			}
		}

		// Check for theme update compatibility
		$theme_mtime = filemtime( $theme_obj->get_stylesheet_directory() );
		$time_diff   = time() - $theme_mtime;
		$months_old  = (int) ( $time_diff / ( 30 * 24 * 60 * 60 ) );

		if ( $months_old > 12 ) {
			$warnings[] = sprintf(
				/* translators: %d: number of months */
				__( 'Theme not updated for %d months - may have compatibility issues with new plugins', 'wpshadow' ),
				$months_old
			);
		}

		// Report findings
		if ( ! empty( $conflicts ) || ! empty( $warnings ) ) {
			$severity     = 'medium';
			$threat_level = 45;

			if ( count( $conflicts ) > 2 ) {
				$severity     = 'high';
				$threat_level = 70;
			}

			$description = __( 'Theme and plugin compatibility issues detected', 'wpshadow' );

			$details = array(
				'theme_name' => $theme,
			);

			if ( ! empty( $conflicts ) ) {
				$details['conflicts'] = $conflicts;
			}
			if ( ! empty( $warnings ) ) {
				$details['warnings'] = $warnings;
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/theme-plugin-compatibility',
				'details'      => $details,
			);
		}

		return null;
	}
}
