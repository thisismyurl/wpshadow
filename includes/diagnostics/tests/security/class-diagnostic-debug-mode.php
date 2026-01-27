<?php
/**
 * Diagnostic: Debug Mode Detection
 *
 * Checks if WP_DEBUG is enabled in production environments.
 * Exposes sensitive error messages, file paths, and database queries to potential attackers.
 *
 * Philosophy: Security isn't optional (#1 Helpful Neighbor, #10 Beyond Pure)
 * KB Link: https://wpshadow.com/kb/security-debug-mode
 * Training: https://wpshadow.com/training/security-debug-mode
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Debug Mode Diagnostic Class
 *
 * Detects WP_DEBUG enabled in production, exposing error messages and paths.
 */
class Diagnostic_Debug_Mode extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'debug-mode';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Debug Mode Enabled in Production';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'WordPress debug mode is enabled, exposing sensitive error messages and file paths.';

	/**
	 * Run the diagnostic check
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		// Check if WP_DEBUG is enabled
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
			return null; // All good, debug is off
		}

		// Calculate threat level based on what's exposed
		$threat_level = 80; // Base threat
		$exposed      = array();

		if ( defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY ) {
			$exposed[]     = __( 'Error messages displayed on screen', 'wpshadow' );
			$threat_level += 10; // Higher risk when displayed publicly
		}

		if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			$exposed[] = __( 'Errors logged to debug.log file', 'wpshadow' );
		}

		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
			$exposed[] = __( 'Unminified JavaScript/CSS files loaded', 'wpshadow' );
		}

		$message = sprintf(
			/* translators: 1: list of exposed debug settings */
			__( 'Debug mode is enabled on your production site. This exposes: %s. Attackers can use this information to find vulnerabilities.', 'wpshadow' ),
			implode( ', ', $exposed )
		);

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => $message,
			'severity'    => 'high',
			'threat_level' => min( $threat_level, 100 ),
			'auto_fixable' => true,
			'kb_link'     => 'https://wpshadow.com/kb/security-debug-mode',
			'training_link' => 'https://wpshadow.com/training/security-debug-mode',
			'impact'      => array(
				'security'    => __( 'Exposes sensitive information to attackers', 'wpshadow' ),
				'performance' => __( 'Unminified assets slow page load times', 'wpshadow' ),
			),
			'evidence'    => array(
				'WP_DEBUG'         => WP_DEBUG ? 'true' : 'false',
				'WP_DEBUG_DISPLAY' => defined( 'WP_DEBUG_DISPLAY' ) ? ( WP_DEBUG_DISPLAY ? 'true' : 'false' ) : 'undefined',
				'WP_DEBUG_LOG'     => defined( 'WP_DEBUG_LOG' ) ? ( WP_DEBUG_LOG ? 'true' : 'false' ) : 'undefined',
				'SCRIPT_DEBUG'     => defined( 'SCRIPT_DEBUG' ) ? ( SCRIPT_DEBUG ? 'true' : 'false' ) : 'undefined',
			),
		);
	}

	/**
	 * Get available treatments for this diagnostic
	 *
	 * @since  1.2601.2148
	 * @return array Array of treatment class names.
	 */
	public static function get_available_treatments(): array {
		return array(
			'WPShadow\\Treatments\\Treatment_Debug_Mode',
		);
	}
}
