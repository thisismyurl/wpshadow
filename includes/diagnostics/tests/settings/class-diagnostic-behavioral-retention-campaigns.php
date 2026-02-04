<?php
/**
 * Diagnostic: Retention Campaigns Active
 *
 * Tests whether the site runs proactive retention campaigns that reduce churn
 * through targeted re-engagement.
 *
 * Issue: https://github.com/thisismyurl/wpshadow/issues/4544
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Behavioral
 * @since      1.6034.1450
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Retention Campaigns Active Diagnostic
 *
 * Checks for email/automation campaigns targeting inactive members.
 * Proactive retention reduces churn by 15-25% vs reactive cancellation handling.
 *
 * @since 1.6034.1450
 */
class Diagnostic_Behavioral_Retention_Campaigns extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'runs-retention-campaigns';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Retention Campaigns Active';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether site runs proactive retention campaigns';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'behavioral';

	/**
	 * Check for retention campaign implementation.
	 *
	 * Looks for email automation targeting inactive/at-risk members.
	 *
	 * @since  1.6034.1450
	 * @return array|null Finding array if missing, null if present.
	 */
	public static function check() {
		// Check for email automation plugins.
		$automation_plugins = array(
			'uncanny-automator/uncanny-automator.php'        => 'Uncanny Automator',
			'mailpoet/mailpoet.php'                          => 'MailPoet',
			'newsletter/plugin.php'                          => 'Newsletter',
		);

		$has_automation = false;
		foreach ( $automation_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$has_automation = true;
				break;
			}
		}

		// Check for drip campaign plugins.
		$drip_plugins = array(
			'mailchimp-for-wp/mailchimp-for-wp.php',
			'convertkit/convertkit.php',
		);

		foreach ( $drip_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_automation = true;
				break;
			}
		}

		// Only applicable for membership/subscription sites.
		$is_subscription_site = false;
		
		if ( class_exists( 'WC_Subscriptions' ) ) {
			$is_subscription_site = true;
		}

		$membership_plugins = array(
			'memberpress/memberpress.php',
			'paid-memberships-pro/paid-memberships-pro.php',
			'restrict-content-pro/restrict-content-pro.php',
		);

		foreach ( $membership_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$is_subscription_site = true;
				break;
			}
		}

		if ( ! $is_subscription_site ) {
			return null; // Not subscription model.
		}

		if ( $has_automation ) {
			// Has automation tools, assume retention campaigns exist.
			return null;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => __(
				'No retention campaign automation detected. Subscription sites need proactive re-engagement for inactive members - "We miss you" emails, special offers for at-risk members, usage reminders. Retention campaigns reduce churn by 15-25% compared to only reacting when members cancel. Implement email automation targeting low-engagement segments.',
				'wpshadow'
			),
			'severity'     => 'medium',
			'threat_level' => 46,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/retention-campaigns',
		);
	}
}
