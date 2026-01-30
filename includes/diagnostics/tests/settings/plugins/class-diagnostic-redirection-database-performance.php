<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_RedirectionDatabasePerformance extends Diagnostic_Base {
	protected static $slug = 'redirection-database-performance';
	protected static $title = 'Redirection Database Performance';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'Red_Item' ) ) { return null; }
		global $wpdb;
		$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_items" );
		if ( $count > 10000 ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => sprintf( __( '%d redirects may impact performance', 'wpshadow' ), $count ),
				'severity' => 'high',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/redirection-performance',
			);
		}
		return null;
	}
}
