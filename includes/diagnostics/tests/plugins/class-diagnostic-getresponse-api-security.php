<?php
/**
 * Getresponse Api Security Diagnostic
 *
 * Getresponse Api Security configuration issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.733.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Getresponse Api Security Diagnostic Class
 *
 * @since 1.733.0000
 */
class Diagnostic_GetresponseApiSecurity extends Diagnostic_Base {

	protected static $slug = 'getresponse-api-security';
	protected static $title = 'Getresponse Api Security';
	protected static $description = 'Getresponse Api Security configuration issues';
	protected static $family = 'security';

	public static function check() {
		if ( ! get_option( 'getresponse_api_key', '' ) && ! get_option( 'getresponse_client_id', '' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: API key configured
		$api_key = get_option( 'getresponse_api_key', '' );
		if ( empty( $api_key ) ) {
			$issues[] = 'GetResponse API key not configured';
		}

		// Check 2: Client ID configured
		$client_id = get_option( 'getresponse_client_id', '' );
		if ( empty( $client_id ) ) {
			$issues[] = 'GetResponse client ID not configured';
		}

		// Check 3: OAuth enabled
		$oauth_enabled = get_option( 'getresponse_oauth_enabled', 0 );
		if ( ! $oauth_enabled ) {
			$issues[] = 'OAuth not enabled for GetResponse';
		}

		// Check 4: Key masking
		$mask_keys = get_option( 'getresponse_mask_api_keys', 0 );
		if ( ! $mask_keys ) {
			$issues[] = 'API keys not masked in admin';
		}

		// Check 5: API logging
		$api_logging = get_option( 'getresponse_api_logging', 0 );
		if ( $api_logging ) {
			$issues[] = 'API logging enabled (exposure risk)';
		}

		// Check 6: Key rotation
		$key_rotation = get_option( 'getresponse_key_rotation', 0 );
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
					'Found %d GetResponse API security issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/getresponse-api-security',
			);
		}

		return null;
	}
}
