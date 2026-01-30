<?php
/**
 * Wordpress Rest Api Exposed Endpoints Diagnostic
 *
 * Wordpress Rest Api Exposed Endpoints issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1249.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wordpress Rest Api Exposed Endpoints Diagnostic Class
 *
 * @since 1.1249.0000
 */
class Diagnostic_WordpressRestApiExposedEndpoints extends Diagnostic_Base {

	protected static $slug = 'wordpress-rest-api-exposed-endpoints';
	protected static $title = 'Wordpress Rest Api Exposed Endpoints';
	protected static $description = 'Wordpress Rest Api Exposed Endpoints issue detected';
	protected static $family = 'functionality';

	public static function check() {
		$issues = array();
		
		// Check 1: REST API enabled for unauthenticated users
		$rest_enabled = get_option( 'rest_api_enabled', 1 );
		if ( $rest_enabled ) {
			$issues[] = 'REST API enabled for unauthenticated access';
		}
		
		// Check 2: User endpoints exposed
		$user_endpoints = get_option( 'rest_api_expose_user_endpoints', 0 );
		if ( $user_endpoints ) {
			$issues[] = 'User enumeration via REST API enabled';
		}
		
		// Check 3: Post endpoint restrictions
		$post_restriction = get_option( 'rest_api_post_endpoint_restriction', 0 );
		if ( ! $post_restriction ) {
			$issues[] = 'Post endpoint access not restricted';
		}
		
		// Check 4: Search endpoint disabled
		$search_disabled = get_option( 'rest_api_search_endpoint_disabled', 0 );
		if ( ! $search_disabled ) {
			$issues[] = 'Search endpoint not disabled';
		}
		
		// Check 5: Authentication requirement
		$auth_required = get_option( 'rest_api_requires_authentication', 0 );
		if ( ! $auth_required ) {
			$issues[] = 'REST API authentication not required';
		}
		
		// Check 6: Rate limiting
		$rate_limit = get_option( 'rest_api_rate_limiting', 0 );
		if ( ! $rate_limit ) {
			$issues[] = 'REST API rate limiting not enabled';
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
					'Found %d REST API exposure issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wordpress-rest-api-exposed-endpoints',
			);
		}
		
		return null;
	}
}
