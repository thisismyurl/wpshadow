<?php
/**
 * Wordfence Firewall Rules Diagnostic
 *
 * Wordfence Firewall Rules misconfiguration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.838.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wordfence Firewall Rules Diagnostic Class
 *
 * @since 1.838.0000
 */
class Diagnostic_WordfenceFirewallRules extends Diagnostic_Base {

	protected static $slug = 'wordfence-firewall-rules';
	protected static $title = 'Wordfence Firewall Rules';
	protected static $description = 'Wordfence Firewall Rules misconfiguration';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'WORDFENCE_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check if Wordfence firewall is actually enabled
		$firewall_enabled = get_option( 'wordfence_firewallEnabled', 0 );
		if ( ! $firewall_enabled ) {
			$issues[] = 'firewall_disabled';
		}
		
		// Check firewall protection level
		$protection_mode = get_option( 'wordfence_protectionLevel', '' );
		if ( 'extended' !== $protection_mode ) {
			// Extended Protection Mode offers the best security
			$issues[] = 'basic_protection_mode';
		}
		
		// Check if learning mode is still enabled (should be disabled after setup)
		$learning_mode = get_option( 'wordfence_learningMode', 0 );
		if ( $learning_mode ) {
			$issues[] = 'learning_mode_enabled';
		}
		
		// Check if firewall rules are outdated
		$rules_last_updated = get_option( 'wordfence_lastRulesUpdate', 0 );
		if ( $rules_last_updated ) {
			$days_since_update = ( time() - $rules_last_updated ) / DAY_IN_SECONDS;
			if ( $days_since_update > 7 ) {
				$issues[] = 'outdated_firewall_rules';
			}
		}
		
		// Check if Web Application Firewall is in the optimal location (.user.ini or .htaccess)
		$waf_status = get_option( 'wordfence_wafStatus', '' );
		if ( 'disabled' === $waf_status || 'learning' === $waf_status ) {
			$issues[] = 'waf_not_optimized';
		}
		
		// Check if brute force protection is enabled
		$brute_force_enabled = get_option( 'wordfence_loginSec_enableBruteForce', 0 );
		if ( ! $brute_force_enabled ) {
			$issues[] = 'brute_force_protection_disabled';
		}
		
		// Check if rate limiting is configured
		$rate_limit_enabled = get_option( 'wordfence_rateLimitingEnabled', 0 );
		if ( ! $rate_limit_enabled ) {
			$issues[] = 'rate_limiting_disabled';
		}
		
		// Check if country blocking is being used (can be a security measure)
		$country_blocking = get_option( 'wordfence_cbl_action', '' );
		// Not having country blocking is not necessarily an issue, but good to note
		
		// Check if firewall blocks immediate threats
		$block_immediate_threats = get_option( 'wordfence_blockImmediatelyOnBan', 0 );
		if ( ! $block_immediate_threats ) {
			$issues[] = 'delayed_threat_blocking';
		}
		
		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of firewall configuration issues */
				__( 'Wordfence Firewall has configuration issues: %s. These settings leave your site more vulnerable to attacks.', 'wpshadow' ),
				implode( ', ', array_map( 'ucwords', str_replace( '_', ' ', $issues ) ) )
			);
			
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => 75,
				'threat_level' => 75,
				'auto_fixable' => false, // Requires admin to configure Wordfence settings
				'kb_link'      => 'https://wpshadow.com/kb/wordfence-firewall-rules',
			);
		}
		
		return null;
	}
}
