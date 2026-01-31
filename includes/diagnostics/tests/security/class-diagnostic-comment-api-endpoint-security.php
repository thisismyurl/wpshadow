<?php
/**
 * Comment API Endpoint Security Diagnostic
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26031.1500
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diagnostic_Comment_API_Endpoint_Security extends Diagnostic_Base {
	protected static $slug = 'comment-api-endpoint-security';
	protected static $title = 'Comment API Endpoint Security';
	protected static $description = 'Verifies comment REST API endpoints are secure';
	protected static $family = 'security';

	public static function check() {
		// Check if REST API is enabled.
		$rest_enabled = get_option( 'rest_api_enabled', true );

		if ( ! $rest_enabled ) {
			return null; // REST API disabled, no security concerns.
		}

		$issues = array();

		// Check if comments endpoint allows unauthenticated access.
		$routes        = rest_get_server()->get_routes();
		$comments_open = false;

		if ( isset( $routes['/wp/v2/comments'] ) ) {
			$route = $routes['/wp/v2/comments'];
			foreach ( $route as $handler ) {
				if ( isset( $handler['permission_callback'] ) ) {
					if ( '__return_true' === $handler['permission_callback'] || is_null( $handler['permission_callback'] ) ) {
						$comments_open = true;
					}
				}
			}
		}

		// Check if comment creation requires authentication.
		$require_auth = (int) get_option( 'comment_registration', 0 );

		if ( ! $require_auth && $comments_open ) {
			$issues[] = array(
				'issue'       => 'open_api_endpoint',
				'description' => __( 'Comment REST API endpoint allows unauthenticated access - potential spam vector', 'wpshadow' ),
				'severity'    => 'high',
			);
		}

		// Check for rate limiting.
		$has_rate_limit = has_filter( 'rest_pre_dispatch' ) || class_exists( 'WP_REST_Rate_Limit' );

		if ( ! $has_rate_limit ) {
			$issues[] = array(
				'issue'       => 'no_rate_limiting',
				'description' => __( 'REST API has no rate limiting - vulnerable to abuse', 'wpshadow' ),
				'severity'    => 'medium',
			);
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				__( 'Found %d REST API security issues', 'wpshadow' ),
				count( $issues )
			),
			'severity'     => 'high',
			'threat_level' => 60,
			'auto_fixable' => false,
			'details'      => $issues,
			'kb_link'      => 'https://wpshadow.com/kb/comment-api-endpoint-security',
		);
	}
}
