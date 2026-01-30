<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_JetpackPerformanceCdn extends Diagnostic_Base {
	protected static $slug = 'jetpack-performance-cdn';
	protected static $title = 'Jetpack Performance CDN';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'Jetpack' ) ) { return null; }
		$active = \Jetpack::get_active_modules();
		if ( ! in_array( 'photon', $active, true ) && ! in_array( 'photon-cdn', $active, true ) ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'Image CDN (Photon) not enabled', 'wpshadow' ),
				'severity' => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/jetpack-cdn',
			);
		}
		return null;
	}
}
