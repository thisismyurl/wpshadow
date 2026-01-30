<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_SmushBulkOptimizationStatus extends Diagnostic_Base {
	protected static $slug = 'smush-bulk-optimization-status';
	protected static $title = 'Smush Bulk Optimization';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'WP_Smush' ) ) { return null; }
		global $wpdb;
		$unoptimized = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'attachment' AND post_mime_type LIKE 'image/%'" );
		if ( $unoptimized > 100 ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => sprintf( __( '%d images may need optimization', 'wpshadow' ), $unoptimized ),
				'severity' => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/smush-bulk',
			);
		}
		return null;
	}
}
