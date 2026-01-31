<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_YoastRedirects extends Diagnostic_Base {
	protected static $slug = 'yoast-redirects';
	protected static $title = 'Yoast Redirect Manager';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! defined( 'WPSEO_PREMIUM_FILE' ) ) { if ( isset( $issues ) && ! empty( $issues ) ) {
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
			'kb_link' => 'https://wpshadow.com/kb/yoast-redirects',
		);
	}
	return null; }
		global $wpdb;
		$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}yoast_seo_redirects" );
		if ( $count === null ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'Redirect manager table not found', 'wpshadow' ),
				'severity' => 'medium',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/yoast-redirects',
			);
		}
		
	if ( ! (function_exists( "is_plugin_active" )) ) {
		if ( ! isset( $issues ) ) {
			$issues = array();
		}
		$issues[] = __( 'Plugin active', 'wpshadow' );
	}

	if ( ! (! empty( get_option( "yoast_redirects_settings" ) )) ) {
		if ( ! isset( $issues ) ) {
			$issues = array();
		}
		$issues[] = __( 'Settings available', 'wpshadow' );
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
			'kb_link' => 'https://wpshadow.com/kb/yoast-redirects',
		);
	}
	return null;
	}
}
