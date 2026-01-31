<?php
/**
 * Multisite Spam User Deletion Diagnostic
 *
 * Multisite Spam User Deletion misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.983.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Multisite Spam User Deletion Diagnostic Class
 *
 * @since 1.983.0000
 */
class Diagnostic_MultisiteSpamUserDeletion extends Diagnostic_Base {

	protected static $slug = 'multisite-spam-user-deletion';
	protected static $title = 'Multisite Spam User Deletion';
	protected static $description = 'Multisite Spam User Deletion misconfigured';
	protected static $family = 'security';

	public static function check() {
		if ( ! is_multisite() ) {
			return null;
		}
		
		$issues = array();

		// Check 1: Verify spam user detection is enabled
		$spam_detection = get_site_option( 'ms_spam_user_detection', false );
		if ( ! $spam_detection ) {
			$issues[] = __( 'Spam user detection not enabled', 'wpshadow' );
		}

		// Check 2: Check automatic spam user deletion schedule
		$auto_delete_schedule = wp_get_schedule( 'ms_spam_user_cleanup' );
		if ( false === $auto_delete_schedule ) {
			$issues[] = __( 'Automatic spam user deletion not scheduled', 'wpshadow' );
		}

		// Check 3: Verify spam user retention period
		$retention_days = get_site_option( 'ms_spam_user_retention_days', 0 );
		if ( $retention_days > 90 || $retention_days === 0 ) {
			$issues[] = __( 'Spam user retention period too long or unlimited', 'wpshadow' );
		}

		// Check 4: Check notification to network admins
		$notify_admins = get_site_option( 'ms_notify_spam_deletion', false );
		if ( ! $notify_admins ) {
			$issues[] = __( 'Spam user deletion notifications not enabled', 'wpshadow' );
		}

		// Check 5: Verify deletion logging
		$log_deletions = get_site_option( 'ms_log_spam_user_deletions', false );
		if ( ! $log_deletions ) {
			$issues[] = __( 'Spam user deletion logging not enabled', 'wpshadow' );
		}

		// Check 6: Check spam criteria configuration
		$spam_criteria = get_site_option( 'ms_spam_user_criteria', array() );
		if ( empty( $spam_criteria ) ) {
			$issues[] = __( 'Spam user detection criteria not configured', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 100, 70 + ( count( $issues ) * 5 ) );
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Comma-separated list of issues */
					__( 'Multisite spam user deletion issues detected: %s', 'wpshadow' ),
					implode( ', ', $issues )
				),
				'severity'     => 'high',
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/multisite-spam-user-deletion',
			);
		}

		return null;
	}
}
