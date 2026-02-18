<?php
/**
 * Email Subject Line Testing Diagnostic
 *
 * Tests whether the site regularly A/B tests email subject lines to optimize open rates.
 *
 * @since   1.6034.0315
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Email Subject Line Testing Diagnostic Class
 *
 * A/B testing subject lines improves open rates by 49% on average.
 * Without testing, you're guessing what resonates with subscribers.
 *
 * @since 1.6034.0315
 */
class Diagnostic_Email_Subject_Line_Testing extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'email-subject-line-testing';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Email Subject Line Testing';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site regularly A/B tests email subject lines to optimize open rates';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'email-marketing';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6034.0315
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$testing_score = 0;
		$max_score = 5;

		// Check for A/B testing capability.
		$ab_testing = self::check_ab_testing_capability();
		if ( $ab_testing ) {
			$testing_score++;
		} else {
			$issues[] = __( 'No A/B testing capability for subject lines', 'wpshadow' );
		}

		// Check for analytics tracking.
		$analytics = self::check_analytics_tracking();
		if ( $analytics ) {
			$testing_score++;
		} else {
			$issues[] = __( 'No email analytics to measure open rates', 'wpshadow' );
		}

		// Check for testing frequency.
		$testing_frequency = self::check_testing_frequency();
		if ( $testing_frequency ) {
			$testing_score++;
		} else {
			$issues[] = __( 'No evidence of regular subject line testing', 'wpshadow' );
		}

		// Check for best practices.
		$best_practices = self::check_best_practices();
		if ( $best_practices ) {
			$testing_score++;
		} else {
			$issues[] = __( 'Subject line best practices not documented', 'wpshadow' );
		}

		// Check for emoji/personalization testing.
		$advanced_testing = self::check_advanced_testing();
		if ( $advanced_testing ) {
			$testing_score++;
		} else {
			$issues[] = __( 'Not testing advanced tactics (emojis, personalization)', 'wpshadow' );
		}

		// Determine severity based on testing implementation.
		$testing_percentage = ( $testing_score / $max_score ) * 100;

		if ( $testing_percentage < 40 ) {
			$severity = 'low';
			$threat_level = 25;
		} elseif ( $testing_percentage < 70 ) {
			$severity = 'low';
			$threat_level = 15;
		} else {
			return null;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %d: Subject line testing percentage */
				__( 'Email subject line testing at %d%%. ', 'wpshadow' ),
				(int) $testing_percentage
			) . implode( '. ', $issues ) . ' ' . __( 'A/B testing improves open rates by 49%', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/email-subject-line-testing',
			);
		}

		return null;
	}

	/**
	 * Check A/B testing capability.
	 *
	 * @since  1.6034.0315
	 * @return bool True if capable, false otherwise.
	 */
	private static function check_ab_testing_capability() {
		// MailPoet Pro has A/B testing.
		if ( is_plugin_active( 'mailpoet-premium/mailpoet-premium.php' ) ) {
			return true;
		}

		// Newsletter plugin supports testing.
		if ( is_plugin_active( 'newsletter/newsletter.php' ) ) {
			return true;
		}

		return apply_filters( 'wpshadow_has_ab_testing', false );
	}

	/**
	 * Check analytics tracking.
	 *
	 * @since  1.6034.0315
	 * @return bool True if tracking exists, false otherwise.
	 */
	private static function check_analytics_tracking() {
		// Most email platforms track opens.
		if ( is_plugin_active( 'mailpoet/mailpoet.php' ) ||
			 is_plugin_active( 'newsletter/newsletter.php' ) ) {
			return true;
		}

		return apply_filters( 'wpshadow_tracks_email_opens', false );
	}

	/**
	 * Check testing frequency.
	 *
	 * @since  1.6034.0315
	 * @return bool True if regular testing, false otherwise.
	 */
	private static function check_testing_frequency() {
		// Difficult to detect automatically, check for documentation.
		$query = new \WP_Query(
			array(
				's'              => 'subject line test a/b testing',
				'post_type'      => 'any',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			)
		);

		return $query->have_posts();
	}

	/**
	 * Check best practices.
	 *
	 * @since  1.6034.0315
	 * @return bool True if documented, false otherwise.
	 */
	private static function check_best_practices() {
		// Check for email marketing documentation.
		$query = new \WP_Query(
			array(
				's'              => 'email best practices subject line',
				'post_type'      => 'any',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			)
		);

		return $query->have_posts();
	}

	/**
	 * Check advanced testing.
	 *
	 * @since  1.6034.0315
	 * @return bool True if advanced tactics used, false otherwise.
	 */
	private static function check_advanced_testing() {
		// Premium platforms support personalization.
		if ( is_plugin_active( 'mailpoet-premium/mailpoet-premium.php' ) ) {
			return true;
		}

		return apply_filters( 'wpshadow_uses_advanced_subject_testing', false );
	}
}
