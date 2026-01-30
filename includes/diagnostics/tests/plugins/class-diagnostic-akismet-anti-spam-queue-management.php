<?php
/**
 * Akismet Anti Spam Queue Management Diagnostic
 *
 * Akismet Anti Spam Queue Management issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1447.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Akismet Anti Spam Queue Management Diagnostic Class
 *
 * @since 1.1447.0000
 */
class Diagnostic_AkismetAntiSpamQueueManagement extends Diagnostic_Base {

	protected static $slug = 'akismet-anti-spam-queue-management';
	protected static $title = 'Akismet Anti Spam Queue Management';
	protected static $description = 'Akismet Anti Spam Queue Management issue found';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'AKISMET_VERSION' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Spam queue size
		$spam_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved = %s",
				'spam'
			)
		);
		
		if ( $spam_count === 0 ) {
			return null;
		}
		
		if ( $spam_count > 500 ) {
			$issues[] = sprintf( __( '%d spam comments in queue (database bloat)', 'wpshadow' ), $spam_count );
		}
		
		// Check 2: Auto-delete spam setting
		$auto_delete = get_option( 'akismet_strictness', '1' );
		$delete_days = get_option( 'akismet_delete_spam_days', 15 );
		
		if ( $delete_days > 30 ) {
			$issues[] = sprintf( __( 'Spam auto-deleted after %d days (recommend 15 days)', 'wpshadow' ), $delete_days );
		}
		
		// Check 3: Old spam not purged
		$old_spam = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->comments}
				 WHERE comment_approved = %s AND comment_date < DATE_SUB(NOW(), INTERVAL 90 DAY)",
				'spam'
			)
		);
		
		if ( $old_spam > 50 ) {
			$issues[] = sprintf( __( '%d spam comments older than 90 days (cleanup needed)', 'wpshadow' ), $old_spam );
		}
		
		// Check 4: False positive tracking
		$false_positives = get_option( 'akismet_false_positive_count', 0 );
		if ( $false_positives > 20 ) {
			$issues[] = sprintf( __( '%d false positives (review strictness settings)', 'wpshadow' ), $false_positives );
		}
		
		// Check 5: API key status
		$api_status = get_option( 'akismet_connectivity_time' );
		if ( ! $api_status ) {
			$issues[] = __( 'Akismet API connectivity not verified recently', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 50;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 62;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 56;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of spam queue issues */
				__( 'Akismet spam queue has %d management issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => true,
			'kb_link'     => 'https://wpshadow.com/kb/akismet-anti-spam-queue-management',
		);
	}
}
