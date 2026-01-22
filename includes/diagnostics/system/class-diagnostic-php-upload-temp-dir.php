<?php
declare(strict_types=1);
/**
 * PHP Upload Temp Directory Diagnostic
 *
 * Philosophy: File security - clean temporary files
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check PHP upload temp directory for old files.
 */
class Diagnostic_PHP_Upload_Temp_Dir extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$upload_tmp_dir = ini_get( 'upload_tmp_dir' );
		
		if ( empty( $upload_tmp_dir ) ) {
			$upload_tmp_dir = sys_get_temp_dir();
		}
		
		if ( ! is_dir( $upload_tmp_dir ) || ! is_readable( $upload_tmp_dir ) ) {
			return null;
		}
		
		// Count PHP temp files
		$pattern = $upload_tmp_dir . '/php*';
		$temp_files = glob( $pattern );
		
		if ( empty( $temp_files ) ) {
			return null;
		}
		
		$old_files = 0;
		$total_size = 0;
		
		foreach ( $temp_files as $file ) {
			if ( is_file( $file ) ) {
				$age = time() - filemtime( $file );
				$total_size += filesize( $file );
				
				// Files older than 1 hour
				if ( $age > 3600 ) {
					$old_files++;
				}
			}
		}
		
		if ( $old_files > 50 ) {
			return array(
				'id'          => 'php-upload-temp-dir',
				'title'       => 'Excessive Old Temporary Upload Files',
				'description' => sprintf(
					'Found %d temporary upload files older than 1 hour in %s (total: %s). Temp files should auto-delete but aren\'t. Old malware uploads may persist. Configure proper temp file cleanup.',
					$old_files,
					$upload_tmp_dir,
					size_format( $total_size )
				),
				'severity'    => 'medium',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/clean-temp-files/',
				'training_link' => 'https://wpshadow.com/training/temp-file-security/',
				'auto_fixable' => true,
				'threat_level' => 70,
			);
		}
		
		return null;
	}
}
