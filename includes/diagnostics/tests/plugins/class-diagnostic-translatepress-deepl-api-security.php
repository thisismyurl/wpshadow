<?php
/**
 * Translatepress Deepl Api Security Diagnostic
 *
 * Translatepress Deepl Api Security misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1153.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Translatepress Deepl Api Security Diagnostic Class
 *
 * @since 1.1153.0000
 */
class Diagnostic_TranslatepressDeeplApiSecurity extends Diagnostic_Base {

	protected static $slug = 'translatepress-deepl-api-security';
	protected static $title = 'Translatepress Deepl Api Security';
	protected static $description = 'Translatepress Deepl Api Security misconfigured';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'TRP_PLUGIN_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: API key configured
		$api_key = get_option( 'trp_deepl_api_key', '' );
		if ( empty( $api_key ) ) {
			$issues[] = 'DeepL API key not configured';
		}

		// Check 2: API endpoint secured
		$https = get_option( 'trp_deepl_api_https_enabled', 0 );
		if ( ! $https ) {
			$issues[] = 'HTTPS not enforced for API calls';
		}

		// Check 3: Authentication headers
		$auth_header = get_option( 'trp_deepl_auth_header_enabled', 0 );
		if ( ! $auth_header ) {
			$issues[] = 'Authentication header not properly configured';
		}

		// Check 4: Request validation
		$req_val = get_option( 'trp_deepl_request_validation_enabled', 0 );
		if ( ! $req_val ) {
			$issues[] = 'API request validation not enabled';
		}

		// Check 5: Rate limiting
		$rate_limit = get_option( 'trp_deepl_rate_limiting_enabled', 0 );
		if ( ! $rate_limit ) {
			$issues[] = 'API rate limiting not enabled';
		}

		// Check 6: Error logging
		$logging = get_option( 'trp_deepl_error_logging_enabled', 0 );
		if ( ! $logging ) {
			$issues[] = 'API error logging not enabled';
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
					'Found %d API security issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/translatepress-deepl-api-security',
			);
		}

		return null;
	}
}
