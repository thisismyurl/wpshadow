<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_ElementorProDynamicContentSecurity extends Diagnostic_Base {
	protected static $slug = 'elementor-pro-dynamic-content-security';
	protected static $title = 'Elementor Pro Dynamic Content Security';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! defined( 'ELEMENTOR_PRO_VERSION' ) ) { return null; }
		$experiments = get_option( 'elementor_experiments', array() );
		if ( ! empty( $experiments['container'] ) && 'active' === $experiments['container'] ) {
			$dynamic_tags = get_option( 'elementor_pro_dynamic_tags_usage', 0 );
			if ( $dynamic_tags > 20 ) {
				return array(
					'id' => self::$slug,
					'title' => self::$title,
					'description' => sprintf( __( '%d dynamic tags - validate data sanitization', 'wpshadow' ), $dynamic_tags ),
					'severity' => 'high',
					'threat_level' => 60,
					'auto_fixable' => false,
					'kb_link' => 'https://wpshadow.com/kb/elementor-dynamic-content',
				);
			}
		}
		return null;
	}
}
