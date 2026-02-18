<?php
/**
 * Diagnostic: Member Anniversaries Celebrated
 *
 * Tests whether the site recognizes membership milestones (1 year, etc.) to
 * increase emotional connection and retention.
 *
 * Issue: https://github.com/thisismyurl/wpshadow/issues/4548
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Behavioral
 * @since      1.6034.1455
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Member Anniversaries Celebrated Diagnostic
 *
 * Checks for milestone recognition automation. Celebrating member anniversaries
 * increases retention by 18% through emotional reinforcement.
 *
 * @since 1.6034.1455
 */
class Diagnostic_Behavioral_Member_Anniversaries extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'celebrates-member-anniversaries';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Member Anniversaries Celebrated';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether site recognizes membership milestones';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'behavioral';

	/**
	 * Check for anniversary celebration implementation.
	 *
	 * Looks for automation that triggers on membership milestones.
	 *
	 * @since  1.6034.1455
	 * @return array|null Finding array if missing, null if present.
	 */
	public static function check() {
		// Check for automation plugins.
		$automation_plugins = array(
			'uncanny-automator/uncanny-automator.php'        => 'Uncanny Automator',
			'automatorwp/automatorwp.php'                    => 'AutomatorWP',
		);

		foreach ( $automation_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				// Has automation capability for anniversaries.
				return null;
			}
		}

		// Check for gamification/achievement plugins.
		$achievement_plugins = array(
			'gamipress/gamipress.php'                        => 'GamiPress',
			'badgeos/badgeos.php'                            => 'BadgeOS',
		);

		foreach ( $achievement_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				// Can track milestones.
				return null;
			}
		}

		// Check for email automation.
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

		// Only applicable for membership sites.
		$is_membership_site = false;
		
		$membership_plugins = array(
			'memberpress/memberpress.php',
			'paid-memberships-pro/paid-memberships-pro.php',
			'restrict-content-pro/restrict-content-pro.php',
		);

		foreach ( $membership_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$is_membership_site = true;
				break;
			}
		}

		if ( class_exists( 'WC_Subscriptions' ) ) {
			$is_membership_site = true;
		}

		if ( ! $is_membership_site ) {
			return null; // Not membership site.
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => __(
				'No membership anniversary celebration detected. Recognizing milestones (1 year, 2 years) increases retention by 18% - it reinforces member identity and emotional connection. Send congratulatory emails, offer anniversary badges/rewards, or provide special content. Use automation to trigger celebrations on registration anniversary dates.',
				'wpshadow'
			),
			'severity'     => 'low',
			'threat_level' => 35,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/member-anniversaries',
		);
	}
}
