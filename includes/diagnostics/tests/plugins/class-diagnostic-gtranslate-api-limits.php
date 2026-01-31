<?php
/**
 * Gtranslate Api Limits Diagnostic
 *
 * Gtranslate Api Limits misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1162.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gtranslate Api Limits Diagnostic Class
 *
 * @since 1.1162.0000
 */
class Diagnostic_GtranslateApiLimits extends Diagnostic_Base {

	protected static $slug = 'gtranslate-api-limits';
	protected static $title = 'Gtranslate Api Limits';
	protected static $description = 'Gtranslate Api Limits misconfigured';
	protected static $family = 'security';

	public static function check() {
		if ( ! get_option( 'gt_api_key', '' ) && ! get_option( 'gt_enabled', '' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: API key configured
		$api_key = get_option( 'gt_api_key', '' );
		if ( empty( $api_key ) ) {
			$issues[] = 'GTranslate API key not configured';
		}

		// Check 2: Daily API limit set
		$daily_limit = absint( get_option( 'gt_daily_api_limit', 0 ) );
		if ( $daily_limit <= 0 ) {
			$issues[] = 'Daily API limit not configured';
		}

		// Check 3: Rate limiting enabled
		$rate_limit = get_option( 'gt_rate_limiting_enabled', 0 );
		if ( ! $rate_limit ) {
			$issues[] = 'Rate limiting not enabled';
		}

		// Check 4: Cache enabled
		$cache = get_option( 'gt_cache_translations', 0 );
		if ( ! $cache ) {
			$issues[] = 'Translation caching not enabled';
		}

		// Check 5: Usage monitoring
		$monitor = get_option( 'gt_api_usage_monitoring', 0 );
		if ( ! $monitor ) {
			$issues[] = 'API usage monitoring not enabled';
		}

		// Check 6: Alert threshold configured
		$alert_threshold = absint( get_option( 'gt_alert_threshold_percent', 0 ) );
		if ( $alert_threshold <= 0 ) {
			$issues[] = 'Alert threshold not configured';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 50;
			$threat_multiplier = 6;
			$max_threat = 80;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d GTranslate API limit issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/gtranslate-api-limits',
			);
		}

		return null;
	}
}
