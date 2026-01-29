<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_WpRocketCaching extends Diagnostic_Base {
	protected static $slug = 'wp-rocket-caching';
	protected static $title = 'WP Rocket Core Caching';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! function_exists( 'rocket_direct_filesystem' ) ) { return null; }
		$settings = get_option( 'wp_rocket_settings', array() );
		if ( empty( $settings['cache_mobile'] ) && empty( $settings['do_caching_mobile_files'] ) ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'Mobile caching not enabled', 'wpshadow' ),
				'severity' => 'high',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/wp-rocket-caching',
			);
		}
		return null;
	}
}
