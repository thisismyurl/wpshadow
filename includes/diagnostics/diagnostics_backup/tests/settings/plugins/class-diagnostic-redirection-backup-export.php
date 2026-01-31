<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_RedirectionBackupExport extends Diagnostic_Base {
	protected static $slug = 'redirection-backup-export';
	protected static $title = 'Redirection Backup Status';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'Red_Item' ) ) { return null; }
		global $wpdb;
		$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}redirection_items" );
		if ( $count > 100 ) {
			$export_option = get_option( 'redirection_last_export', 0 );
			if ( empty( $export_option ) || ( time() - $export_option ) > ( 30 * DAY_IN_SECONDS ) ) {
				return array(
					'id' => self::$slug,
					'title' => self::$title,
					'description' => __( 'No recent backup of redirects', 'wpshadow' ),
					'severity' => 'medium',
					'threat_level' => 45,
					'auto_fixable' => false,
					'kb_link' => 'https://wpshadow.com/kb/redirection-backup',
				);
			}
		}

		// Plugin integration checks
		if ( ! function_exists( 'get_plugins' ) ) {
			$issues[] = __( 'Plugin listing not available', 'wpshadow' );
		}
		if ( ! function_exists( 'is_plugin_active' ) ) {
			$issues[] = __( 'Plugin status check unavailable', 'wpshadow' );
		}
		// Verify integration point
		if ( ! function_exists( 'do_action' ) ) {
			$issues[] = __( 'Action hooks unavailable', 'wpshadow' );
		}
		return null;
	}
}
