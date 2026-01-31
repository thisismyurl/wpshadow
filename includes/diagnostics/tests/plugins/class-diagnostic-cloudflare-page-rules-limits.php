<?php
/**
 * Cloudflare Page Rules Limits Diagnostic
 *
 * Cloudflare Page Rules Limits needs attention.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.991.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cloudflare Page Rules Limits Diagnostic Class
 *
 * @since 1.991.0000
 */
class Diagnostic_CloudflarePageRulesLimits extends Diagnostic_Base {

	protected static $slug = 'cloudflare-page-rules-limits';
	protected static $title = 'Cloudflare Page Rules Limits';
	protected static $description = 'Cloudflare Page Rules Limits needs attention';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'CLOUDFLARE_VERSION' ) && ! get_option( 'cloudflare_api_key', '' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Page rules enabled
		$rules_enabled = get_option( 'cloudflare_page_rules_enabled', 0 );
		if ( ! $rules_enabled ) {
			$issues[] = 'Page rules not enabled';
		}

		// Check 2: API key configured
		$api_key = get_option( 'cloudflare_api_key', '' );
		if ( empty( $api_key ) ) {
			$issues[] = 'Cloudflare API key not configured';
		}

		// Check 3: Zone ID configured
		$zone_id = get_option( 'cloudflare_zone_id', '' );
		if ( empty( $zone_id ) ) {
			$issues[] = 'Cloudflare zone ID not configured';
		}

		// Check 4: Page rule limits monitored
		$rule_limit_monitor = get_option( 'cloudflare_rule_limit_monitor', 0 );
		if ( ! $rule_limit_monitor ) {
			$issues[] = 'Page rule limit monitoring not enabled';
		}

		// Check 5: Cache bypass rules
		$cache_bypass = get_option( 'cloudflare_cache_bypass_rules', '' );
		if ( empty( $cache_bypass ) ) {
			$issues[] = 'Cache bypass rules not configured';
		}

		// Check 6: Performance optimization rules
		$perf_rules = get_option( 'cloudflare_performance_rules', '' );
		if ( empty( $perf_rules ) ) {
			$issues[] = 'Performance optimization rules not configured';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 40;
			$threat_multiplier = 6;
			$max_threat = 70;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d Cloudflare page rule issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/cloudflare-page-rules-limits',
			);
		}

		return null;
	}
}
