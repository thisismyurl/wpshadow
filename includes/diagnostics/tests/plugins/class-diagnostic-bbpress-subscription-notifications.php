<?php
/**
 * bbPress Subscription Notifications Diagnostic
 *
 * bbPress email subscriptions misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.243.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * bbPress Subscription Notifications Diagnostic Class
 *
 * @since 1.243.0000
 */
class Diagnostic_BbpressSubscriptionNotifications extends Diagnostic_Base {

	protected static $slug = 'bbpress-subscription-notifications';
	protected static $title = 'bbPress Subscription Notifications';
	protected static $description = 'bbPress email subscriptions misconfigured';
	protected static $family = 'functionality';

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

		// Check notification settings
		$allow_topic_subscribe = get_option( '_bbp_allow_topic_subscriptions', 1 );
		$allow_forum_subscribe = get_option( '_bbp_allow_forum_subscriptions', 1 );
		
		if ( ! $allow_topic_subscribe && ! $allow_forum_subscribe ) {
			return null; // Subscriptions disabled entirely
		}

		// Check email FROM configuration
		$from_email = get_option( '_bbp_subscription_from_email', '' );
		if ( empty( $from_email ) ) {
			$issues[] = 'from_email_not_configured';
			$threat_level += 20;
		}

		// Check notification frequency
		$instant_notify = get_option( '_bbp_enable_instant_notifications', 1 );
		$digest_available = get_option( '_bbp_enable_digest_option', 0 );
		if ( $instant_notify && ! $digest_available ) {
			$issues[] = 'no_digest_option_available';
			$threat_level += 15;
		}

		// Check notification templates
		$custom_template = get_option( '_bbp_subscription_email_template', '' );
		if ( empty( $custom_template ) ) {
			$issues[] = 'using_default_email_template';
			$threat_level += 10;
		}

		// Check double opt-in
		$double_optin = get_option( '_bbp_subscription_double_optin', 0 );
		if ( ! $double_optin ) {
			$issues[] = 'double_optin_disabled';
			$threat_level += 15;
		}

		// Check unsubscribe process
		$one_click_unsub = get_option( '_bbp_one_click_unsubscribe', 0 );
		if ( ! $one_click_unsub ) {
			$issues[] = 'one_click_unsubscribe_disabled';
			$threat_level += 10;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of configuration issues */
				__( 'bbPress email subscriptions have configuration problems: %s. This reduces notification effectiveness and may violate anti-spam laws.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/bbpress-subscription-notifications',
			);
		}
		
		return null;
	}
}
