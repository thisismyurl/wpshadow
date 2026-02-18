<?php
/**
 * No Retry Mechanism for Failed Tool Operations Diagnostic
 *
 * Tests for retry capability on tool operation failures.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6033.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * No Retry Mechanism for Failed Tool Operations Diagnostic Class
 *
 * Tests for retry capability on tool operation failures.
 *
 * @since 1.6033.0000
 */
class Diagnostic_No_Retry_Mechanism_For_Failed_Tool_Operations extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-retry-mechanism-for-failed-tool-operations';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'No Retry Mechanism for Failed Tool Operations';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests for retry capability on tool operation failures';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for retry hook/filter availability.
		$has_retry_filter = has_filter( 'wpshadow_retry_failed_operation' );

		if ( ! $has_retry_filter ) {
			$issues[] = __( 'No retry filter available - failed operations cannot be retried', 'wpshadow' );
		}

		// Check for scheduled retry events.
		$scheduled_retries = wp_get_scheduled_hook( 'wpshadow_retry_failed_operations' );

		if ( empty( $scheduled_retries ) ) {
			$issues[] = __( 'No retry scheduling configured - failed operations will not retry', 'wpshadow' );
		}

		// Check for failed operation logging.
		$failed_ops = get_transient( '_wpshadow_failed_operations' );

		if ( empty( $failed_ops ) || ! is_array( $failed_ops ) ) {
			// This could be good (no failures) or bad (not logging).
			// Check if logging is configured.
			if ( ! has_action( 'wpshadow_operation_failed' ) ) {
				$issues[] = __( 'No operation failure logging configured', 'wpshadow' );
			}
		}

		// Check for exponential backoff support.
		$has_backoff = has_filter( 'wpshadow_retry_delay' );

		if ( ! $has_backoff ) {
			$issues[] = __( 'No exponential backoff available - retries may hammer the server', 'wpshadow' );
		}

		// Check for max retry limit configuration.
		$max_retries = get_option( '_wpshadow_max_operation_retries', 0 );

		if ( (int) $max_retries === 0 ) {
			$issues[] = __( 'No maximum retry limit configured - operations may retry infinitely', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/no-retry-mechanism-for-failed-tool-operations',
			);
		}

		return null;
	}
}
