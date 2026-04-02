<?php
/**
 * Onboarding Sequence Diagnostic
 *
 * Tests whether new members receive structured onboarding for better activation.
 *
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Onboarding Sequence Diagnostic Class
 *
 * Effective onboarding can improve long-term retention by 25-40%. First impressions
 * and early activation are critical for member success and satisfaction.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Onboarding_Sequence extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'onboarding-sequence';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Onboarding Sequence';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether new members receive structured onboarding for better activation';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'membership';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Only relevant for membership sites.
		if ( ! self::is_membership_site() ) {
			return null;
		}

		$issues = array();
		$onboarding_score = 0;
		$max_score = 7;

		// Check for welcome email.
		$welcome_email = self::check_welcome_email();
		if ( $welcome_email ) {
			$onboarding_score++;
		} else {
			$issues[] = __( 'No automated welcome email for new members', 'wpshadow' );
		}

		// Check for onboarding email sequence.
		$email_sequence = self::check_email_sequence();
		if ( $email_sequence ) {
			$onboarding_score++;
		} else {
			$issues[] = __( 'No multi-email onboarding sequence', 'wpshadow' );
		}

		// Check for getting started guide.
		$getting_started = self::check_getting_started();
		if ( $getting_started ) {
			$onboarding_score++;
		} else {
			$issues[] = __( 'No getting started guide or welcome page', 'wpshadow' );
		}

		// Check for interactive tutorials.
		$tutorials = self::check_tutorials();
		if ( $tutorials ) {
			$onboarding_score++;
		} else {
			$issues[] = __( 'No interactive tutorials or walkthroughs', 'wpshadow' );
		}

		// Check for quick wins/activation tasks.
		$quick_wins = self::check_quick_wins();
		if ( $quick_wins ) {
			$onboarding_score++;
		} else {
			$issues[] = __( 'No clear activation tasks or quick wins', 'wpshadow' );
		}

		// Check for welcome call/support.
		$welcome_support = self::check_welcome_support();
		if ( $welcome_support ) {
			$onboarding_score++;
		} else {
			$issues[] = __( 'No proactive welcome support or check-in', 'wpshadow' );
		}

		// Check for onboarding completion tracking.
		$completion_tracking = self::check_completion_tracking();
		if ( $completion_tracking ) {
			$onboarding_score++;
		} else {
			$issues[] = __( 'No tracking of onboarding completion rates', 'wpshadow' );
		}

		// Determine severity based on onboarding implementation.
		$onboarding_percentage = ( $onboarding_score / $max_score ) * 100;

		if ( $onboarding_percentage < 40 ) {
			$severity = 'high';
			$threat_level = 65;
		} elseif ( $onboarding_percentage < 70 ) {
			$severity = 'medium';
			$threat_level = 45;
		} else {
			return null;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %d: Onboarding completeness percentage */
				__( 'Member onboarding at %d%%. ', 'wpshadow' ),
				(int) $onboarding_percentage
			) . implode( '. ', $issues ) . ' ' . __( 'Effective onboarding improves retention by 25-40%', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/onboarding-sequence',
			);
		}

		return null;
	}

	/**
	 * Check if this is a membership site.
	 *
	 * @since 1.6093.1200
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
	 * Check for welcome email.
	 *
	 * @since 1.6093.1200
	 * @return bool True if welcome email exists, false otherwise.
	 */
	private static function check_welcome_email() {
		// WordPress has default new user emails.
		// Check if membership plugins customize it.
		if ( is_plugin_active( 'paid-memberships-pro/paid-memberships-pro.php' ) ||
			 is_plugin_active( 'memberpress/memberpress.php' ) ) {
			return true;
		}

		// Check for welcome email content.
		$keywords = array( 'welcome email', 'new member', 'thank you for joining' );

		foreach ( $keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword,
					'post_type'      => array( 'post', 'page', 'email' ),
					'posts_per_page' => 1,
					'post_status'    => 'any',
				)
			);

			if ( $query->have_posts() ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_welcome_email', false );
	}

	/**
	 * Check for email sequence.
	 *
	 * @since 1.6093.1200
	 * @return bool True if sequence exists, false otherwise.
	 */
	private static function check_email_sequence() {
		// Check for email automation plugins.
		$automation_plugins = array(
			'mailpoet/mailpoet.php',
			'newsletter/newsletter.php',
			'automated-emails/automated-emails.php',
			'fluentcrm/fluentcrm.php',
		);

		foreach ( $automation_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		// Check for sequence-related content.
		$query = new \WP_Query(
			array(
				's'              => 'onboarding sequence email series drip campaign',
				'post_type'      => array( 'post', 'page' ),
				'posts_per_page' => 1,
				'post_status'    => 'any',
			)
		);

		return $query->have_posts();
	}

	/**
	 * Check for getting started guide.
	 *
	 * @since 1.6093.1200
	 * @return bool True if guide exists, false otherwise.
	 */
	private static function check_getting_started() {
		$keywords = array( 'getting started', 'quick start', 'welcome guide', 'how to begin' );

		foreach ( $keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword,
					'post_type'      => 'page',
					'posts_per_page' => 1,
					'post_status'    => 'publish',
				)
			);

			if ( $query->have_posts() ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_getting_started', false );
	}

	/**
	 * Check for tutorials.
	 *
	 * @since 1.6093.1200
	 * @return bool True if tutorials exist, false otherwise.
	 */
	private static function check_tutorials() {
		// Check for tutorial/course plugins.
		$tutorial_plugins = array(
			'learndash/learndash.php',
			'sensei-lms/sensei-lms.php',
			'lifterlms/lifterlms.php',
			'wp-courseware/wp-courseware.php',
		);

		foreach ( $tutorial_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		// Check for tutorial content.
		$keywords = array( 'tutorial', 'walkthrough', 'step by step', 'how to use' );

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

		return apply_filters( 'wpshadow_has_tutorials', false );
	}

	/**
	 * Check for quick wins.
	 *
	 * @since 1.6093.1200
	 * @return bool True if quick wins exist, false otherwise.
	 */
	private static function check_quick_wins() {
		$keywords = array( 'first steps', 'quick win', 'activation checklist', 'complete your profile' );

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

		// Check for gamification with achievements.
		if ( is_plugin_active( 'gamipress/gamipress.php' ) ||
			 is_plugin_active( 'badgeos/badgeos.php' ) ) {
			return true;
		}

		return apply_filters( 'wpshadow_has_quick_wins', false );
	}

	/**
	 * Check for welcome support.
	 *
	 * @since 1.6093.1200
	 * @return bool True if support exists, false otherwise.
	 */
	private static function check_welcome_support() {
		// Check for live chat plugins.
		$chat_plugins = array(
			'tidio-live-chat/tidio-live-chat.php',
			'tawk-to-live-chat/tawk-to.php',
			'wp-live-chat-support/wp-live-chat-support.php',
		);

		foreach ( $chat_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		// Check for welcome/support messaging.
		$query = new \WP_Query(
			array(
				's'              => 'welcome call onboarding support personal assistance',
				'post_type'      => array( 'post', 'page' ),
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			)
		);

		return $query->have_posts();
	}

	/**
	 * Check for completion tracking.
	 *
	 * @since 1.6093.1200
	 * @return bool True if tracking exists, false otherwise.
	 */
	private static function check_completion_tracking() {
		// Check for analytics.
		if ( is_plugin_active( 'google-site-kit/google-site-kit.php' ) ||
			 is_plugin_active( 'matomo/matomo.php' ) ) {
			return true;
		}

		// Check for progress tracking plugins.
		if ( is_plugin_active( 'gamipress/gamipress.php' ) ) {
			return true;
		}

		// Check for onboarding tracking content.
		$query = new \WP_Query(
			array(
				's'              => 'onboarding completion activation rate',
				'post_type'      => 'any',
				'posts_per_page' => 1,
				'post_status'    => 'any',
			)
		);

		return $query->have_posts();
	}
}
