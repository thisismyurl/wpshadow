<?php
/**
 * Email Send Time Optimization Diagnostic
 *
 * Tests whether the site uses data-driven send time optimization for each subscriber.
 *
 * @since   1.6034.0320
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Email Send Time Optimization Diagnostic Class
 *
 * Send time optimization increases open rates by 30% by reaching subscribers
 * when they're most likely to engage.
 *
 * @since 1.6034.0320
 */
class Diagnostic_Email_Send_Time_Optimization extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'email-send-time-optimization';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Email Send Time Optimization';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site uses data-driven send time optimization for each subscriber';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'email-marketing';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6034.0320
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$optimization_score = 0;
		$max_score = 5;

		// Check for advanced email platform.
		$advanced_platform = self::check_advanced_platform();
		if ( $advanced_platform ) {
			$optimization_score++;
		} else {
			$issues[] = __( 'No email platform with send time optimization', 'wpshadow' );
		}

		// Check for engagement tracking.
		$engagement_tracking = self::check_engagement_tracking();
		if ( $engagement_tracking ) {
			$optimization_score++;
		} else {
			$issues[] = __( 'Not tracking subscriber engagement patterns', 'wpshadow' );
		}

		// Check for timezone awareness.
		$timezone_aware = self::check_timezone_awareness();
		if ( $timezone_aware ) {
			$optimization_score++;
		} else {
			$issues[] = __( 'Not sending emails based on subscriber timezone', 'wpshadow' );
		}

		// Check for testing.
		$send_time_testing = self::check_send_time_testing();
		if ( $send_time_testing ) {
			$optimization_score++;
		} else {
			$issues[] = __( 'No testing of different send times', 'wpshadow' );
		}

		// Check for automation.
		$automated_optimization = self::check_automated_optimization();
		if ( $automated_optimization ) {
			$optimization_score++;
		} else {
			$issues[] = __( 'Send time optimization not automated', 'wpshadow' );
		}

		// Determine severity based on optimization.
		$optimization_percentage = ( $optimization_score / $max_score ) * 100;

		if ( $optimization_percentage < 40 ) {
			$severity = 'low';
			$threat_level = 20;
		} elseif ( $optimization_percentage < 70 ) {
			$severity = 'low';
			$threat_level = 10;
		} else {
			return null;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %d: Send time optimization percentage */
				__( 'Email send time optimization at %d%%. ', 'wpshadow' ),
				(int) $optimization_percentage
			) . implode( '. ', $issues ) . ' ' . __( 'Send time optimization increases open rates by 30%', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/email-send-time-optimization',
			);
		}

		return null;
	}

	/**
	 * Check advanced platform.
	 *
	 * @since  1.6034.0320
	 * @return bool True if advanced, false otherwise.
	 */
	private static function check_advanced_platform() {
		// Premium platforms have send time optimization.
		if ( is_plugin_active( 'mailpoet-premium/mailpoet-premium.php' ) ) {
			return true;
		}

		return apply_filters( 'wpshadow_has_send_time_optimization', false );
	}

	/**
	 * Check engagement tracking.
	 *
	 * @since  1.6034.0320
	 * @return bool True if tracking, false otherwise.
	 */
	private static function check_engagement_tracking() {
		// Most email platforms track engagement.
		if ( is_plugin_active( 'mailpoet/mailpoet.php' ) ||
			 is_plugin_active( 'newsletter/newsletter.php' ) ) {
			return true;
		}

		return apply_filters( 'wpshadow_tracks_email_engagement', false );
	}

	/**
	 * Check timezone awareness.
	 *
	 * @since  1.6034.0320
	 * @return bool True if timezone aware, false otherwise.
	 */
	private static function check_timezone_awareness() {
		// Advanced platforms support timezone sending.
		if ( is_plugin_active( 'mailpoet-premium/mailpoet-premium.php' ) ) {
			return true;
		}

		return apply_filters( 'wpshadow_timezone_aware_sending', false );
	}

	/**
	 * Check send time testing.
	 *
	 * @since  1.6034.0320
	 * @return bool True if testing, false otherwise.
	 */
	private static function check_send_time_testing() {
		// Check for documentation of testing.
		$query = new \WP_Query(
			array(
				's'              => 'send time test best time email',
				'post_type'      => 'any',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			)
		);

		return $query->have_posts();
	}

	/**
	 * Check automated optimization.
	 *
	 * @since  1.6034.0320
	 * @return bool True if automated, false otherwise.
	 */
	private static function check_automated_optimization() {
		// Premium platforms have AI-powered optimization.
		return apply_filters( 'wpshadow_automated_send_time', false );
	}
}
