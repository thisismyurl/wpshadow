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
		if ( ! class_exists( 'ACF' ) ) { return null; }
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
		return null;
	}
}
