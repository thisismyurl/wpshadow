<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_AcfDatabasePerformance extends Diagnostic_Base {
	protected static $slug = 'acf-database-performance';
	protected static $title = 'ACF Database Performance';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'ACF' ) ) { return null; }
		global $wpdb;
		$meta_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key LIKE '_%'" );
		if ( $meta_count > 100000 ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => sprintf( __( '%d ACF meta rows impact performance', 'wpshadow' ), $meta_count ),
				'severity' => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/acf-database',
			);
		}
		return null;
	}
}
