<?php
/**
 * OpenTable Integration Security Diagnostic
 *
 * OpenTable API credentials exposed.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.602.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * OpenTable Integration Security Diagnostic Class
 *
 * @since 1.602.0000
 */
class Diagnostic_OpentableIntegrationSecurity extends Diagnostic_Base {

	protected static $slug = 'opentable-integration-security';
	protected static $title = 'OpenTable Integration Security';
	protected static $description = 'OpenTable API credentials exposed';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'OPENTABLE_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: API credentials secured
		$api_secure = get_option( 'opentable_api_credentials_secured', 0 );
		if ( ! $api_secure ) {
			$issues[] = 'API credentials not properly secured';
		}
		
		// Check 2: HTTPS enforced
		$https = get_option( 'opentable_https_enforced', 0 );
		if ( ! $https ) {
			$issues[] = 'HTTPS not enforced for OpenTable integration';
		}
		
		// Check 3: Data encryption
		$encrypt = get_option( 'opentable_data_encryption_enabled', 0 );
		if ( ! $encrypt ) {
			$issues[] = 'Data encryption not enabled';
		}
		
		// Check 4: Authentication token validation
		$token = get_option( 'opentable_token_validation_enabled', 0 );
		if ( ! $token ) {
			$issues[] = 'Authentication token validation not enabled';
		}
		
		// Check 5: Request signature verification
		$signature = get_option( 'opentable_request_signature_verification_enabled', 0 );
		if ( ! $signature ) {
			$issues[] = 'Request signature verification not enabled';
		}
		
		// Check 6: Rate limiting
		$rate_limit = get_option( 'opentable_api_rate_limiting_enabled', 0 );
		if ( ! $rate_limit ) {
			$issues[] = 'API rate limiting not enabled';
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
					'Found %d API security issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/opentable-integration-security',
			);
		}
		
		return null;
	}
}
