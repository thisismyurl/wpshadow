<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_JetpackConnectionStatus extends Diagnostic_Base {
	protected static $slug = 'jetpack-connection-status';
	protected static $title = 'Jetpack Connection Status';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'Jetpack' ) ) { return null; }
		if ( ! \Jetpack::is_connection_ready() ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'Jetpack not connected to WordPress.com', 'wpshadow' ),
				'severity' => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/jetpack-connection',
			);
		}
		return null;
	}
}
