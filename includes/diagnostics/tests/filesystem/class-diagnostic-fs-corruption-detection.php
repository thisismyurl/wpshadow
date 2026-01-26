<?php
/**
 * Filesystem Corruption Detection Diagnostic
 *
 * Identifies unreadable or partially written files.
 *
 * @since   1.2601.2112
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Fs_Corruption_Detection
 *
 * Scans WordPress directories for corrupted, incomplete, or unreadable files.
 *
 * @since 1.2601.2112
 */
class Diagnostic_Fs_Corruption_Detection extends Diagnostic_Base {

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2112
	 * @return array|null Finding array if issues detected, null otherwise.
	 */
	public static function check() {
		if ( ! is_admin() ) {
			return null;
		}

		$corrupted = array();

		// Check wp-content/uploads for zero-byte or corrupted images.
		$upload_dir = wp_upload_dir();
		$basedir    = $upload_dir['basedir'] ?? '';

		if ( ! empty( $basedir ) && is_dir( $basedir ) ) {
			$iterator = new \RecursiveIteratorIterator(
				new \RecursiveDirectoryIterator( $basedir, \RecursiveDirectoryIterator::SKIP_DOTS ),
				\RecursiveIteratorIterator::LEAVES_ONLY
			);

			foreach ( $iterator as $file ) {
				// Check for zero-byte files or unreadable files.
				if ( $file->isFile() ) {
					$size = $file->getSize();
					if ( 0 === $size ) {
						$corrupted[] = str_replace( ABSPATH, '', $file->getRealPath() );
					} elseif ( ! is_readable( $file->getRealPath() ) ) {
						$corrupted[] = str_replace( ABSPATH, '', $file->getRealPath() );
					}
				}
			}
		}

		if ( ! empty( $corrupted ) ) {
			return array(
				'id'           => 'fs-corruption-detection',
				'title'        => __( 'Corrupted or Unreadable Files Detected', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %s: file count */
					__( 'Found %d zero-byte or unreadable files in uploads directory. This can affect media functionality. Consider deleting orphaned files or restoring from backup.', 'wpshadow' ),
					count( $corrupted )
				),
				'severity'     => 'medium',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/fs_corruption_detection',
				'meta'         => array(
					'corrupted_count' => count( $corrupted ),
					'sample_files'    => array_slice( $corrupted, 0, 5 ),
				),
			);
		}

		return null;
	}
}
