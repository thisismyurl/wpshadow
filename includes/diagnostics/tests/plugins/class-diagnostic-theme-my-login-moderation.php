<?php
/**
 * Theme My Login Moderation Diagnostic
 *
 * Theme My Login Moderation issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1234.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme My Login Moderation Diagnostic Class
 *
 * @since 1.1234.0000
 */
class Diagnostic_ThemeMyLoginModeration extends Diagnostic_Base {

	protected static $slug = 'theme-my-login-moderation';
	protected static $title = 'Theme My Login Moderation';
	protected static $description = 'Theme My Login Moderation issue found';
	protected static $family = 'functionality';

	public static function check() {
		// Check for Theme My Login plugin
		if ( ! function_exists( 'tml_get_option' ) && ! class_exists( 'Theme_My_Login' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: User moderation enabled
		$moderation = get_option( 'tml_user_moderation', 'disabled' );
		if ( 'disabled' === $moderation ) {
			return null;
		}
		
		// Check 2: Pending users count
		$pending_users = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->users} u
				 INNER JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
				 WHERE um.meta_key = %s AND um.meta_value = %s",
				'tml_user_status',
				'pending'
			)
		);
		
		if ( $pending_users > 50 ) {
			$issues[] = sprintf( __( '%d users awaiting moderation approval', 'wpshadow' ), $pending_users );
		}
		
		// Check 3: Email verification
		$email_verification = get_option( 'tml_email_verification', false );
		if ( ! $email_verification ) {
			$issues[] = __( 'Email verification not required (spam risk)', 'wpshadow' );
		}
		
		// Check 4: Old pending registrations
		$old_pending = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->users} u
				 INNER JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
				 WHERE um.meta_key = %s AND um.meta_value = %s
				 AND u.user_registered < DATE_SUB(NOW(), INTERVAL 30 DAY)",
				'tml_user_status',
				'pending'
			)
		);
		
		if ( $old_pending > 10 ) {
			$issues[] = sprintf( __( '%d pending registrations older than 30 days (cleanup needed)', 'wpshadow' ), $old_pending );
		}
		
		// Check 5: Moderation notifications
		$notify_admin = get_option( 'tml_moderation_notify_admin', false );
		if ( ! $notify_admin && $pending_users > 0 ) {
			$issues[] = __( 'Admin not notified of pending registrations', 'wpshadow' );
		}
		
		// Check 6: Auto-approval rules
		$auto_approve = get_option( 'tml_auto_approve_domains', array() );
		if ( empty( $auto_approve ) && $pending_users > 100 ) {
			$issues[] = __( 'No auto-approval domains configured (manual queue overload)', 'wpshadow' );
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
				/* translators: %s: list of moderation issues */
				__( 'Theme My Login moderation has %d issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/theme-my-login-moderation',
		);
	}
}
