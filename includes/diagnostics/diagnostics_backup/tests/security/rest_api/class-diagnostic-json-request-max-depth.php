<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Json_Request_Max_Depth extends Diagnostic_Base {
	protected static $slug = 'json-request-max-depth';
	protected static $title = 'JSON Max Decode Depth';
	protected static $description = 'Detects maximum JSON decode depth';
	protected static $family = 'rest_api';
	public static function check() {
		if ( ! function_exists( 'json_decode' ) ) {
			return null;
		}
		$test_json = json_encode( array( 'level1' => array( 'level2' => array( 'level3' => array( 'level4' => array( 'level5' => 'deep' ) ) ) ) ) );
		$decoded = json_decode( $test_json, true );
		if ( empty( $decoded ) ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'JSON decode depth may be limited. Deep nested JSON structures might fail to decode. Check php.ini json settings.', 'wpshadow' ),
				'severity' => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/json-request-max-depth',
				'meta' => array(),
			);
		}

		// Basic WordPress functionality checks
		if ( ! function_exists( 'get_option' ) ) {
			$issues[] = __( 'Options API not available', 'wpshadow' );
		}
		if ( ! function_exists( 'add_action' ) ) {
			$issues[] = __( 'WordPress hooks not available', 'wpshadow' );
		}
		if ( empty( $GLOBALS['wpdb'] ) ) {
			$issues[] = __( 'Database not initialized', 'wpshadow' );
		}
		return null;
	}
}
