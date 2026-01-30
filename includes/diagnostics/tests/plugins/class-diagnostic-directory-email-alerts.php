<?php
/**
 * Directory Email Alerts Diagnostic
 *
 * Directory email alerts excessive.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.567.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Directory Email Alerts Diagnostic Class
 *
 * @since 1.567.0000
 */
class Diagnostic_DirectoryEmailAlerts extends Diagnostic_Base {

	protected static $slug = 'directory-email-alerts';
	protected static $title = 'Directory Email Alerts';
	protected static $description = 'Directory email alerts excessive';
	protected static $family = 'performance';

	public static function check() {
		if ( ! function_exists( 'wpbdp' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Email alerts enabled
		$alerts_enabled = get_option( 'wpbdp_email_alerts_enabled', false );
		if ( ! $alerts_enabled ) {
			return null; // No alerts, no performance concern
		}
		
		// Check 2: Throttling configured
		$throttle_limit = get_option( 'wpbdp_email_throttle_limit', 0 );
		if ( $throttle_limit === 0 ) {
			$issues[] = __( 'No email throttling configured (may trigger spam filters)', 'wpshadow' );
		}
		
		// Check 3: Digest mode available
		$digest_mode = get_option( 'wpbdp_email_digest_enabled', false );
		if ( ! $digest_mode ) {
			$issues[] = __( 'Digest mode not enabled (sends individual emails)', 'wpshadow' );
		}
		
		// Check 4: Alert frequency
		$frequency = get_option( 'wpbdp_email_alert_frequency', 'instant' );
		if ( $frequency === 'instant' ) {
			$issues[] = __( 'Instant email alerts (high server load for active directories)', 'wpshadow' );
		}
		
		// Check 5: Email queue
		global $wpdb;
		$queue_size = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->prefix}wpbdp_email_queue WHERE status = 'pending'"
		);
		
		if ( $queue_size > 100 ) {
			$issues[] = sprintf( __( '%d emails in queue (processing delay or throttling issue)', 'wpshadow' ), $queue_size );
		}
		
		// Check 6: Unsubscribe option
		$unsubscribe = get_option( 'wpbdp_email_unsubscribe_enabled', false );
		if ( ! $unsubscribe ) {
			$issues[] = __( 'One-click unsubscribe not available (CAN-SPAM compliance)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 45;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 58;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 52;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of performance issues */
				__( 'Directory email alerts have %d performance issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => true,
			'kb_link'     => 'https://wpshadow.com/kb/directory-email-alerts',
		);
	}
}
