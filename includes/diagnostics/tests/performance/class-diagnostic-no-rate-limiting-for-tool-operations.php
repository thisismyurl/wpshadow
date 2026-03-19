<?php
/**
 * No Rate Limiting for Tool Operations Diagnostic
 *
 * Tests for API rate limiting implementation.
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
 * No Rate Limiting for Tool Operations Diagnostic Class
 *
 * Tests for API rate limiting implementation.
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_Rate_Limiting_For_Tool_Operations extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-rate-limiting-for-tool-operations';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'No Rate Limiting for Tool Operations';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests for API rate limiting implementation';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for rate limiter filter.
		if ( ! has_filter( 'wpshadow_operation_rate_limit' ) ) {
			$issues[] = __( 'No rate limiting filter available', 'wpshadow' );
		}

		// Check for API request throttling.
		$api_calls_per_second = get_option( '_wpshadow_api_rate_limit', 0 );

		if ( (int) $api_calls_per_second === 0 ) {
			$issues[] = __( 'No API rate limit configured - external API calls not throttled', 'wpshadow' );
		}

		// Check for internal operation throttling.
		$db_queries_per_request = get_option( '_wpshadow_db_rate_limit', 0 );

		if ( (int) $db_queries_per_request === 0 ) {
			$issues[] = __( 'No database query rate limit - batch operations not throttled', 'wpshadow' );
		}

		// Check for backpressure mechanism.
		if ( ! has_action( 'wpshadow_operation_backpressure_check' ) ) {
			$issues[] = __( 'No backpressure mechanism - operations may overload server', 'wpshadow' );
		}

		// Check for token bucket algorithm.
		$has_token_bucket = get_transient( '_wpshadow_operation_tokens_' . get_current_user_id() );

		if ( $has_token_bucket === false ) {
			$issues[] = __( 'No token bucket rate limiting - unlimited concurrent operations possible', 'wpshadow' );
		}

		// Check for concurrent operation limit.
		$max_concurrent = get_option( '_wpshadow_max_concurrent_operations', 0 );

		if ( (int) $max_concurrent === 0 ) {
			$issues[] = __( 'No concurrent operation limit - may cause resource exhaustion', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/no-rate-limiting-for-tool-operations',
			);
		}

		return null;
	}
}
