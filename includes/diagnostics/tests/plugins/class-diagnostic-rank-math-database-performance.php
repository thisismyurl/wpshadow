<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_RankMathDatabasePerformance extends Diagnostic_Base {
	protected static $slug = 'rank-math-database-performance';
	protected static $title = 'Rank Math Database Performance';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'RankMath' ) ) { return null; }
		global $wpdb;
		$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key LIKE 'rank_math_%'" );
		if ( $count > 50000 ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => sprintf( __( '%d Rank Math meta rows may impact performance', 'wpshadow' ), $count ),
				'severity' => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/rank-math-performance',
			);
		}
		return null;
	}
}
