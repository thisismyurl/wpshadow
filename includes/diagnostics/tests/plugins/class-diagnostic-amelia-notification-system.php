<?php
/**
 * Amelia Notification System Diagnostic
 *
 * Amelia notifications not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.468.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Amelia Notification System Diagnostic Class
 *
 * @since 1.468.0000
 */
class Diagnostic_AmeliaNotificationSystem extends Diagnostic_Base {

	protected static $slug = 'amelia-notification-system';
	protected static $title = 'Amelia Notification System';
	protected static $description = 'Amelia notifications not optimized';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'AMELIA_VERSION' ) ) {
			return null;
		}
		$issues = array();
		$notification_enabled = get_option( 'amelia_notifications_enabled', 1 );
		if ( '0' === $notification_enabled ) { $issues[] = 'notifications globally disabled'; }
		$email_queue = get_option( 'amelia_email_queue_processing', 1 );
		if ( '0' === $email_queue ) { $issues[] = 'email queue processing disabled'; }
		$sms_enabled = get_option( 'amelia_sms_notifications_enabled', 0 );
		if ( '0' === $sms_enabled ) { $issues[] = 'SMS notifications not configured'; }
		$notification_templates = get_option( 'amelia_notification_templates', array() );
		if ( empty( $notification_templates ) ) { $issues[] = 'no custom notification templates'; }
		$queue_size = get_option( 'amelia_notification_queue_size', 0 );
		if ( $queue_size > 100 ) { $issues[] = "notification queue backlog ({$queue_size} items)"; }
		$log_notifications = get_option( 'amelia_log_notifications', 0 );
		if ( '0' === $log_notifications ) { $issues[] = 'notification logging disabled'; }
		if ( ! empty( $issues ) ) {
			return array( 'id' => self::$slug, 'title' => self::$title, 'description' => implode( ', ', $issues ), 'severity' => self::calculate_severity( min( 65, 45 + ( count( $issues ) * 3 ) ) ), 'threat_level' => min( 65, 45 + ( count( $issues ) * 3 ) ), 'auto_fixable' => false, 'kb_link' => 'https://wpshadow.com/kb/amelia-notification-system' );
		}
		return null;
	}
}
