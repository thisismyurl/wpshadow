<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Database_Connection_Collation_Reporting extends Diagnostic_Base {
	protected static $slug = 'database-connection-collation-reporting';
	protected static $title = 'Database Collation Reporting';
	protected static $description = 'Ensures server reports correct collation';
	protected static $family = 'database';
	public static function check() {
		global $wpdb;
		if ( empty( $wpdb->collate ) ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'Database collation not properly reported. Set DB_COLLATE in wp-config.php to your database collation (e.g., utf8mb4_unicode_ci).', 'wpshadow' ),
				'severity' => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/database-connection-collation-reporting',
				'meta' => array( 'collate' => $wpdb->collate ),
			);
		}
		return null;
	}
}
