<?php
/**
 * Rank Math 404 Monitoring Diagnostic
 *
 * Rank Math 404 Monitoring configuration issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.698.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Rank Math 404 Monitoring Diagnostic Class
 *
 * @since 1.698.0000
 */
class Diagnostic_RankMath404Monitoring extends Diagnostic_Base {

	protected static $slug = 'rank-math-404-monitoring';
	protected static $title = 'Rank Math 404 Monitoring';
	protected static $description = 'Rank Math 404 Monitoring configuration issues';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'RANK_MATH_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: 404 monitoring enabled
		$monitoring = get_option( 'rank_math_404_monitoring_enabled', 0 );
		if ( ! $monitoring ) {
			$issues[] = '404 monitoring not enabled';
		}

		// Check 2: Redirect suggestions
		$redirect = get_option( 'rank_math_404_redirect_suggestions_enabled', 0 );
		if ( ! $redirect ) {
			$issues[] = 'Redirect suggestions not enabled';
		}

		// Check 3: Log retention
		$retention = absint( get_option( 'rank_math_404_log_retention_days', 0 ) );
		if ( $retention <= 0 ) {
			$issues[] = '404 log retention not configured';
		}

		// Check 4: Email notifications
		$email = get_option( 'rank_math_404_email_notifications_enabled', 0 );
		if ( ! $email ) {
			$issues[] = 'Email notifications not enabled';
		}

		// Check 5: Redirect analytics
		$analytics = get_option( 'rank_math_404_redirect_analytics_enabled', 0 );
		if ( ! $analytics ) {
			$issues[] = 'Redirect analytics not enabled';
		}

		// Check 6: Custom 404 page
		$custom_404 = get_option( 'rank_math_404_custom_page_set', 0 );
		if ( ! $custom_404 ) {
			$issues[] = 'Custom 404 page not configured';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 35;
			$threat_multiplier = 6;
			$max_threat = 65;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d 404 monitoring issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/rank-math-404-monitoring',
			);
		}

		return null;
	}
}
