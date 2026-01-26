<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Sql_Injection_Protection extends Diagnostic_Base {
	protected static $slug = 'sql-injection-protection';
	protected static $title = 'SQL Injection Protection';
	protected static $description = 'Confirms wpdb->prepare uses placeholders';
	protected static $family = 'security';
	public static function check() {
		global $wpdb;
		if ( empty( $wpdb ) ) { return null; }
		$test_query = $wpdb->prepare( "SELECT * FROM {$wpdb->posts} WHERE ID = %d", 1 );
		if ( strpos( $test_query, '%d' ) !== false ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'SQL prepare statement may not be replacing placeholders correctly. Ensure all database queries use wpdb->prepare() with proper placeholders.', 'wpshadow' ),
				'severity' => 'critical',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/sql-injection-protection',
				'meta' => array(),
			);
		}
		return null;
	}
}
