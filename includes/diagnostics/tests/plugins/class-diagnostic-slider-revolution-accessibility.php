<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_SliderRevolutionAccessibility extends Diagnostic_Base {
	protected static $slug = 'slider-revolution-accessibility';
	protected static $title = 'Slider Revolution Accessibility';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'RevSliderFront' ) ) { return null; }
		$keyboard = get_option( 'revslider-keyboard-navigation', false );
		if ( ! $keyboard ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'Keyboard navigation disabled', 'wpshadow' ),
				'severity' => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/slider-accessibility',
			);
		}
		return null;
	}
}
