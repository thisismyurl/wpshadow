<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_JetpackSecurityFeatures extends Diagnostic_Base {
	protected static $slug = 'jetpack-security-features';
	protected static $title = 'Jetpack Security Features';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'Jetpack' ) ) { return null; }
		$active = \Jetpack::get_active_modules();
		$security_mods = array( 'protect', 'sso', 'monitor', 'scan', 'akismet' );
		$enabled = array_intersect( $security_mods, $active );
		if ( count( $enabled ) < 2 ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'Key security modules not enabled', 'wpshadow' ),
				'severity' => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/jetpack-security',
			);
		}
		return null;
	}
}
