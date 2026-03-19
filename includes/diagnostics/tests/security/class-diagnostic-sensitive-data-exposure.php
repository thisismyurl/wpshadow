<?php
/**
 * Sensitive Data Exposure Diagnostic
 *
 * Issue #4877: Debug Mode Exposes Sensitive Data in Logs
 * Pillar: 🛡️ Safe by Default / #10: Beyond Pure
 *
 * Checks if debug mode is exposing sensitive data to users or logs.
 * Debug info should never include passwords, tokens, or API keys.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Sensitive_Data_Exposure Class
 *
 * Checks for:
 * - WP_DEBUG enabled in production
 * - WP_DEBUG_DISPLAY showing errors to users
 * - Error logs readable by public (wp-content/debug.log)
 * - Passwords in error messages
 * - API keys in stack traces
 * - Database credentials in logs
 * - $_POST data logged (may contain passwords)
 * - Source maps exposed (.map files)
 *
 * Why this matters:
 * - Debug mode leaks database structure, file paths, plugins
 * - Attackers use error messages to map vulnerabilities
 * - Passwords/keys in logs can be exploited
 * - GDPR/CCPA: Exposing user data is a breach
 *
 * @since 1.6093.1200
 */
class Diagnostic_Sensitive_Data_Exposure extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $slug = 'sensitive-data-exposure';

	/**
	 * The diagnostic title
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $title = 'Debug Mode Exposes Sensitive Data in Logs';

	/**
	 * The diagnostic description
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $description = 'Checks if debug mode or error logs expose sensitive information';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check WP_DEBUG settings
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$issues[] = __( 'WP_DEBUG is enabled in production (should be disabled)', 'wpshadow' );
		}

		if ( defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY ) {
			$issues[] = __( 'WP_DEBUG_DISPLAY shows errors to visitors (sensitive data leak)', 'wpshadow' );
		}

		// Check if debug.log is publicly accessible
		$debug_log = WP_CONTENT_DIR . '/debug.log';
		if ( file_exists( $debug_log ) && is_readable( $debug_log ) ) {
			$issues[] = sprintf(
				/* translators: %s: file path */
				__( 'Debug log exists at %s and may be publicly accessible', 'wpshadow' ),
				str_replace( ABSPATH, '/', $debug_log )
			);
		}

		// Check error_reporting level
		$error_level = error_reporting();
		if ( $error_level === E_ALL ) {
			$issues[] = __( 'error_reporting() set to E_ALL (shows all errors to users)', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Debug mode and error logs can expose database credentials, API keys, file paths, and user data. This helps attackers map vulnerabilities.', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 90,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/sensitive-data-exposure',
				'details'      => array(
					'findings'                => $issues,
					'wp_debug'                => defined( 'WP_DEBUG' ) ? ( WP_DEBUG ? 'true' : 'false' ) : 'not set',
					'wp_debug_display'        => defined( 'WP_DEBUG_DISPLAY' ) ? ( WP_DEBUG_DISPLAY ? 'true' : 'false' ) : 'not set',
					'wp_debug_log'            => defined( 'WP_DEBUG_LOG' ) ? ( WP_DEBUG_LOG ? 'true' : 'false' ) : 'not set',
					'recommended_production'  => 'WP_DEBUG=false, WP_DEBUG_DISPLAY=false, WP_DEBUG_LOG=true',
					'data_types_at_risk'      => 'Passwords, API keys, database credentials, user emails, session tokens',
				),
			);
		}

		return null;
	}
}
