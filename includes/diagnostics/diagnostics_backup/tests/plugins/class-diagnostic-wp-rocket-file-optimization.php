<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_WpRocketFileOptimization extends Diagnostic_Base {
	protected static $slug = 'wp-rocket-file-optimization';
	protected static $title = 'WP Rocket File Optimization';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! function_exists( 'rocket_direct_filesystem' ) ) { return null; }
		$settings = get_option( 'wp_rocket_settings', array() );
		$issues = array();
		if ( empty( $settings['minify_css'] ) ) { $issues[] = 'CSS minification disabled'; }
		if ( empty( $settings['minify_js'] ) ) { $issues[] = 'JS minification disabled'; }
		if ( ! empty( $issues ) ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => sprintf( __( '%d optimization issues', 'wpshadow' ), count( $issues ) ),
				'severity' => 'high',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/wp-rocket-optimization',
			);
		}

		// Plugin integration checks
		if ( ! function_exists( 'get_plugins' ) ) {
			$issues[] = __( 'Plugin listing not available', 'wpshadow' );
		}
		if ( ! function_exists( 'is_plugin_active' ) ) {
			$issues[] = __( 'Plugin status check unavailable', 'wpshadow' );
		}
		// Verify integration point
		if ( ! function_exists( 'do_action' ) ) {
			$issues[] = __( 'Action hooks unavailable', 'wpshadow' );
		}
		return null;
	}
}
