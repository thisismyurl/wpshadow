<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Rest_Api_Rate_Limit extends Diagnostic_Base {
	protected static $slug = 'rest-api-rate-limit';
	protected static $title = 'REST API Rate Limit';
	protected static $description = 'Detects rate limit headers';
	protected static $family = 'rest_api';
	public static function check() {
		if ( ! function_exists( 'wp_remote_get' ) ) { return null; }
		$rest_url = rest_url( 'wp/v2/posts' );
		$response = wp_remote_get( $rest_url, array( 'blocking' => true, 'timeout' => 5 ) );
		if ( is_wp_error( $response ) ) { return null; }
		$rate_limit = wp_remote_retrieve_header( $response, 'x-ratelimit-limit' );
		if ( empty( $rate_limit ) ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'No rate limit headers detected on REST API. Consider implementing rate limiting to protect against abuse.', 'wpshadow' ),
				'severity' => 'medium',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/rest-api-rate-limit',
				'meta' => array(),
			);
		}
		return null;
	}
}
