<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_AcfJsonSync extends Diagnostic_Base {
	protected static $slug = 'acf-json-sync';
	protected static $title = 'ACF JSON Sync';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'ACF' ) ) { return null; }
		$save_point = acf_get_setting( 'save_json' );
		if ( empty( $save_point ) || ! is_dir( $save_point ) ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'JSON sync not configured - fields not version controlled', 'wpshadow' ),
				'severity' => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/acf-json',
			);
		}
		return null;
	}
}
