<?php
/**
 * Tool Debug Mode Settings Diagnostic
 *
 * Detects whether tools provide enhanced debugging information when
 * WP_DEBUG is enabled.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6033.1900
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tool Debug Mode Settings Diagnostic Class
 *
 * Ensures tools respect WP_DEBUG settings and provide enhanced logging
 * and error reporting when debug mode is enabled.
 *
 * @since 1.6033.1900
 */
class Diagnostic_Tool_Debug_Mode_Settings extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'tool-debug-mode-settings';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Tools Respecting Debug Mode Settings';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies tools provide enhanced debugging when WP_DEBUG is enabled';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'tools';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks:
	 * - WP_DEBUG is enabled for development sites
	 * - Tool error handling respects debug flag
	 * - Debug logging is configured
	 * - Error display settings are appropriate
	 *
	 * @since  1.6033.1900
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if WP_DEBUG is enabled.
		$wp_debug_enabled = defined( 'WP_DEBUG' ) && WP_DEBUG;
		$wp_debug_log     = defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG;
		$wp_debug_display = defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY;

		// For development sites, WP_DEBUG should typically be enabled.
		if ( ! wp_is_development_mode() && ! $wp_debug_enabled ) {
			// This is production, so debug mode being off is expected and OK.
			return null;
		}

		// If in development mode or WP_DEBUG is on, check for proper logging.
		if ( wp_is_development_mode() || $wp_debug_enabled ) {
			if ( ! $wp_debug_log ) {
				$issues[] = __( 'WP_DEBUG_LOG is not enabled; tool errors may not be logged to file for debugging', 'wpshadow' );
			}

			// Check if WPShadow has debug logging enabled.
			$shadow_debug = get_option( 'wpshadow_debug_mode', false );
			if ( ! $shadow_debug ) {
				$issues[] = __( 'WPShadow debug mode is not enabled; tool operations will not provide enhanced logging', 'wpshadow' );
			}

			// Check if error display is properly configured.
			if ( $wp_debug_display && defined( 'WP_DEBUG_DISPLAY' ) ) {
				// In development, showing errors is OK, but file logging is preferred.
				if ( ! $wp_debug_log ) {
					$issues[] = __( 'Errors are displayed on screen (WP_DEBUG_DISPLAY=true) but not logged to file; consider enabling WP_DEBUG_LOG', 'wpshadow' );
				}
			}
		}

		// Check if tool operations create descriptive errors in logs.
		$log_file = defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ? WP_CONTENT_DIR . '/debug.log' : false;
		if ( $log_file && file_exists( $log_file ) ) {
			$log_content = file_get_contents( $log_file, false, null, 0, 5000 );
			if ( is_string( $log_content ) ) {
				// Count WPShadow entries - should have some if tool operations occurred.
				$wpshadow_entries = substr_count( strtolower( $log_content ), 'wpshadow' );
				if ( $wpshadow_entries === 0 ) {
					// If debug log exists but no WPShadow entries, tools may not be logging.
					$issues[] = __( 'No WPShadow debug entries found in debug.log; tools may not be logging debug information', 'wpshadow' );
				}
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/tool-debug-mode-settings',
			);
		}

		return null;
	}
}
