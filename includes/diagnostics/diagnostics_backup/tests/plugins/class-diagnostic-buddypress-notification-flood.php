<?php
/**
 * BuddyPress Notification Flood Diagnostic
 *
 * BuddyPress notifications flooding users.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.516.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BuddyPress Notification Flood Diagnostic Class
 *
 * @since 1.516.0000
 */
class Diagnostic_BuddypressNotificationFlood extends Diagnostic_Base {

	protected static $slug = 'buddypress-notification-flood';
	protected static $title = 'BuddyPress Notification Flood';
	protected static $description = 'BuddyPress notifications flooding users';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'BuddyPress' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Notification throttling enabled
		$throttling = get_option( 'bp_notification_throttling', false );
		if ( ! $throttling ) {
			$issues[] = 'Notification throttling disabled';
		}

		// Check 2: Rate limiting configured
		$rate_limit = get_option( 'bp_notification_rate_limit', 0 );
		if ( $rate_limit <= 0 ) {
			$issues[] = 'Rate limiting not configured';
		}

		// Check 3: Digest mode available
		$digest_mode = get_option( 'bp_notification_digest_mode', false );
		if ( ! $digest_mode ) {
			$issues[] = 'Digest mode not available';
		}

		// Check 4: Notification frequency limits
		$frequency_limit = get_option( 'bp_notification_frequency_limit', '' );
		if ( empty( $frequency_limit ) ) {
			$issues[] = 'Frequency limits not set';
		}

		// Check 5: Muting options enabled
		$muting_options = get_option( 'bp_notification_muting_enabled', false );
		if ( ! $muting_options ) {
			$issues[] = 'Muting options not enabled';
		}

		// Check 6: Batch sending configured
		$batch_sending = get_option( 'bp_notification_batch_sending', false );
		if ( ! $batch_sending ) {
			$issues[] = 'Batch sending not configured';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 65, 35 + ( count( $issues ) * 5 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'BuddyPress notification flood issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/buddypress-notification-flood',
			);
		}
		// Verify core functionality
		if ( ! function_exists( 'get_post' ) ) {
			$issues[] = __( 'Post functionality not available', 'wpshadow' );
		}
		return null;
	}
}
