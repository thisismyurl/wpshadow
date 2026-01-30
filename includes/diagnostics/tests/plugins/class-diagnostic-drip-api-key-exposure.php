<?php
/**
 * Drip Api Key Exposure Diagnostic
 *
 * Drip Api Key Exposure configuration issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.736.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Drip Api Key Exposure Diagnostic Class
 *
 * @since 1.736.0000
 */
class Diagnostic_DripApiKeyExposure extends Diagnostic_Base {

	protected static $slug = 'drip-api-key-exposure';
	protected static $title = 'Drip Api Key Exposure';
	protected static $description = 'Drip Api Key Exposure configuration issues';
	protected static $family = 'security';

	public static function check() {
		if ( ! get_option( 'drip_api_token', '' ) && ! get_option( 'drip_account_id', '' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: API token set
		$api_token = get_option( 'drip_api_token', '' );
		if ( empty( $api_token ) ) {
			$issues[] = 'Drip API token not configured';
		}
		
		// Check 2: Account ID set
		$account_id = get_option( 'drip_account_id', '' );
		if ( empty( $account_id ) ) {
			$issues[] = 'Drip account ID missing';
		}
		
		// Check 3: Key masking
		$mask_keys = get_option( 'drip_mask_api_keys', 0 );
		if ( ! $mask_keys ) {
			$issues[] = 'API keys not masked in admin';
		}
		
		// Check 4: Logging enabled
		$api_logging = get_option( 'drip_api_logging', 0 );
		if ( $api_logging ) {
			$issues[] = 'API logging enabled (exposure risk)';
		}
		
		// Check 5: OAuth usage
		$oauth_enabled = get_option( 'drip_oauth_enabled', 0 );
		if ( ! $oauth_enabled ) {
			$issues[] = 'OAuth not enabled for Drip';
		}
		
		// Check 6: Key rotation
		$key_rotation = get_option( 'drip_key_rotation', 0 );
		if ( ! $key_rotation ) {
			$issues[] = 'API key rotation not configured';
		}
		
		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 55;
			$threat_multiplier = 6;
			$max_threat = 85;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );
			
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d Drip API key exposure issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/drip-api-key-exposure',
			);
		}
		
		return null;
	}
}
