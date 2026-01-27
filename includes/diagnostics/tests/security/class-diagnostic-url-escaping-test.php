<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Url_Escaping_Test extends Diagnostic_Base {
	protected static $slug = 'url-escaping-test';
	protected static $title = 'URL Escaping Test';
	protected static $description = 'Verifies esc_url sanitization works';
	protected static $family = 'security';
	public static function check() {
		if ( ! function_exists( 'esc_url' ) ) { return null; }
		$test_input = 'javascript:alert("xss")';
		$escaped = esc_url( $test_input );
		if ( strpos( $escaped, 'javascript:' ) === 0 ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'URL escaping may not be blocking dangerous protocols. This is a security concern. Verify WordPress esc_url is working correctly.', 'wpshadow' ),
				'severity' => 'critical',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/url-escaping-test',
				'meta' => array(),
			);
		}
		return null;
	}
}
