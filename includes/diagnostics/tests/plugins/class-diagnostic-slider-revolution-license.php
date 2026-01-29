<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_SliderRevolutionLicense extends Diagnostic_Base {
	protected static $slug = 'slider-revolution-license';
	protected static $title = 'Slider Revolution License';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'RevSliderFront' ) ) { return null; }
		$license = get_option( 'revslider-valid', '' );
		if ( empty( $license ) || $license !== 'true' ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'Slider Revolution license not validated', 'wpshadow' ),
				'severity' => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/slider-license',
			);
		}
		return null;
	}
}
