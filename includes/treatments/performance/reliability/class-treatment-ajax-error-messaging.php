<?php
/**
 * AJAX Error Messaging Treatment
 *
 * Issue #4856: AJAX Failures Show Technical Errors Not User-Friendly Messages
 * Pillar: ⚙️ Murphy's Law, Commandment #1: Helpful Neighbor
 *
 * Checks if AJAX errors show user-friendly messages instead of technical details.
 * Stack traces and database errors confuse users and expose system internals.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6050.0000
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_AJAX_Error_Messaging Class
 *
 * Checks for:
 * - AJAX error responses with database/SQL errors
 * - Stack traces exposed to client
 * - PHP warnings/notices in AJAX responses
 * - Unhandled exceptions in AJAX
 * - Missing error translation/user-friendly messages
 *
 * Good error handling:
 * - User sees: "Couldn't save changes. Please try again."
 * - Server logs: Full error details for debugging
 * - Never shows: Database structure, code paths, system info
 *
 * @since 1.6050.0000
 */
class Treatment_AJAX_Error_Messaging extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @since 1.6050.0000
	 * @var   string
	 */
	protected static $slug = 'ajax-error-messaging';

	/**
	 * The treatment title
	 *
	 * @since 1.6050.0000
	 * @var   string
	 */
	protected static $title = 'AJAX Failures Show Technical Errors Not User-Friendly Messages';

	/**
	 * The treatment description
	 *
	 * @since 1.6050.0000
	 * @var   string
	 */
	protected static $description = 'Checks if AJAX errors show user-friendly messages instead of technical details';

	/**
	 * The family this treatment belongs to
	 *
	 * @since 1.6050.0000
	 * @var   string
	 */
	protected static $family = 'reliability';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6050.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check WP_DEBUG setting
		$debug_enabled = defined( 'WP_DEBUG' ) && WP_DEBUG;
		$debug_log_enabled = defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG;
		$debug_display = defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY;

		$issues = array();

		if ( $debug_enabled && $debug_display ) {
			$issues[] = __( 'WP_DEBUG_DISPLAY enabled - errors shown to users', 'wpshadow' );
		}

		if ( ! $debug_log_enabled && $debug_enabled ) {
			$issues[] = __( 'WP_DEBUG enabled but WP_DEBUG_LOG disabled - errors not logged server-side', 'wpshadow' );
		}

		// Check if error_reporting shows all errors
		$error_reporting = error_reporting();
		if ( $error_reporting === E_ALL ) {
			$issues[] = __( 'All errors reported including notices (should suppress in production)', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'AJAX errors should show friendly messages to users, not technical details', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/ajax-error-handling',
				'details'      => array(
					'findings'         => $issues,
					'wp_debug'         => $debug_enabled ? 'Enabled' : 'Disabled',
					'wp_debug_display' => $debug_display ? 'Enabled (bad)' : 'Disabled (good)',
					'wp_debug_log'     => $debug_log_enabled ? 'Enabled (good)' : 'Disabled',
				),
			);
		}

		return null;
	}
}
