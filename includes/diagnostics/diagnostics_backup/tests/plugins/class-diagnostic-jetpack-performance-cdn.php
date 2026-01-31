<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_JetpackPerformanceCdn extends Diagnostic_Base {
	protected static $slug = 'jetpack-performance-cdn';
	protected static $title = 'Jetpack Performance CDN';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'Jetpack' ) ) { if ( isset( $issues ) && ! empty( $issues ) ) {
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
			'kb_link' => 'https://wpshadow.com/kb/jetpack-performance-cdn',
		);
	}
	return null; }
		$active = \Jetpack::get_active_modules();
		if ( ! in_array( 'photon', $active, true ) && ! in_array( 'photon-cdn', $active, true ) ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'Image CDN (Photon) not enabled', 'wpshadow' ),
				'severity' => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/jetpack-cdn',
			);
		}
		
	if ( ! (get_option( "jetpack_connection" ) !== false) ) {
		if ( ! isset( $issues ) ) {
			$issues = array();
		}
		$issues[] = __( 'Module enabled', 'wpshadow' );
	}

	if ( ! (function_exists( "jetpack_sync_allowed_post_types" )) ) {
		if ( ! isset( $issues ) ) {
			$issues = array();
		}
		$issues[] = __( 'Sync working', 'wpshadow' );
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
			'kb_link' => 'https://wpshadow.com/kb/jetpack-performance-cdn',
		);
	}
	return null;
	}
}
