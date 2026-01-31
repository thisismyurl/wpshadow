<?php
/**
 * Eventbrite API Key Security Diagnostic
 *
 * Eventbrite API keys exposed.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.580.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Eventbrite API Key Security Diagnostic Class
 *
 * @since 1.580.0000
 */
class Diagnostic_EventbriteApiKeySecurity extends Diagnostic_Base {

	protected static $slug = 'eventbrite-api-key-security';
	protected static $title = 'Eventbrite API Key Security';
	protected static $description = 'Eventbrite API keys exposed';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'Eventbrite_API' ) && ! get_option( 'eventbrite_api_key', '' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: API key stored
		$api_key = get_option( 'eventbrite_api_key', '' );
		if ( empty( $api_key ) ) {
			$issues[] = 'Eventbrite API key not configured';
		}

		// Check 2: Private token stored
		$private_token = get_option( 'eventbrite_private_token', '' );
		if ( empty( $private_token ) ) {
			$issues[] = 'Eventbrite private token missing';
		}

		// Check 3: OAuth enabled
		$oauth_enabled = get_option( 'eventbrite_oauth_enabled', 0 );
		if ( ! $oauth_enabled ) {
			$issues[] = 'OAuth not enabled for Eventbrite';
		}

		// Check 4: Key masking in admin
		$mask_keys = get_option( 'eventbrite_mask_keys', 0 );
		if ( ! $mask_keys ) {
			$issues[] = 'API keys not masked in admin';
		}

		// Check 5: Logging of API requests
		$api_logging = get_option( 'eventbrite_api_logging', 0 );
		if ( $api_logging ) {
			$issues[] = 'API logging enabled (exposure risk)';
		}

		// Check 6: Key rotation setting
		$key_rotation = get_option( 'eventbrite_key_rotation', 0 );
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
					'Found %d Eventbrite API key security issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/eventbrite-api-key-security',
			);
		}

		return null;
	}
}
