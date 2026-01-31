<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_Redirection404Monitoring extends Diagnostic_Base {
	protected static $slug = 'redirection-404-monitoring';
	protected static $title = 'Redirection 404 Monitoring';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'Red_Item' ) ) { return null; }
		$options = get_option( 'redirection_options', array() );
		if ( empty( $options['monitor_post'] ) && empty( $options['log_404'] ) ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( '404 monitoring disabled - missing SEO opportunities', 'wpshadow' ),
				'severity' => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/redirection-404-monitoring',
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
