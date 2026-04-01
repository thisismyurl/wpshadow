<?php
/**
 * Error Logging Configuration Diagnostic
 *
 * Issue #4901: Error Logging Not Configured or Publicly Accessible
 * Pillar: 🛡️ Safe by Default
 *
 * Checks if error logging is properly configured.
 * Logs should capture errors but not be publicly accessible.
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
 * Diagnostic_Error_Logging_Configuration Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Error_Logging_Configuration extends Diagnostic_Base {

	protected static $slug = 'error-logging-configuration';
	protected static $title = 'Error Logging Not Configured or Publicly Accessible';
	protected static $description = 'Checks if errors are logged server-side and not exposed publicly';
	protected static $family = 'security';

	public static function check() {
		$issues = array();

		// Check debug configuration
		if ( ! defined( 'WP_DEBUG_LOG' ) || ! WP_DEBUG_LOG ) {
			$issues[] = __( 'Enable WP_DEBUG_LOG to capture errors', 'wpshadow' );
		}

		if ( defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY ) {
			$issues[] = __( 'Disable WP_DEBUG_DISPLAY in production (shows errors to users)', 'wpshadow' );
		}

		// Check if debug.log is accessible
		$debug_log = WP_CONTENT_DIR . '/debug.log';
		if ( file_exists( $debug_log ) ) {
			$issues[] = __( 'Debug log exists and may be publicly accessible at /wp-content/debug.log', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Error logs help debug issues but must be server-side only. Public logs expose database structure, file paths, and vulnerabilities.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/error-logging?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'findings'                => $issues,
					'recommended_config'      => 'WP_DEBUG_LOG=true, WP_DEBUG_DISPLAY=false',
					'log_location'            => 'wp-content/debug.log (block via .htaccess)',
					'htaccess_rule'           => '<Files debug.log>\n  Order allow,deny\n  Deny from all\n</Files>',
				),
			);
		}

		return null;
	}
}
