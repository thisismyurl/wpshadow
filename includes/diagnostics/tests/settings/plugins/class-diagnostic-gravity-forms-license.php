<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_GravityFormsLicense extends Diagnostic_Base {
	protected static $slug = 'gravity-forms-license';
	protected static $title = 'Gravity Forms License';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'GFForms' ) ) { return null; }
		$license = get_option( 'gf_license_key', '' );
		if ( empty( $license ) ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'Gravity Forms license not configured', 'wpshadow' ),
				'severity' => 'high',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/gravity-forms-license',
			);
		}
		return null;
	}
}
