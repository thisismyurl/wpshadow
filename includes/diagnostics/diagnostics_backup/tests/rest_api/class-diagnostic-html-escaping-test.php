<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Html_Escaping_Test extends Diagnostic_Base {
	protected static $slug = 'html-escaping-test';
	protected static $title = 'HTML Escaping Validation';
	protected static $description = 'Validates esc_html outputs safe HTML';
	protected static $family = 'security';
	public static function check() {
		if ( ! function_exists( 'esc_html' ) ) { return null; }
		$test_input = '<script>alert("xss")</script>';
		$escaped = esc_html( $test_input );
		if ( strpos( $escaped, '<script>' ) !== false ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'HTML escaping function test failed. This could indicate a security issue. Verify WordPress escaping functions are working correctly.', 'wpshadow' ),
				'severity' => 'critical',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/html-escaping-test',
				'meta' => array(),
			);
		}

		// Security validation checks
		if ( is_ssl() === false ) {
			$issues[] = __( 'HTTPS not enabled', 'wpshadow' );
		}
		if ( defined( 'FORCE_SSL' ) === false || ! FORCE_SSL ) {
			$issues[] = __( 'SSL not forced', 'wpshadow' );
		}
		// Additional checks
		if ( ! function_exists( 'wp_verify_nonce' ) ) {
			$issues[] = __( 'Nonce verification unavailable', 'wpshadow' );
		}
		return null;
	}
}
