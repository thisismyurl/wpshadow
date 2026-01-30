<?php
/**
 * All-in-One WP Migration Max File Size Diagnostic
 *
 * AIO WP Migration file size limits too low.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.389.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * All-in-One WP Migration Max File Size Diagnostic Class
 *
 * @since 1.389.0000
 */
class Diagnostic_AllInOneWpMigrationMaxFileSize extends Diagnostic_Base {

	protected static $slug = 'all-in-one-wp-migration-max-file-size';
	protected static $title = 'All-in-One WP Migration Max File Size';
	protected static $description = 'AIO WP Migration file size limits too low';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'AI1WM_PLUGIN_NAME' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: PHP upload_max_filesize.
		$upload_max = ini_get( 'upload_max_filesize' );
		$upload_max_bytes = wp_convert_hr_to_bytes( $upload_max );
		if ( $upload_max_bytes < 134217728 ) { // 128MB.
			$issues[] = "PHP upload_max_filesize is {$upload_max} (may be too low for large backups)";
		}
		
		// Check 2: PHP post_max_size.
		$post_max = ini_get( 'post_max_size' );
		$post_max_bytes = wp_convert_hr_to_bytes( $post_max );
		if ( $post_max_bytes < 134217728 ) { // 128MB.
			$issues[] = "PHP post_max_size is {$post_max} (should be larger than upload_max_filesize)";
		}
		
		// Check 3: PHP max_execution_time.
		$max_exec_time = ini_get( 'max_execution_time' );
		if ( $max_exec_time > 0 && $max_exec_time < 300 ) {
			$issues[] = "max_execution_time is {$max_exec_time}s (large migrations may timeout)";
		}
		
		// Check 4: PHP memory_limit.
		$memory_limit = ini_get( 'memory_limit' );
		$memory_bytes = wp_convert_hr_to_bytes( $memory_limit );
		if ( $memory_bytes < 268435456 ) { // 256MB.
			$issues[] = "memory_limit is {$memory_limit} (migrations may fail with out of memory)";
		}
		
		// Check 5: Available disk space.
		$storage_path = defined( 'AI1WM_STORAGE_PATH' ) ? AI1WM_STORAGE_PATH : WP_CONTENT_DIR . '/ai1wm-backups';
		if ( is_dir( $storage_path ) ) {
			$free_space = @disk_free_space( $storage_path );
			if ( $free_space !== false && $free_space < 1073741824 ) { // 1GB.
				$free_gb = round( $free_space / 1073741824, 2 );
				$issues[] = "only {$free_gb}GB free disk space (may not be enough for backups)";
			}
		}
		
		// Check 6: Plugin file size limit setting.
		$plugin_max_size = get_option( 'ai1wm_max_file_size', 0 );
		if ( $plugin_max_size > 0 && $plugin_max_size < 536870912 ) { // 512MB.
			$max_mb = round( $plugin_max_size / 1048576 );
			$issues[] = "plugin max file size set to {$max_mb}MB (consider increasing)";
		}
		
		if ( ! empty( $issues ) ) {
			$threat_level = min( 70, 40 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'All-in-One WP Migration file size limit issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/all-in-one-wp-migration-max-file-size',
			);
		}
		
		return null;
	}
}
