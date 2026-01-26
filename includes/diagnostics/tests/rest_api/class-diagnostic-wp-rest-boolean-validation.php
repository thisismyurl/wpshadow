<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Wp_Rest_Boolean_Validation extends Diagnostic_Base {
	protected static $slug = 'wp-rest-boolean-validation';
	protected static $title = 'REST API Boolean Validation';
	protected static $description = 'Detects REST API boolean conversion issues';
	protected static $family = 'rest_api';
	public static function check() {
		if ( ! function_exists( 'rest_api_init' ) ) { return null; }
		global $wp_rest_server;
		if ( empty( $wp_rest_server ) ) { return null; }
		$request = new \WP_REST_Request( 'GET', '/wp/v2/posts' );
		$request->set_param( 'strict_check', true );
		$response = rest_do_request( $request );
		if ( is_wp_error( $response ) ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'REST API boolean parameter validation may have issues. Check REST API configuration and parameter validation.', 'wpshadow' ),
				'severity' => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/wp-rest-boolean-validation',
				'meta' => array(),
			);
		}
		return null;
	}
}
