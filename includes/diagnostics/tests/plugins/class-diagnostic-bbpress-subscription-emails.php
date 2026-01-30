<?php
/**
 * bbPress Subscription Emails Diagnostic
 *
 * bbPress subscription emails flooding.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.510.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * bbPress Subscription Emails Diagnostic Class
 *
 * @since 1.510.0000
 */
class Diagnostic_BbpressSubscriptionEmails extends Diagnostic_Base {

	protected static $slug = 'bbpress-subscription-emails';
	protected static $title = 'bbPress Subscription Emails';
	protected static $description = 'bbPress subscription emails flooding';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'bbPress' ) ) {
			return null;
		}
		
		// Check if bbPress is active
		if ( ! class_exists( 'bbPress' ) && ! function_exists( 'bbp_get_version' ) ) {
			return null;
		}

		$issues = array();
		$threat_level = 0;

		global $wpdb;

		// Check subscription count
		$subscriptions = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->usermeta} 
				 WHERE meta_key LIKE %s",
				'_bbp_subscriptions%'
			)
		);

		if ( $subscriptions > 1000 ) {
			// Check email throttling
			$throttle_enabled = get_option( '_bbp_enable_email_throttle', 0 );
			if ( ! $throttle_enabled ) {
				$issues[] = 'email_throttling_disabled';
				$threat_level += 30;
			}

			// Check batch processing
			$batch_size = get_option( '_bbp_subscription_email_batch_size', 0 );
			if ( $batch_size === 0 || $batch_size > 100 ) {
				$issues[] = 'improper_batch_size';
				$threat_level += 25;
			}
		}

		// Check digest mode
		$digest_mode = get_option( '_bbp_enable_subscription_digest', 0 );
		if ( ! $digest_mode && $subscriptions > 5000 ) {
			$issues[] = 'digest_mode_not_enabled';
			$threat_level += 20;
		}

		// Check email queue
		$queue_enabled = get_option( '_bbp_enable_email_queue', 0 );
		if ( ! $queue_enabled && $subscriptions > 2000 ) {
			$issues[] = 'email_queue_disabled';
			$threat_level += 20;
		}

		// Check unsubscribe functionality
		$unsubscribe_link = get_option( '_bbp_subscription_unsubscribe_link', 1 );
		if ( ! $unsubscribe_link ) {
			$issues[] = 'unsubscribe_link_disabled';
			$threat_level += 15;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of email issues */
				__( 'bbPress subscription emails have flooding problems: %s. This overloads mail servers and triggers spam filters.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/bbpress-subscription-emails',
			);
		}
		
		return null;
	}
}
