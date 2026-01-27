<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Attribute_Escaping_Test extends Diagnostic_Base {
	protected static $slug = 'attribute-escaping-test';
	protected static $title = 'Attribute Escaping Test';
	protected static $description = 'Confirms esc_attr works correctly';
	protected static $family = 'security';
	public static function check() {
		if ( ! function_exists( 'esc_attr' ) ) { return null; }
		$test_input = '" onclick="alert(\'xss\')"';
		$escaped = esc_attr( $test_input );
		if ( strpos( $escaped, 'onclick' ) !== false ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'Attribute escaping may not be working correctly. This is a security concern. Verify WordPress is properly escaping HTML attributes.', 'wpshadow' ),
				'severity' => 'critical',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/attribute-escaping-test',
				'meta' => array(),
			);
		}
		return null;
	}
}
