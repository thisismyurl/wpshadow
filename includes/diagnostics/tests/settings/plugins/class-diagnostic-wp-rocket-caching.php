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
		if ( ! function_exists( 'rocket_direct_filesystem' ) ) { if ( isset( $issues ) && ! empty( $issues ) ) {
		return array(
			'id' => self::$slug,
			'title' => self::$title,
			'description' => sprintf(
				__( 'Found %d issues', 'wpshadow' ),
				count( $issues )
			),
			'severity' => 'medium',
			'threat_level' => 45,
			'auto_fixable' => false,
			'kb_link' => 'https://wpshadow.com/kb/wp-rocket-caching',
		);
	}
	return null; }
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
		
	if ( ! (! empty( $settings["lazyload"] )) ) {
		if ( ! isset( $issues ) ) {
			$issues = array();
		}
		$issues[] = __( 'Image lazy loading', 'wpshadow' );
	}

	if ( ! (! empty( $settings["preload_fonts"] )) ) {
		if ( ! isset( $issues ) ) {
			$issues = array();
		}
		$issues[] = __( 'Cache preloading', 'wpshadow' );
	}
	if ( isset( $issues ) && ! empty( $issues ) ) {
		return array(
			'id' => self::$slug,
			'title' => self::$title,
			'description' => sprintf(
				__( 'Found %d issues', 'wpshadow' ),
				count( $issues )
			),
			'severity' => 'medium',
			'threat_level' => 45,
			'auto_fixable' => false,
			'kb_link' => 'https://wpshadow.com/kb/wp-rocket-caching',
		);
	}
	return null;
	}
}
