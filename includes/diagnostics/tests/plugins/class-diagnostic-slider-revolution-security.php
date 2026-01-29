<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_SliderRevolutionSecurity extends Diagnostic_Base {
	protected static $slug = 'slider-revolution-security';
	protected static $title = 'Slider Revolution Security';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'RevSliderFront' ) ) { return null; }
		if ( ! defined( 'RS_REVISION' ) ) { return null; }
		$version = RS_REVISION;
		$vulnerable = array( '4.1.4', '4.2', '5.0.0' );
		if ( in_array( $version, $vulnerable, true ) ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => sprintf( __( 'Vulnerable version %s', 'wpshadow' ), $version ),
				'severity' => 'critical',
				'threat_level' => 95,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/slider-security',
			);
		}
		return null;
	}
}
