<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_GravityFormsPerformance extends Diagnostic_Base {
	protected static $slug = 'gravity-forms-performance';
	protected static $title = 'Gravity Forms Performance';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'GFForms' ) ) { return null; }
		$no_conflict = get_option( 'gform_enable_noconflict', false );
		if ( ! $no_conflict ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'No-conflict mode disabled', 'wpshadow' ),
				'severity' => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/gravity-forms-performance',
			);
		}
		return null;
	}
}
