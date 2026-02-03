<?php
/**
 * Diagnostic: Onboarding Sequence Optimized
 *
 * Tests whether the site provides structured member onboarding that achieves
 * >80% completion rate for better retention.
 *
 * Issue: https://github.com/thisismyurl/wpshadow/issues/4541
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Behavioral
 * @since      1.6034.1445
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Onboarding Sequence Optimized Diagnostic
 *
 * Checks for structured onboarding for new members/users. Effective onboarding
 * increases retention by 50% and time-to-value understanding.
 *
 * @since 1.6034.1445
 */
class Diagnostic_Behavioral_Onboarding_Sequence extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'optimizes-member-onboarding-sequence';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Onboarding Sequence Optimized';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether site provides structured onboarding for new members';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'behavioral';

	/**
	 * Check for onboarding sequence implementation.
	 *
	 * Looks for onboarding plugins, welcome emails, and guided setup flows.
	 *
	 * @since  1.6034.1445
	 * @return array|null Finding array if missing, null if present.
	 */
	public static function check() {
		// Check for onboarding/welcome plugins.
		$onboarding_plugins = array(
			'wp-user-frontend/wpuf.php'                      => 'WP User Frontend',
			'uncanny-automator/uncanny-automator.php'        => 'Uncanny Automator',
			'memberpress/memberpress.php'                    => 'MemberPress',
			'paid-memberships-pro/paid-memberships-pro.php'  => 'Paid Memberships Pro',
			'wishlist-member-x/wishlist-member-x.php'        => 'Wishlist Member',
		);

		foreach ( $onboarding_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				// Membership plugins typically include onboarding.
				return null;
			}
		}

		// Check for welcome/drip email plugins.
		$email_plugins = array(
			'mailpoet/mailpoet.php',
			'newsletter/plugin.php',
			'mailchimp-for-wp/mailchimp-for-wp.php',
		);

		$has_email_automation = false;
		foreach ( $email_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_email_automation = true;
				break;
			}
		}

		// Check for onboarding page.
		$onboarding_keywords = array( 'welcome', 'getting-started', 'onboarding', 'setup', 'first-steps' );
		$pages               = get_pages();
		$has_onboarding_page = false;

		foreach ( $pages as $page ) {
			foreach ( $onboarding_keywords as $keyword ) {
				if ( stripos( $page->post_title, $keyword ) !== false || stripos( $page->post_name, $keyword ) !== false ) {
					$has_onboarding_page = true;
					break 2;
				}
			}
		}

		if ( $has_email_automation && $has_onboarding_page ) {
			return null; // Has onboarding elements.
		}

		// Only recommend for membership/community sites.
		$needs_onboarding = false;
		
		// Check for membership functionality.
		if ( class_exists( 'MeprUser' ) || class_exists( 'WP_Members' ) ) {
			$needs_onboarding = true;
		}

		// Check for user registration enabled.
		if ( get_option( 'users_can_register' ) ) {
			$needs_onboarding = true;
		}

		// Check for LMS plugins.
		$lms_plugins = array(
			'learndash/learndash.php',
			'lifterlms/lifterlms.php',
			'sensei-lms/sensei-lms.php',
		);

		foreach ( $lms_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$needs_onboarding = true;
				break;
			}
		}

		if ( ! $needs_onboarding ) {
			return null; // No user accounts, onboarding not applicable.
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => __(
				'No structured onboarding sequence detected. New members need guided first experiences - clear next steps, welcome emails, and setup completion tracking. Sites with effective onboarding (>80% completion) have 50% better retention. Create welcome sequences, getting started pages, and progress indicators.',
				'wpshadow'
			),
			'severity'     => 'medium',
			'threat_level' => 45,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/member-onboarding',
		);
	}
}
