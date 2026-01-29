<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_AcfFieldGroupOptimization extends Diagnostic_Base {
	protected static $slug = 'acf-field-group-optimization';
	protected static $title = 'ACF Field Group Optimization';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'ACF' ) ) { return null; }
		$groups = acf_get_field_groups();
		if ( count( $groups ) > 50 ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => sprintf( __( '%d field groups may slow admin', 'wpshadow' ), count( $groups ) ),
				'severity' => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/acf-optimization',
			);
		}
		return null;
	}
}
