<?php
/**
 * Visual Composer Api Security Diagnostic
 *
 * Visual Composer Api Security issues found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.833.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Visual Composer Api Security Diagnostic Class
 *
 * @since 1.833.0000
 */
class Diagnostic_VisualComposerApiSecurity extends Diagnostic_Base {

	protected static $slug = 'visual-composer-api-security';
	protected static $title = 'Visual Composer Api Security';
	protected static $description = 'Visual Composer Api Security issues found';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'VCV_VERSION' ) && ! class_exists( 'Vc_Manager' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: API key configured
		$api_key = get_option( 'vcv_api_key', '' );
		if ( empty( $api_key ) ) {
			$issues[] = 'Visual Composer API key not configured';
		}

		// Check 2: API access control
		$api_access = get_option( 'vcv_api_access_control', 0 );
		if ( ! $api_access ) {
			$issues[] = 'API access control not configured';
		}

		// Check 3: OAuth enabled
		$oauth = get_option( 'vcv_oauth_enabled', 0 );
		if ( ! $oauth ) {
			$issues[] = 'OAuth authentication not enabled';
		}

		// Check 4: API rate limiting
		$rate_limit = get_option( 'vcv_api_rate_limit', 0 );
		if ( ! $rate_limit ) {
			$issues[] = 'API rate limiting not enabled';
		}

		// Check 5: Request logging
		$request_logging = get_option( 'vcv_api_request_logging', 0 );
		if ( ! $request_logging ) {
			$issues[] = 'API request logging not enabled';
		}

		// Check 6: Key rotation
		$key_rotation = get_option( 'vcv_api_key_rotation', 0 );
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
					'Found %d Visual Composer API security issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/visual-composer-api-security',
			);
		}

		return null;
	}
}
