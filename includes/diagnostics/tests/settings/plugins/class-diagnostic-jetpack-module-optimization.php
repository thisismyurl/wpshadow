<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_JetpackModuleOptimization extends Diagnostic_Base {
	protected static $slug = 'jetpack-module-optimization';
	protected static $title = 'Jetpack Module Optimization';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'Jetpack' ) ) { return null; }
		$active = \Jetpack::get_active_modules();
		if ( count( $active ) > 20 ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => sprintf( __( '%d modules active - potential bloat', 'wpshadow' ), count( $active ) ),
				'severity' => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/jetpack-optimization',
			);
		}
		return null;
	}
}
