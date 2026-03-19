<?php
/**
 * Diagnostic: Churn Prediction Model
 *
 * Tests whether the site uses predictive analytics to identify at-risk
 * members early and prevent subscription cancellations.
 *
 * Issue: https://github.com/thisismyurl/wpshadow/issues/4542
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Behavioral
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Churn Prediction Model Diagnostic
 *
 * Checks for subscription analytics and churn prevention systems. Proactive
 * churn detection reduces cancellations by 20-30% through early intervention.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Behavioral_Churn_Prediction extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'uses-churn-prediction-model';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Churn Prediction Model';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether site uses predictive analytics to identify at-risk members';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'behavioral';

	/**
	 * Check for churn prediction implementation.
	 *
	 * Looks for subscription analytics, engagement tracking, and
	 * retention automation systems.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if missing, null if present.
	 */
	public static function check() {
		// Check for subscription analytics plugins.
		$analytics_plugins = array(
			'metorik-helper/metorik-helper.php'              => 'Metorik',
			'google-analytics-for-wordpress/googleanalytics.php' => 'MonsterInsights',
		);

		foreach ( $analytics_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				// Has analytics capability.
				return null;
			}
		}

		// Check for retention/engagement plugins.
		$retention_plugins = array(
			'gamipress/gamipress.php'                        => 'GamiPress',
			'uncanny-automator/uncanny-automator.php'        => 'Uncanny Automator',
		);

		foreach ( $retention_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				return null; // Has engagement tracking.
			}
		}

		// Check for email re-engagement campaigns.
		$email_plugins = array(
			'mailpoet/mailpoet.php',
			'newsletter/plugin.php',
		);

		$has_email_automation = false;
		foreach ( $email_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_email_automation = true;
				break;
			}
		}

		// Only applicable for subscription sites.
		$is_subscription_site = false;
		
		if ( class_exists( 'WC_Subscriptions' ) ) {
			$is_subscription_site = true;
		}

		// Check for membership plugins.
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
			return null; // Not a subscription model.
		}

		// Subscription site without churn prevention.
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => __(
				'No churn prediction or retention analytics detected. Subscription sites need to identify at-risk members before they cancel - tracking login frequency, feature usage, and engagement patterns. Proactive outreach to disengaged members reduces churn by 20-30%. Implement analytics to monitor member health scores.',
				'wpshadow'
			),
			'severity'     => 'medium',
			'threat_level' => 48,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/churn-prediction',
		);
	}
}
