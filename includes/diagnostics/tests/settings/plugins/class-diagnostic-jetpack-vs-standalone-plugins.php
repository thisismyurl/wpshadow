<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_JetpackVsStandalonePlugins extends Diagnostic_Base {
	protected static $slug = 'jetpack-vs-standalone-plugins';
	protected static $title = 'Jetpack vs Standalone Alternatives';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'Jetpack' ) ) { return null; }
		$active = \Jetpack::get_active_modules();
		if ( count( $active ) < 5 ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => sprintf( __( 'Only %d modules active - consider lighter alternatives', 'wpshadow' ), count( $active ) ),
				'severity' => 'medium',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/jetpack-alternatives',
			);
		}
		return null;
	}
}
