<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_WpRocketConflicts extends Diagnostic_Base {
	protected static $slug = 'wp-rocket-conflicts';
	protected static $title = 'WP Rocket Hosting Conflicts';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! function_exists( 'rocket_direct_filesystem' ) ) { return null; }
		if ( defined( 'WPE_PLUGIN_VERSION' ) || defined( 'KINSTA_CACHE_VERSION' ) ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'Potential conflict with managed hosting cache', 'wpshadow' ),
				'severity' => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/wp-rocket-hosting-conflicts',
			);
		}
		return null;
	}
}
