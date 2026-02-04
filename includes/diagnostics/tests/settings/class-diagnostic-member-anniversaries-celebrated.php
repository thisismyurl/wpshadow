<?php
/**
 * Member Anniversaries Celebrated Diagnostic
 *
 * Tests whether the site recognizes membership milestones to increase retention.
 *
 * @since   1.6034.0230
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Member Anniversaries Celebrated Diagnostic Class
 *
 * Celebrating membership anniversaries and milestones increases member loyalty,
 * engagement, and lifetime value through recognition and appreciation.
 *
 * @since 1.6034.0230
 */
class Diagnostic_Member_Anniversaries_Celebrated extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'member-anniversaries-celebrated';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Member Anniversaries Celebrated';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site recognizes membership milestones to increase retention';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'membership';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6034.0230
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Only relevant for membership sites.
		if ( ! self::is_membership_site() ) {
			return null;
		}

		$issues = array();
		$anniversary_score = 0;
		$max_score = 6;

		// Check for anniversary automation plugins.
		$automation_plugins = self::check_automation_plugins();
		if ( $automation_plugins ) {
			$anniversary_score++;
		} else {
			$issues[] = __( 'No automation system for anniversary recognition', 'wpshadow' );
		}

		// Check for anniversary email templates.
		$email_templates = self::check_email_templates();
		if ( $email_templates ) {
			$anniversary_score++;
		} else {
			$issues[] = __( 'No dedicated anniversary email templates', 'wpshadow' );
		}

		// Check for milestone rewards/benefits.
		$milestone_rewards = self::check_milestone_rewards();
		if ( $milestone_rewards ) {
			$anniversary_score++;
		} else {
			$issues[] = __( 'No special rewards or benefits for milestones', 'wpshadow' );
		}

		// Check for anniversary content.
		$anniversary_content = self::check_anniversary_content();
		if ( $anniversary_content ) {
			$anniversary_score++;
		} else {
			$issues[] = __( 'No content about celebrating member anniversaries', 'wpshadow' );
		}

		// Check for gamification/badges.
		$gamification = self::check_gamification();
		if ( $gamification ) {
			$anniversary_score++;
		} else {
			$issues[] = __( 'No badges or gamification for loyalty milestones', 'wpshadow' );
		}

		// Check for anniversary tracking.
		$tracking = self::check_anniversary_tracking();
		if ( $tracking ) {
			$anniversary_score++;
		} else {
			$issues[] = __( 'No system to track member join dates and anniversaries', 'wpshadow' );
		}

		// Determine severity based on anniversary implementation.
		$anniversary_percentage = ( $anniversary_score / $max_score ) * 100;

		if ( $anniversary_percentage < 30 ) {
			$severity = 'medium';
			$threat_level = 45;
		} elseif ( $anniversary_percentage < 60 ) {
			$severity = 'low';
			$threat_level = 30;
		} else {
			return null;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %d: Anniversary celebration percentage */
				__( 'Anniversary celebration at %d%%. ', 'wpshadow' ),
				(int) $anniversary_percentage
			) . implode( '. ', $issues ) . ' ' . __( 'Milestone recognition can increase retention by 10-15%', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/member-anniversaries-celebrated',
			);
		}

		return null;
	}

	/**
	 * Check if this is a membership site.
	 *
	 * @since  1.6034.0230
	 * @return bool True if membership features detected, false otherwise.
	 */
	private static function is_membership_site() {
		$membership_plugins = array(
			'paid-memberships-pro/paid-memberships-pro.php',
			'restrict-content-pro/restrict-content-pro.php',
			'memberpress/memberpress.php',
			'woocommerce-memberships/woocommerce-memberships.php',
		);

		foreach ( $membership_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check for automation plugins.
	 *
	 * @since  1.6034.0230
	 * @return bool True if automation exists, false otherwise.
	 */
	private static function check_automation_plugins() {
		$automation_plugins = array(
			'mailchimp-for-wp/mailchimp-for-wp.php',
			'mailpoet/mailpoet.php',
			'newsletter/newsletter.php',
			'automated-emails/automated-emails.php',
		);

		foreach ( $automation_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_anniversary_automation', false );
	}

	/**
	 * Check for email templates.
	 *
	 * @since  1.6034.0230
	 * @return bool True if templates exist, false otherwise.
	 */
	private static function check_email_templates() {
		$keywords = array( 'anniversary email', 'membership anniversary', 'celebrate your' );

		foreach ( $keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword,
					'post_type'      => 'any',
					'posts_per_page' => 1,
					'post_status'    => 'publish',
				)
			);

			if ( $query->have_posts() ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_anniversary_emails', false );
	}

	/**
	 * Check for milestone rewards.
	 *
	 * @since  1.6034.0230
	 * @return bool True if rewards exist, false otherwise.
	 */
	private static function check_milestone_rewards() {
		$reward_keywords = array( 'anniversary reward', 'milestone benefit', 'loyalty bonus', '1 year member' );

		foreach ( $reward_keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword,
					'post_type'      => array( 'post', 'page' ),
					'posts_per_page' => 1,
					'post_status'    => 'publish',
				)
			);

			if ( $query->have_posts() ) {
				return true;
			}
		}

		// Check for coupon/discount plugins.
		if ( class_exists( 'WooCommerce' ) ) {
			return true;
		}

		return apply_filters( 'wpshadow_has_milestone_rewards', false );
	}

	/**
	 * Check for anniversary content.
	 *
	 * @since  1.6034.0230
	 * @return bool True if anniversary content exists, false otherwise.
	 */
	private static function check_anniversary_content() {
		$keywords = array( 'member anniversary', 'celebrating members', 'thank you for being with us' );

		foreach ( $keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword,
					'post_type'      => array( 'post', 'page' ),
					'posts_per_page' => 1,
					'post_status'    => 'publish',
				)
			);

			if ( $query->have_posts() ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_anniversary_content', false );
	}

	/**
	 * Check for gamification.
	 *
	 * @since  1.6034.0230
	 * @return bool True if gamification exists, false otherwise.
	 */
	private static function check_gamification() {
		$gamification_plugins = array(
			'gamipress/gamipress.php',
			'mycred/mycred.php',
			'badgeos/badgeos.php',
		);

		foreach ( $gamification_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_gamification', false );
	}

	/**
	 * Check for anniversary tracking.
	 *
	 * @since  1.6034.0230
	 * @return bool True if tracking exists, false otherwise.
	 */
	private static function check_anniversary_tracking() {
		// WordPress tracks user registration by default.
		$users = get_users( array( 'number' => 1 ) );
		if ( ! empty( $users ) && isset( $users[0]->user_registered ) ) {
			return true;
		}

		return apply_filters( 'wpshadow_tracks_anniversaries', false );
	}
}
