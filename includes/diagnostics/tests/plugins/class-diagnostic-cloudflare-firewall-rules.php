<?php
/**
 * Cloudflare Firewall Rules Diagnostic
 *
 * Cloudflare Firewall Rules needs attention.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.992.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cloudflare Firewall Rules Diagnostic Class
 *
 * @since 1.992.0000
 */
class Diagnostic_CloudflareFirewallRules extends Diagnostic_Base {

	protected static $slug = 'cloudflare-firewall-rules';
	protected static $title = 'Cloudflare Firewall Rules';
	protected static $description = 'Cloudflare Firewall Rules needs attention';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'CLOUDFLARE_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Verify API credentials are configured
		$api_key = get_option( 'cloudflare_api_key', '' );
		$email = get_option( 'cloudflare_email', '' );
		if ( empty( $api_key ) || empty( $email ) ) {
			$issues[] = 'Cloudflare API credentials not configured';
		}

		// Check 2: Verify zone ID is configured
		$zone_id = get_option( 'cloudflare_zone_id', '' );
		if ( empty( $zone_id ) ) {
			$issues[] = 'Cloudflare zone ID not configured';
		}

		// Check 3: Check for firewall rules enabled
		$firewall_rules = get_option( 'cloudflare_firewall_rules', array() );
		if ( empty( $firewall_rules ) ) {
			$issues[] = 'No firewall rules configured';
		}

		// Check 4: Verify WAF is enabled
		$waf_enabled = get_option( 'cloudflare_waf_enabled', 0 );
		if ( ! $waf_enabled ) {
			$issues[] = 'Web Application Firewall not enabled';
		}

		// Check 5: Check for rate limiting rules
		$rate_limiting = get_option( 'cloudflare_rate_limiting', 0 );
		if ( ! $rate_limiting ) {
			$issues[] = 'Rate limiting rules not enabled';
		}

		// Check 6: Verify bot fight mode
		$bot_fight = get_option( 'cloudflare_bot_fight_mode', 0 );
		if ( ! $bot_fight ) {
			$issues[] = 'Bot fight mode not enabled';
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
					'Found %d Cloudflare firewall rules issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/cloudflare-firewall-rules',
			);
		}

		return null;
	}
}
