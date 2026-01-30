<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_NinjaFormsDatabasePerformance extends Diagnostic_Base {
	protected static $slug = 'ninja-forms-database-performance';
	protected static $title = 'Ninja Forms Database Performance';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'Ninja_Forms' ) ) { return null; }
		global $wpdb;
		$submissions = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}nf3_submissions" );
		if ( $submissions > 10000 ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => sprintf( __( '%d submissions may slow queries', 'wpshadow' ), $submissions ),
				'severity' => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/ninja-forms-performance',
			);
		}
		return null;
	}
}
