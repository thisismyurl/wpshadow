<?php
/**
 * WP_DEBUG Mode Status Diagnostic
 *
 * Detects when WP_DEBUG is disabled in production, hiding critical errors,
 * or enabled in production, exposing sensitive information publicly.
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
 * Diagnostic_WP_Debug_Status Class
 *
 * Monitors WP_DEBUG configuration.
 *
 * @since 1.2601.2148
 */
class Diagnostic_WP_Debug_Status extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'wp-debug-status';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'WP_DEBUG Mode Status';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Monitors WP_DEBUG configuration';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'developer';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if misconfigured, null otherwise.
	 */
	public static function check() {
		$debug_status = self::check_debug_configuration();

		if ( ! $debug_status['has_issue'] ) {
			return null; // Debug configured properly
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => $debug_status['message'],
			'severity'     => $debug_status['severity'],
			'threat_level' => $debug_status['threat_level'],
			'auto_fixable' => true,
			'kb_link'      => 'https://wpshadow.com/kb/wp-debug',
			'family'       => self::$family,
			'meta'         => array(
				'wp_debug'          => WP_DEBUG ? 'enabled' : 'disabled',
				'wp_debug_display'  => defined( 'WP_DEBUG_DISPLAY' ) ? ( WP_DEBUG_DISPLAY ? 'enabled' : 'disabled' ) : 'default',
				'wp_debug_log'      => defined( 'WP_DEBUG_LOG' ) ? ( WP_DEBUG_LOG ? 'enabled' : 'disabled' ) : 'default',
				'environment'       => $debug_status['environment'],
			),
			'details'      => array(
				'wp_debug_constants'        => array(
					'WP_DEBUG' => array(
						'Purpose: Enable PHP error reporting',
						'Production: false (hide errors)',
						'Development: true (show errors)',
					),
					'WP_DEBUG_DISPLAY' => array(
						'Purpose: Show errors on screen',
						'Production: false (NEVER show publicly)',
						'Development: true (see errors immediately)',
					),
					'WP_DEBUG_LOG' => array(
						'Purpose: Log errors to /wp-content/debug.log',
						'Production: true (log without displaying)',
						'Development: true (keep error history)',
					),
					'SCRIPT_DEBUG' => array(
						'Purpose: Use unminified JS/CSS',
						'Production: false (use minified)',
						'Development: true (easier debugging)',
					),
				),
				'production_configuration'  => array(
					'Recommended wp-config.php' => array(
						'define( \'WP_DEBUG\', false );',
						'define( \'WP_DEBUG_DISPLAY\', false );',
						'define( \'WP_DEBUG_LOG\', true );',
						'@ini_set( \'display_errors\', 0 );',
					),
					'Why This Setup' => array(
						'WP_DEBUG false: No performance overhead',
						'WP_DEBUG_LOG true: Errors saved for investigation',
						'WP_DEBUG_DISPLAY false: No public error exposure',
					),
				),
				'development_configuration' => array(
					'Recommended wp-config.php' => array(
						'define( \'WP_DEBUG\', true );',
						'define( \'WP_DEBUG_DISPLAY\', true );',
						'define( \'WP_DEBUG_LOG\', true );',
						'define( \'SCRIPT_DEBUG\', true );',
						'@ini_set( \'display_errors\', 1 );',
					),
					'Why This Setup' => array(
						'Catch errors immediately',
						'See exact line numbers',
						'Test with unminified scripts',
					),
				),
				'dangers_of_debug_in_production' => array(
					__( 'Exposes file paths (/var/www/html/wp-content/...)' ),
					__( 'Shows database queries (potential SQL injection clues)' ),
					__( 'Reveals plugin/theme structure' ),
					__( 'Displays sensitive variable values' ),
					__( 'Makes site look unprofessional' ),
				),
				'accessing_debug_log'       => array(
					'Via FTP/File Manager' => '/wp-content/debug.log',
					'Via WP-CLI' => 'wp eval "echo file_get_contents(WP_CONTENT_DIR . \'/debug.log\');"',
					'Via Plugin' => 'Debug Log Manager (free)',
				),
			),
		);
	}

	/**
	 * Check debug configuration.
	 *
	 * @since  1.2601.2148
	 * @return array Debug configuration analysis.
	 */
	private static function check_debug_configuration() {
		// Determine environment (production vs development)
		// Production indicators: HTTPS, no localhost, domain looks real
		$home_url = home_url();
		$is_localhost = strpos( $home_url, 'localhost' ) !== false || strpos( $home_url, '127.0.0.1' ) !== false;
		$is_https = is_ssl();
		$is_production = ! $is_localhost && $is_https;

		$has_issue = false;
		$message = '';
		$severity = 'info';
		$threat_level = 20;

		if ( $is_production ) {
			// Production site
			if ( WP_DEBUG && ( ! defined( 'WP_DEBUG_DISPLAY' ) || WP_DEBUG_DISPLAY ) ) {
				$has_issue = true;
				$message = __( 'WP_DEBUG enabled with public error display on PRODUCTION site. Exposes file paths, database queries, sensitive data. Security risk + unprofessional.', 'wpshadow' );
				$severity = 'critical';
				$threat_level = 80;
			} elseif ( ! WP_DEBUG ) {
				$has_issue = true;
				$message = __( 'WP_DEBUG completely disabled in production. Errors not logged = can\'t troubleshoot issues. Enable WP_DEBUG + WP_DEBUG_LOG, disable WP_DEBUG_DISPLAY.', 'wpshadow' );
				$severity = 'medium';
				$threat_level = 45;
			}
		} else {
			// Development/staging site
			if ( ! WP_DEBUG ) {
				$has_issue = true;
				$message = __( 'WP_DEBUG disabled on development site. Missing errors during development = bugs in production. Enable WP_DEBUG, WP_DEBUG_DISPLAY, WP_DEBUG_LOG.', 'wpshadow' );
				$severity = 'medium';
				$threat_level = 40;
			}
		}

		return array(
			'has_issue'     => $has_issue,
			'message'       => $message,
			'severity'      => $severity,
			'threat_level'  => $threat_level,
			'environment'   => $is_production ? 'production' : 'development',
		);
	}
}
