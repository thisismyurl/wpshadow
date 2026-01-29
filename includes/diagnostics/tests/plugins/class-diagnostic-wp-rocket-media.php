<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_WpRocketMedia extends Diagnostic_Base {
	protected static $slug = 'wp-rocket-media';
	protected static $title = 'WP Rocket Media Optimization';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! function_exists( 'rocket_direct_filesystem' ) ) { return null; }
		$settings = get_option( 'wp_rocket_settings', array() );
		if ( empty( $settings['lazyload'] ) ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'LazyLoad not enabled', 'wpshadow' ),
				'severity' => 'high',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/wp-rocket-media',
			);
		}
		return null;
	}
}
