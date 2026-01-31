<?php
/**
 * Core File Integrity Verification Diagnostic
 *
 * Compares installed WordPress core files against official checksums to detect modifications.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26028.1905
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Core File Integrity Verification Class
 *
 * Tests WordPress core files against official checksums.
 *
 * @since 1.26028.1905
 */
class Diagnostic_Core_File_Integrity_Verification extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'core-file-integrity-verification';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Core File Integrity Verification';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Compares installed WordPress core files against official checksums to detect modifications';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26028.1905
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_version;

		// Get official checksums.
		$checksums = self::get_core_checksums( $wp_version );
		
		if ( ! $checksums ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Unable to retrieve WordPress core checksums for verification', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 90,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/core-file-integrity-verification',
				'meta'         => array(
					'wp_version' => $wp_version,
				),
			);
		}

		// Verify core files.
		$verification = self::verify_core_files( $checksums );
		
		if ( $verification['modified_files'] > 0 || $verification['unexpected_files'] > 0 ) {
			$severity = 'high';
			if ( $verification['modified_files'] > 10 ) {
				$severity = 'critical';
			}

			$issues = array();
			
			if ( $verification['modified_files'] > 0 ) {
				$issues[] = sprintf(
					/* translators: %d: number of modified files */
					__( '%d core files modified', 'wpshadow' ),
					$verification['modified_files']
				);
			}

			if ( $verification['unexpected_files'] > 0 ) {
				$issues[] = sprintf(
					/* translators: %d: number of unexpected files */
					__( '%d unexpected files in core directories', 'wpshadow' ),
					$verification['unexpected_files']
				);
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $issues ) . ' ' . __( '(possible security breach or malware)', 'wpshadow' ),
				'severity'     => $severity,
				'threat_level' => 90,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/core-file-integrity-verification',
				'meta'         => array(
					'wp_version'          => $wp_version,
					'modified_files'      => $verification['modified_files'],
					'unexpected_files'    => $verification['unexpected_files'],
					'sample_modified'     => array_slice( $verification['modified_file_list'], 0, 10 ),
					'sample_unexpected'   => array_slice( $verification['unexpected_file_list'], 0, 10 ),
				),
			);
		}

		return null;
	}

	/**
	 * Get official WordPress core checksums.
	 *
	 * @since  1.26028.1905
	 * @param  string $version WordPress version.
	 * @return array|false Array of checksums or false on failure.
	 */
	private static function get_core_checksums( $version ) {
		$url = 'https://api.wordpress.org/core/checksums/1.0/?version=' . $version;
		
		$response = wp_remote_get( $url, array( 'timeout' => 10 ) );
		
		if ( is_wp_error( $response ) ) {
			return false;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( ! isset( $data['checksums'] ) ) {
			return false;
		}

		return $data['checksums'];
	}

	/**
	 * Verify core files against checksums.
	 *
	 * @since  1.26028.1905
	 * @param  array $checksums Official checksums.
	 * @return array Verification results.
	 */
	private static function verify_core_files( $checksums ) {
		$results = array(
			'modified_files'       => 0,
			'unexpected_files'     => 0,
			'modified_file_list'   => array(),
			'unexpected_file_list' => array(),
		);

		// Check each file in checksums.
		foreach ( $checksums as $file => $expected_hash ) {
			$file_path = ABSPATH . $file;
			
			if ( ! file_exists( $file_path ) ) {
				continue; // Missing file (separate issue).
			}

			// Calculate actual hash.
			$actual_hash = md5_file( $file_path );
			
			if ( $actual_hash !== $expected_hash ) {
				++$results['modified_files'];
				$results['modified_file_list'][] = $file;
			}
		}

		// Check for unexpected files in core directories.
		$core_dirs = array(
			ABSPATH . 'wp-admin/',
			ABSPATH . 'wp-includes/',
		);

		foreach ( $core_dirs as $dir ) {
			$unexpected = self::find_unexpected_files( $dir, $checksums );
			$results['unexpected_files'] += count( $unexpected );
			$results['unexpected_file_list'] = array_merge( $results['unexpected_file_list'], $unexpected );
		}

		return $results;
	}

	/**
	 * Find unexpected files in directory.
	 *
	 * @since  1.26028.1905
	 * @param  string $dir Directory to scan.
	 * @param  array  $checksums Official checksums.
	 * @return array Array of unexpected file paths.
	 */
	private static function find_unexpected_files( $dir, $checksums ) {
		$unexpected = array();

		if ( ! is_dir( $dir ) ) {
			return $unexpected;
		}

		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $dir, \RecursiveDirectoryIterator::SKIP_DOTS ),
			\RecursiveIteratorIterator::SELF_FIRST
		);

		foreach ( $iterator as $file ) {
			if ( $file->isFile() ) {
				$file_path = $file->getPathname();
				$relative_path = str_replace( ABSPATH, '', $file_path );

				// Check if file is in checksums.
				if ( ! isset( $checksums[ $relative_path ] ) ) {
					// Check common exceptions.
					$basename = basename( $file_path );
					$exceptions = array( '.htaccess', 'index.php', 'readme.html', 'wp-config.php' );
					
					if ( ! in_array( $basename, $exceptions, true ) ) {
						$unexpected[] = $relative_path;
					}
				}
			}
		}

		return $unexpected;
	}
}
