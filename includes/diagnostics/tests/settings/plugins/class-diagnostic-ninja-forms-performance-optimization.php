<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_NinjaFormsPerformanceOptimization extends Diagnostic_Base {
	protected static $slug = 'ninja-forms-performance-optimization';
	protected static $title = 'Ninja Forms Performance';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'Ninja_Forms' ) ) { return null; }
		global $wpdb;
		$forms = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}nf3_forms" );
		if ( $forms > 30 ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => sprintf( __( '%d forms may impact performance', 'wpshadow' ), $forms ),
				'severity' => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/ninja-forms-performance',
			);
		}
		return null;
	}
}
