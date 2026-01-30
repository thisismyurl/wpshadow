<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_LitespeedServerCompatibility extends Diagnostic_Base {
	protected static $slug = 'litespeed-server-compatibility';
	protected static $title = 'LiteSpeed Server Compatibility';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! defined( 'LSCWP_V' ) ) { return null; }
		if ( ! function_exists( 'litespeed_server_type' ) && ! isset( $_SERVER['X-LiteSpeed'] ) ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'LiteSpeed server not detected', 'wpshadow' ),
				'severity' => 'critical',
				'threat_level' => 90,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/litespeed-server',
			);
		}
		return null;
	}
}
