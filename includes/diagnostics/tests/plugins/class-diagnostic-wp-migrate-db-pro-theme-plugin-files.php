<?php
/**
 * Wp Migrate Db Pro Theme Plugin Files Diagnostic
 *
 * Wp Migrate Db Pro Theme Plugin Files issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1087.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wp Migrate Db Pro Theme Plugin Files Diagnostic Class
 *
 * @since 1.1087.0000
 */
class Diagnostic_WpMigrateDbProThemePluginFiles extends Diagnostic_Base {

	protected static $slug = 'wp-migrate-db-pro-theme-plugin-files';
	protected static $title = 'Wp Migrate Db Pro Theme Plugin Files';
	protected static $description = 'Wp Migrate Db Pro Theme Plugin Files issue detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'WPMDB_Pro' ) && ! defined( 'WPMDB_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Theme files addon enabled
		$theme_addon = get_option( 'wpmdb_theme_plugin_files_enabled', '0' );
		if ( '1' === $theme_addon ) {
			$theme_excludes = get_option( 'wpmdb_theme_excludes', array() );
			if ( empty( $theme_excludes ) ) {
				$issues[] = 'theme migration enabled with no exclusions (may include sensitive files)';
			}
		}
		
		// Check 2: Plugin files addon enabled
		$plugin_addon = get_option( 'wpmdb_plugin_files_enabled', '0' );
		if ( '1' === $plugin_addon ) {
			$plugin_excludes = get_option( 'wpmdb_plugin_excludes', array() );
			if ( empty( $plugin_excludes ) ) {
				$issues[] = 'plugin migration enabled with no exclusions (large transfer size)';
			}
		}
		
		// Check 3: Connection security
		$connections = get_option( 'wpmdb_saved_profiles', array() );
		if ( ! empty( $connections ) && is_array( $connections ) ) {
			foreach ( $connections as $connection ) {
				if ( isset( $connection['url'] ) && 0 === strpos( $connection['url'], 'http://' ) ) {
					$issues[] = 'unencrypted connections detected (use HTTPS)';
					break;
				}
			}
		}
		
		// Check 4: Large file handling
		$max_file_size = get_option( 'wpmdb_max_file_size', 2097152 ); // 2MB default
		if ( ( '1' === $theme_addon || '1' === $plugin_addon ) && $max_file_size > 10485760 ) {
			$size_mb = round( $max_file_size / 1048576 );
			$issues[] = "large file size limit ({$size_mb}MB, may cause timeouts)";
		}
		
		// Check 5: Temporary file cleanup
		$temp_dir = WP_CONTENT_DIR . '/uploads/wp-migrate-db/';
		if ( is_dir( $temp_dir ) ) {
			$temp_files = glob( $temp_dir . '*' );
			if ( is_array( $temp_files ) && count( $temp_files ) > 10 ) {
				$temp_count = count( $temp_files );
				$issues[] = "{$temp_count} temporary files not cleaned up";
			}
		}
		
		// Check 6: Migration log errors
		$migration_log = get_option( 'wpmdb_migration_log', array() );
		if ( ! empty( $migration_log ) && is_array( $migration_log ) ) {
			$error_count = 0;
			foreach ( $migration_log as $entry ) {
				if ( isset( $entry['type'] ) && 'error' === $entry['type'] ) {
					$error_count++;
				}
			}
			if ( $error_count > 5 ) {
				$issues[] = "{$error_count} migration errors logged";
			}
		}
		
		if ( ! empty( $issues ) ) {
			$threat_level = min( 70, 40 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'WP Migrate DB Pro file migration issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wp-migrate-db-pro-theme-plugin-files',
			);
		}
		
		return null;
	}
}
