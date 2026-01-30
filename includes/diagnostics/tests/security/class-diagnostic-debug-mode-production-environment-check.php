<?php
/**
 * Debug Mode Production Environment Check Diagnostic
 *
 * Ensures debug modes are disabled on production environments.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26029.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Debug Mode Production Environment Check Class
 *
 * Tests debug mode configuration.
 *
 * @since 1.26029.0000
 */
class Diagnostic_Debug_Mode_Production_Environment_Check extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'debug-mode-production-environment-check';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Debug Mode Production Environment Check';

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
	 * @since  1.26029.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$debug_check = self::check_debug_mode();
		
		if ( $debug_check['has_issues'] ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $debug_check['issues'] ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/debug-mode-production-environment-check',
				'meta'         => array(
					'debug_enabled'         => $debug_check['debug_enabled'],
					'debug_display_enabled' => $debug_check['debug_display_enabled'],
					'debug_log_accessible'  => $debug_check['debug_log_accessible'],
				),
			);
		}

		return null;
	}

	/**
	 * Check debug mode configuration.
	 *
	 * @since  1.26029.0000
	 * @return array Check results.
	 */
	private static function check_debug_mode() {
		$check = array(
			'has_issues'            => false,
			'issues'                => array(),
			'debug_enabled'         => false,
			'debug_display_enabled' => false,
			'debug_log_accessible'  => false,
		);

		// Check WP_DEBUG.
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$check['has_issues'] = true;
			$check['debug_enabled'] = true;
			$check['issues'][] = __( 'WP_DEBUG enabled on production (exposes system paths and errors)', 'wpshadow' );
		}

		// Check WP_DEBUG_DISPLAY.
		if ( defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY ) {
			$check['has_issues'] = true;
			$check['debug_display_enabled'] = true;
			$check['issues'][] = __( 'WP_DEBUG_DISPLAY enabled (shows errors to visitors, information disclosure)', 'wpshadow' );
		}

		// Check SCRIPT_DEBUG.
		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
			$check['has_issues'] = true;
			$check['issues'][] = __( 'SCRIPT_DEBUG enabled (loads unminified assets, slower performance)', 'wpshadow' );
		}

		// Check SAVEQUERIES.
		if ( defined( 'SAVEQUERIES' ) && SAVEQUERIES ) {
			$check['has_issues'] = true;
			$check['issues'][] = __( 'SAVEQUERIES enabled (stores all queries in memory, can exhaust RAM)', 'wpshadow' );
		}

		// Check if debug.log is accessible.
		$debug_log = WP_CONTENT_DIR . '/debug.log';
		
		if ( file_exists( $debug_log ) ) {
			$debug_log_url = content_url( 'debug.log' );
			$response = wp_remote_get( $debug_log_url, array(
				'timeout'     => 5,
				'redirection' => 0,
			) );

			if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
				$check['has_issues'] = true;
				$check['debug_log_accessible'] = true;
				$check['issues'][] = __( 'debug.log is publicly accessible (exposes errors and system information)', 'wpshadow' );
			}
		}

		return $check;
	}
}
