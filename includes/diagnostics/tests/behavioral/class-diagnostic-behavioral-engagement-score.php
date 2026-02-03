<?php
/**
 * Diagnostic: Member Engagement Score
 *
 * Tests whether the site quantifies member engagement to trigger appropriate
 * retention actions based on activity levels.
 *
 * Issue: https://github.com/thisismyurl/wpshadow/issues/4543
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
 * Member Engagement Score Diagnostic
 *
 * Checks for engagement scoring systems. Quantifying member activity enables
 * data-driven retention and prevents churn through timely interventions.
 *
 * @since 1.6034.1450
 */
class Diagnostic_Behavioral_Engagement_Score extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'calculates-member-engagement-score';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Member Engagement Score';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether site quantifies member engagement for retention actions';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'behavioral';

	/**
	 * Check for engagement scoring implementation.
	 *
	 * Looks for gamification, activity tracking, and analytics plugins
	 * that quantify user engagement.
	 *
	 * @since  1.6034.1450
	 * @return array|null Finding array if missing, null if present.
	 */
	public static function check() {
		// Check for gamification/points plugins.
		$gamification_plugins = array(
			'gamipress/gamipress.php'                        => 'GamiPress',
			'mycred/mycred.php'                              => 'myCred',
			'badgeos/badgeos.php'                            => 'BadgeOS',
		);

		foreach ( $gamification_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				return null; // Has engagement scoring.
			}
		}

		// Check for LMS engagement tracking.
		$lms_plugins = array(
			'learndash/learndash.php'                        => 'LearnDash',
			'lifterlms/lifterlms.php'                        => 'LifterLMS',
			'sensei-lms/sensei-lms.php'                      => 'Sensei LMS',
		);

		foreach ( $lms_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				return null; // LMS tracks engagement.
			}
		}

		// Check for community plugins with activity tracking.
		$community_plugins = array(
			'buddypress/bp-loader.php'                       => 'BuddyPress',
			'bbpress/bbpress.php'                            => 'bbPress',
		);

		foreach ( $community_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				return null; // Community plugins track activity.
			}
		}

		// Only applicable for membership/subscription sites.
		$is_membership_site = false;
		
		$membership_plugins = array(
			'memberpress/memberpress.php',
			'paid-memberships-pro/paid-memberships-pro.php',
			'restrict-content-pro/restrict-content-pro.php',
			'woocommerce-subscriptions/woocommerce-subscriptions.php',
		);

		foreach ( $membership_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$is_membership_site = true;
				break;
			}
		}

		// Check if user registration enabled.
		if ( get_option( 'users_can_register' ) ) {
			$is_membership_site = true;
		}

		if ( ! $is_membership_site ) {
			return null; // Not membership site.
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => __(
				'No member engagement scoring detected. Quantifying engagement (logins, content views, forum posts, course progress) enables data-driven retention. High-engagement members rarely churn. Low-engagement members need intervention. Implement engagement scoring to identify at-risk members before they cancel.',
				'wpshadow'
			),
			'severity'     => 'medium',
			'threat_level' => 43,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/engagement-scoring',
		);
	}
}
