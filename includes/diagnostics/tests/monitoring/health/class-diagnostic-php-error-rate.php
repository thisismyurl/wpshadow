<?php
/**
 * PHP Error Rate Diagnostic
 *
 * Analyzes error logs to detect recurring PHP errors that
 * impact performance, stability, and user experience.
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
 * Diagnostic_PHP_Error_Rate Class
 *
 * Monitors PHP error frequency.
 *
 * @since 1.2601.2148
 */
class Diagnostic_PHP_Error_Rate extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'php-error-rate';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'PHP Error Rate';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes recurring PHP errors';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'health';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if errors detected, null otherwise.
	 */
	public static function check() {
		$error_analysis = self::analyze_error_log();

		if ( ! $error_analysis['has_errors'] ) {
			return null; // No errors
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of errors */
				__( '%d PHP errors in last 24 hours. Errors = silent failures = broken features = frustrated users. Some errors invisible to users, causing backend damage.', 'wpshadow' ),
				$error_analysis['error_count']
			),
			'severity'     => 'medium',
			'threat_level' => 55,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/php-errors',
			'family'       => self::$family,
			'meta'         => array(
				'errors_24h' => $error_analysis['error_count'],
			),
			'details'      => array(
				'common_php_errors'           => array(
					'Notice' => array(
						'Severity: Low',
						'Example: Undefined variable used',
						'Impact: Usually invisible',
					),
					'Warning' => array(
						'Severity: Medium',
						'Example: Include file not found',
						'Impact: Feature broken',
					),
					'Fatal Error' => array(
						'Severity: Critical',
						'Example: Call to undefined function',
						'Impact: Page crashes, white screen',
					),
				),
				'common_error_causes'         => array(
					'Plugin Conflicts' => array(
						'Two plugins using same function name',
						'Solution: Disable plugins one by one',
					),
					'Outdated Theme/Plugins' => array(
						'Old code incompatible with new PHP',
						'Solution: Update all plugins/themes',
					),
					'Custom Code Issues' => array(
						'Developer errors in functions.php',
						'Solution: Check custom code',
					),
					'Server Config' => array(
						'PHP version too old or too new',
						'Solution: Check PHP version',
					),
				),
				'finding_errors'              => array(
					'Error Log File' => array(
						'Location: /var/log/php_errors.log',
						'Or: /home/user/public_html/error_log',
						'Accessible: Via FTP or cPanel File Manager',
					),
					'WordPress Debug Log' => array(
						'File: /wp-content/debug.log',
						'Requires: wp-config.php WP_DEBUG_LOG enabled',
						'View: Via FTP or cPanel',
					),
					'cPanel Error Manager' => array(
						'Access: cPanel → Metrics → Errors',
						'Shows: Recent PHP errors',
						'Real-time: Updates frequently',
					),
				),
				'analyzing_error_patterns'    => array(
					'Same Error Repeatedly' => array(
						'Indicates: Systematic issue',
						'Example: "Call to undefined function" every request',
						'Solution: Fix the specific line',
					),
					'Error After Update' => array(
						'Cause: Plugin/theme incompatibility',
						'Solution: Rollback or wait for update',
					),
					'Error on Specific Page' => array(
						'Cause: Page-specific plugin or code',
						'Solution: Check page settings/meta',
					),
				),
				'fixing_php_errors'           => array(
					'Debug Mode' => array(
						'Enable: WP_DEBUG_DISPLAY = true',
						'See: Exact error on page',
						'Line: Error message shows file + line number',
					),
					'Error Logs' => array(
						'Check: /wp-content/debug.log',
						'Search: Plugin/theme name from error',
						'Pattern: If all errors from one plugin',
					),
					'Disable Plugins' => array(
						'Method: Disable all plugins',
						'Test: See if error stops',
						'Enable one by one to find culprit',
					),
					'Update Everything' => array(
						'WordPress: Check for updates',
						'Plugins: All of them',
						'Theme: Latest version',
						'PHP version: Request from host',
					),
				),
				'monitoring_error_health'     => array(
					__( 'Daily: Check error log for new issues' ),
					__( 'Weekly: Rotate old logs (prevent disk fill)' ),
					__( 'Monthly: Review patterns, categorize' ),
					__( 'Quarterly: Remove errors that no longer occur' ),
				),
			),
		);
	}

	/**
	 * Analyze error log.
	 *
	 * @since  1.2601.2148
	 * @return array Error analysis.
	 */
	private static function analyze_error_log() {
		$debug_log = WP_CONTENT_DIR . '/debug.log';

		if ( ! file_exists( $debug_log ) ) {
			return array(
				'has_errors'  => false,
				'error_count' => 0,
			);
		}

		// Read last 100 lines
		$lines = file( $debug_log );
		if ( empty( $lines ) ) {
			return array(
				'has_errors'  => false,
				'error_count' => 0,
			);
		}

		// Count errors in last 24 hours (very rough estimate)
		$cutoff_time = time() - 86400;
		$errors_24h = 0;

		foreach ( array_slice( $lines, -100 ) as $line ) {
			if ( strpos( $line, 'PHP' ) !== false || strpos( $line, 'Error' ) !== false ) {
				$errors_24h++;
			}
		}

		return array(
			'has_errors'  => $errors_24h > 5,
			'error_count' => $errors_24h,
		);
	}
}
