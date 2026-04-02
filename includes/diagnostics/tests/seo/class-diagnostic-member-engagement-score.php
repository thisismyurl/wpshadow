<?php
/**
 * Member Engagement Score Diagnostic
 *
 * Tests whether the site tracks member engagement levels systematically.
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
 * Member Engagement Score Diagnostic Class
 *
 * Engagement scoring allows proactive intervention before churn occurs.
 * Sites that track engagement can identify and assist at-risk members early.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Member_Engagement_Score extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'member-engagement-score';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Member Engagement Score';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site tracks member engagement levels systematically';

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
		$engagement_score = 0;
		$max_score = 6;

		// Check for activity tracking.
		$activity_tracking = self::check_activity_tracking();
		if ( $activity_tracking ) {
			$engagement_score++;
		} else {
			$issues[] = __( 'No system to track member activity and engagement', 'wpshadow' );
		}

		// Check for engagement metrics.
		$engagement_metrics = self::check_engagement_metrics();
		if ( $engagement_metrics ) {
			$engagement_score++;
		} else {
			$issues[] = __( 'No defined engagement metrics or KPIs', 'wpshadow' );
		}

		// Check for scoring system.
		$scoring_system = self::check_scoring_system();
		if ( $scoring_system ) {
			$engagement_score++;
		} else {
			$issues[] = __( 'No engagement scoring or health score system', 'wpshadow' );
		}

		// Check for segmentation by engagement.
		$engagement_segments = self::check_engagement_segments();
		if ( $engagement_segments ) {
			$engagement_score++;
		} else {
			$issues[] = __( 'No segmentation of members by engagement level', 'wpshadow' );
		}

		// Check for analytics dashboard.
		$analytics_dashboard = self::check_analytics_dashboard();
		if ( $analytics_dashboard ) {
			$engagement_score++;
		} else {
			$issues[] = __( 'No dashboard to view member engagement analytics', 'wpshadow' );
		}

		// Check for automated alerts.
		$automated_alerts = self::check_automated_alerts();
		if ( $automated_alerts ) {
			$engagement_score++;
		} else {
			$issues[] = __( 'No alerts for declining member engagement', 'wpshadow' );
		}

		// Determine severity based on engagement tracking implementation.
		$engagement_percentage = ( $engagement_score / $max_score ) * 100;

		if ( $engagement_percentage < 30 ) {
			$severity = 'medium';
			$threat_level = 50;
		} elseif ( $engagement_percentage < 60 ) {
			$severity = 'low';
			$threat_level = 35;
		} else {
			return null;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %d: Engagement tracking percentage */
				__( 'Member engagement tracking at %d%%. ', 'wpshadow' ),
				(int) $engagement_percentage
			) . implode( '. ', $issues ) . ' ' . __( 'Engagement scoring enables 15-20% better retention', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/member-engagement-score',
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
	 * Check for activity tracking.
	 *
	 * @since 1.6093.1200
	 * @return bool True if activity tracking exists, false otherwise.
	 */
	private static function check_activity_tracking() {
		// Check for activity tracking plugins.
		$tracking_plugins = array(
			'stream/stream.php',
			'simple-history/index.php',
			'aryo-activity-log/aryo-activity-log.php',
			'wp-user-activity/wp-user-activity.php',
		);

		foreach ( $tracking_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		// Check for analytics plugins.
		if ( is_plugin_active( 'google-site-kit/google-site-kit.php' ) ||
			 is_plugin_active( 'matomo/matomo.php' ) ) {
			return true;
		}

		return apply_filters( 'wpshadow_has_activity_tracking', false );
	}

	/**
	 * Check for engagement metrics.
	 *
	 * @since 1.6093.1200
	 * @return bool True if metrics exist, false otherwise.
	 */
	private static function check_engagement_metrics() {
		// Check for content about engagement metrics.
		$keywords = array( 'engagement metrics', 'member activity', 'participation rate', 'active users' );

		foreach ( $keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword,
					'post_type'      => array( 'post', 'page' ),
					'posts_per_page' => 1,
					'post_status'    => 'any',
				)
			);

			if ( $query->have_posts() ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_engagement_metrics', false );
	}

	/**
	 * Check for scoring system.
	 *
	 * @since 1.6093.1200
	 * @return bool True if scoring system exists, false otherwise.
	 */
	private static function check_scoring_system() {
		// Check for gamification plugins with points/scores.
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

		// Check for health score content.
		$query = new \WP_Query(
			array(
				's'              => 'health score engagement score member score',
				'post_type'      => 'any',
				'posts_per_page' => 1,
				'post_status'    => 'any',
			)
		);

		return $query->have_posts();
	}

	/**
	 * Check for engagement segments.
	 *
	 * @since 1.6093.1200
	 * @return bool True if segmentation exists, false otherwise.
	 */
	private static function check_engagement_segments() {
		// Check for email segmentation.
		if ( is_plugin_active( 'mailpoet/mailpoet.php' ) && class_exists( 'MailPoet\Models\Segment' ) ) {
			return true;
		}

		// Check for segment-related content.
		$keywords = array( 'active members', 'inactive members', 'at-risk members', 'engaged users' );

		foreach ( $keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword,
					'post_type'      => array( 'post', 'page' ),
					'posts_per_page' => 1,
					'post_status'    => 'any',
				)
			);

			if ( $query->have_posts() ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_engagement_segments', false );
	}

	/**
	 * Check for analytics dashboard.
	 *
	 * @since 1.6093.1200
	 * @return bool True if dashboard exists, false otherwise.
	 */
	private static function check_analytics_dashboard() {
		// Check for analytics plugins.
		$analytics_plugins = array(
			'google-site-kit/google-site-kit.php',
			'matomo/matomo.php',
			'jetpack/jetpack.php',
		);

		foreach ( $analytics_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		// WooCommerce has analytics.
		if ( class_exists( 'WooCommerce' ) ) {
			return true;
		}

		// PMPro has reports.
		if ( function_exists( 'pmpro_report_logins' ) ) {
			return true;
		}

		return apply_filters( 'wpshadow_has_analytics_dashboard', false );
	}

	/**
	 * Check for automated alerts.
	 *
	 * @since 1.6093.1200
	 * @return bool True if alerts exist, false otherwise.
	 */
	private static function check_automated_alerts() {
		// Check for notification/alert plugins.
		$notification_plugins = array(
			'mailpoet/mailpoet.php',
			'newsletter/newsletter.php',
			'automated-emails/automated-emails.php',
		);

		foreach ( $notification_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		// Check for alert-related content.
		$query = new \WP_Query(
			array(
				's'              => 'inactive alert declining engagement notification',
				'post_type'      => 'any',
				'posts_per_page' => 1,
				'post_status'    => 'any',
			)
		);

		return $query->have_posts();
	}
}
