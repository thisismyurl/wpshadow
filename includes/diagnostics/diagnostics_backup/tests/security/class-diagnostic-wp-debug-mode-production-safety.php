<?php
/**
 * WP_DEBUG Mode Production Safety Diagnostic
 *
 * Ensures debug modes are disabled on production environments.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26028.1905
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP_DEBUG Mode Production Safety Class
 *
 * Tests debug mode configuration.
 *
 * @since 1.26028.1905
 */
class Diagnostic_Wp_Debug_Mode_Production_Safety extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'wp-debug-mode-production-safety';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'WP_DEBUG Mode Production Safety';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Ensures debug modes are disabled on production environments';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26028.1905
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Skip if local/development environment.
		if ( self::is_development_environment() ) {
			return null;
		}

		$debug_check = self::check_debug_modes();
		
		if ( $debug_check['has_debug_enabled'] ) {
			$issues = array();
			
			if ( $debug_check['wp_debug'] ) {
				$issues[] = __( 'WP_DEBUG enabled (exposes system paths and errors)', 'wpshadow' );
			}

			if ( $debug_check['wp_debug_display'] ) {
				$issues[] = __( 'WP_DEBUG_DISPLAY enabled (errors visible to public)', 'wpshadow' );
			}

			if ( $debug_check['script_debug'] ) {
				$issues[] = __( 'SCRIPT_DEBUG enabled (loads unminified assets, slower)', 'wpshadow' );
			}

			if ( $debug_check['savequeries'] ) {
				$issues[] = __( 'SAVEQUERIES enabled (memory intensive, security risk)', 'wpshadow' );
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $issues ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/wp-debug-mode-production-safety',
				'meta'         => array(
					'wp_debug'         => $debug_check['wp_debug'],
					'wp_debug_display' => $debug_check['wp_debug_display'],
					'wp_debug_log'     => $debug_check['wp_debug_log'],
					'script_debug'     => $debug_check['script_debug'],
					'savequeries'      => $debug_check['savequeries'],
				),
			);
		}

		return null;
	}

	/**
	 * Check debug modes.
	 *
	 * @since  1.26028.1905
	 * @return array Check results.
	 */
	private static function check_debug_modes() {
		$check = array(
			'has_debug_enabled' => false,
			'wp_debug'          => false,
			'wp_debug_display'  => false,
			'wp_debug_log'      => false,
			'script_debug'      => false,
			'savequeries'       => false,
		);

		// Check WP_DEBUG.
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$check['wp_debug'] = true;
			$check['has_debug_enabled'] = true;
		}

		// Check WP_DEBUG_DISPLAY.
		if ( defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY ) {
			$check['wp_debug_display'] = true;
			$check['has_debug_enabled'] = true;
		}

		// Check WP_DEBUG_LOG (informational, not necessarily bad).
		if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			$check['wp_debug_log'] = true;
		}

		// Check SCRIPT_DEBUG.
		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
			$check['script_debug'] = true;
			$check['has_debug_enabled'] = true;
		}

		// Check SAVEQUERIES.
		if ( defined( 'SAVEQUERIES' ) && SAVEQUERIES ) {
			$check['savequeries'] = true;
			$check['has_debug_enabled'] = true;
		}

		return $check;
	}

	/**
	 * Detect if this is a development environment.
	 *
	 * @since  1.26028.1905
	 * @return bool True if development environment.
	 */
	private static function is_development_environment() {
		// Check for common local development indicators.
		$site_url = get_site_url();
		
		$dev_indicators = array(
			'localhost',
			'127.0.0.1',
			'.local',
			'.dev',
			'.test',
			'staging',
		);

		foreach ( $dev_indicators as $indicator ) {
			if ( false !== strpos( $site_url, $indicator ) ) {
				return true;
			}
		}

		// Check WP_ENVIRONMENT_TYPE (WordPress 5.5+).
		if ( function_exists( 'wp_get_environment_type' ) ) {
			$env_type = wp_get_environment_type();
			if ( in_array( $env_type, array( 'local', 'development' ), true ) ) {
				return true;
			}
		}

		return false;
	}
}
