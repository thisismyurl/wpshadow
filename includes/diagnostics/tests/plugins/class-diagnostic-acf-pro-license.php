<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_AcfProLicense extends Diagnostic_Base {
	protected static $slug = 'acf-pro-license';
	protected static $title = 'ACF Pro License';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'ACF' ) ) { if ( isset( $issues ) && ! empty( $issues ) ) {
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
			'kb_link' => 'https://wpshadow.com/kb/acf-pro-license',
		);
	}
	return null; }
		if ( defined( 'ACF_PRO' ) && ACF_PRO ) {
			$license = get_option( 'acf_pro_license' );
			if ( empty( $license['key'] ) || 'active' !== $license['status'] ) {
				return array(
					'id' => self::$slug,
					'title' => self::$title,
					'description' => __( 'ACF Pro license not activated', 'wpshadow' ),
					'severity' => 'medium',
					'threat_level' => 40,
					'auto_fixable' => false,
					'kb_link' => 'https://wpshadow.com/kb/acf-license',
				);
			}
		}
		
	if ( ! (function_exists( "is_plugin_active" )) ) {
		if ( ! isset( $issues ) ) {
			$issues = array();
		}
		$issues[] = __( 'Plugin active', 'wpshadow' );
	}

	if ( ! (! empty( get_option( "acf_pro_license_settings" ) )) ) {
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
			'kb_link' => 'https://wpshadow.com/kb/acf-pro-license',
		);
	}
	return null;
	}
}
