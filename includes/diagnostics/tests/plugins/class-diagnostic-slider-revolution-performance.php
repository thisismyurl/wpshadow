<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_SliderRevolutionPerformance extends Diagnostic_Base {
	protected static $slug = 'slider-revolution-performance';
	protected static $title = 'Slider Revolution Performance';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'RevSliderFront' ) ) { return null; }
		$lazy = get_option( 'revslider-lazy-load-type', 'none' );
		if ( $lazy === 'none' ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'Lazy loading not enabled', 'wpshadow' ),
				'severity' => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/slider-performance',
			);
		}
		return null;
	}
}
