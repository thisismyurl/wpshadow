<?php
declare(strict_types=1);
/**
 * Core File Integrity Diagnostic
 *
 * Philosophy: Malware detection - verify core files untampered
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if WordPress core files have been modified.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Core_File_Integrity extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wp_version;

		// Get core checksums from WordPress.org
		$checksums_url = 'https://api.wordpress.org/core/checksums/1.0/?version=' . $wp_version;
		$response      = wp_remote_get( $checksums_url, array( 'timeout' => 10 ) );

		if ( is_wp_error( $response ) ) {
			return null; // Can't check
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( empty( $data['checksums'] ) ) {
			return null;
		}

		$checksums      = $data['checksums'];
		$modified_files = array();

		// Check a sample of critical files
		$critical_files = array( 'wp-login.php', 'wp-settings.php', 'wp-load.php' );

		foreach ( $critical_files as $file ) {
			if ( ! isset( $checksums[ $file ] ) ) {
				continue;
			}

			$filepath = ABSPATH . $file;
			if ( file_exists( $filepath ) ) {
				$file_hash = md5_file( $filepath );
				if ( $file_hash !== $checksums[ $file ] ) {
					$modified_files[] = $file;
				}
			}
		}

		if ( ! empty( $modified_files ) ) {
			return array(
				'id'            => 'core-file-integrity',
				'title'         => 'Core Files Modified',
				'description'   => sprintf(
					'WordPress core files have been modified: %s. This may indicate malware injection or unauthorized changes. Restore original files immediately.',
					implode( ', ', $modified_files )
				),
				'severity'      => 'critical',
				'category'      => 'security',
				'kb_link'       => 'https://wpshadow.com/kb/verify-core-integrity/',
				'training_link' => 'https://wpshadow.com/training/malware-detection/',
				'auto_fixable'  => false,
				'threat_level'  => 95,
			);
		}

		return null;
	}
}
