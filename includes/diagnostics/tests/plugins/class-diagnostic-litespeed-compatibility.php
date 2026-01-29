<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_LitespeedCompatibility extends Diagnostic_Base {
	protected static $slug = 'litespeed-compatibility';
	protected static $title = 'LiteSpeed Plugin Compatibility';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! defined( 'LSCWP_V' ) ) { return null; }
		$conflicts = array();
		if ( is_plugin_active( 'wp-rocket/wp-rocket.php' ) ) { $conflicts[] = 'WP Rocket'; }
		if ( is_plugin_active( 'w3-total-cache/w3-total-cache.php' ) ) { $conflicts[] = 'W3 Total Cache'; }
		if ( ! empty( $conflicts ) ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => sprintf( __( 'Conflicts with: %s', 'wpshadow' ), implode( ', ', $conflicts ) ),
				'severity' => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/litespeed-conflicts',
			);
		}
		return null;
	}
}
