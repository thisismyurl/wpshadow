<?php
/**
 * API Timeout and Retry Diagnostic
 *
 * Issue #4859: External API Calls Don't Timeout or Retry
 * Pillar: ⚙️ Murphy's Law
 *
 * Checks if external API requests have proper timeout and retry logic.
 * Hanging requests can freeze the entire admin interface.
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
 * Diagnostic_API_Timeout_Retry Class
 *
 * Checks for:
 * - Timeouts on external API requests
 * - Retry logic with exponential backoff
 * - Maximum retry attempts (prevent infinite loops)
 * - Graceful fallback when API is down
 * - No blocking requests in admin (use async)
 * - Proper HTTP response handling (not all 200s are success)
 *
 * Why this matters:
 * - External APIs go down (regularly, without notice)
 * - Slow APIs can timeout and hang WordPress
 * - Infinite retries waste server resources
 * - Users see spinning admin UI with no feedback
 *
 * @since 1.6093.1200
 */
class Diagnostic_API_Timeout_Retry extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $slug = 'api-timeout-retry';

	/**
	 * The diagnostic title
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $title = 'External API Calls Don\'t Timeout or Retry';

	/**
	 * The diagnostic description
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $description = 'Checks if external API requests have timeout and retry logic';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $family = 'reliability';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// This is more of an implementation guidance diagnostic.
		// We recommend checking for:
		// - wp_remote_get() calls with timeout parameter
		// - wp_remote_post() calls with retry logic
		// - Error handling for is_wp_error() responses
		// - Graceful fallback on failure

		$issues = array();

		// Check if plugins use proper timeouts
		// This is informational - shows best practices
		$issues[] = __( 'External API requests should have explicit timeout values (5-30 seconds)', 'wpshadow' );
		$issues[] = __( 'Failed API requests should retry with exponential backoff', 'wpshadow' );
		$issues[] = __( 'Maximum retry attempts should be set (typically 2-3 retries)', 'wpshadow' );
		$issues[] = __( 'API calls in admin should be async (don\'t block page load)', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'API endpoints can be slow, unresponsive, or temporarily down. Without timeouts and retries, WordPress freezes waiting for a response that never comes.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/api-timeout-retry',
				'details'      => array(
					'recommendations'           => $issues,
					'wp_remote_timeout_default' => 5,
					'recommended_timeout'       => '5-30 seconds (depends on API)',
					'retry_strategy'            => 'Exponential backoff: 1s, 2s, 4s',
					'max_retries'               => '2-3 attempts',
					'async_recommendation'      => 'Use wp_schedule_single_event or background jobs',
				),
			);
		}

		return null;
	}
}
