<?php
/**
 * Core Integrity Diagnostic
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

/**
 * Verify WordPress core files against official checksums.
 */
class Diagnostic_Core_Integrity {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		$issues = self::verify_checksums();
		if ( empty( $issues ) ) {
			return null;
		}
		
		return array(
			'id'           => 'core-integrity-mismatch',
			'title'        => 'Core Files Modified',
			'description'  => 'WordPress core files differ from the official checksums. Reinstall core or restore clean files.',
			'color'        => '#f44336',
			'bg_color'     => '#ffebee',
			'kb_link'      => 'https://wpshadow.com/kb/verify-wordpress-checksums/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=core-integrity',
			'auto_fixable' => false,
			'threat_level' => 85,
		);
	}
	
	/**
	 * Verify core checksums.
	 *
	 * @return array List of mismatched files.
	 */
	private static function verify_checksums() {
		if ( ! function_exists( 'get_core_checksums' ) || ! function_exists( 'wp_normalize_path' ) ) {
			return array();
		}
		
		$locale    = get_locale();
		$version   = get_bloginfo( 'version' );
		$checksums = get_core_checksums( $version, $locale );
		if ( ! is_array( $checksums ) ) {
			return array();
		}
		
		$root      = wp_normalize_path( ABSPATH );
		$mismatches = array();
		
		foreach ( $checksums as $file => $checksum ) {
			$path = wp_normalize_path( $root . $file );
			if ( ! file_exists( $path ) ) {
				$mismatches[] = $file . ' (missing)';
				continue;
			}
			$md5 = md5_file( $path );
			if ( $md5 !== $checksum ) {
				$mismatches[] = $file;
			}
		}
		
		return $mismatches;
	}
}
