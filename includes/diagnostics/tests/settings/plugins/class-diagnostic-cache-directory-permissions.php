<?php
/**
 * Cache Directory Permissions Diagnostic
 *
 * Validates cache directory write permissions.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5029.1810
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cache Directory Permissions Class
 *
 * Checks cache folder permissions.
 *
 * @since 1.5029.1810
 */
class Diagnostic_Cache_Directory_Permissions extends Diagnostic_Base {

	protected static $slug        = 'cache-directory-permissions';
	protected static $title       = 'Cache Directory Permissions';
	protected static $description = 'Validates cache folder access';
	protected static $family      = 'plugins';

	public static function check() {
		$cache_key = 'wpshadow_cache_perms';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$issues = array();
		$cache_paths = array();

		// Check common cache directories.
		$wp_content = WP_CONTENT_DIR;
		$cache_dirs = array(
			$wp_content . '/cache',
			$wp_content . '/cache/wpfc-minified',
			$wp_content . '/w3tc-cache',
			$wp_content . '/wp-rocket-cache',
			$wp_content . '/cache-enabler',
		);

		foreach ( $cache_dirs as $dir ) {
			if ( file_exists( $dir ) ) {
				$cache_paths[] = $dir;

				// Check if writable.
				if ( ! is_writable( $dir ) ) {
					$issues[] = sprintf( '%s is not writable', basename( $dir ) );
				}

				// Check permissions.
				$perms = fileperms( $dir );
				$octal = substr( sprintf( '%o', $perms ), -4 );
				
				if ( '0755' !== $octal && '0775' !== $octal ) {
					$issues[] = sprintf( '%s has permissions %s (should be 0755 or 0775)', basename( $dir ), $octal );
				}

				// Check disk space.
				$free_space = disk_free_space( $dir );
				$total_space = disk_total_space( $dir );
				$used_percent = ( ( $total_space - $free_space ) / $total_space ) * 100;

				if ( $used_percent > 90 ) {
					$issues[] = sprintf( 'Disk space critical: %d%% used', round( $used_percent ) );
				}
			}
		}

		if ( empty( $cache_paths ) ) {
			// No cache directories found - may indicate cache not working.
			$issues[] = 'No cache directories found';
		}

		if ( ! empty( $issues ) ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: count */
					__( '%d cache directory permission issues detected.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/cache-directory-permissions',
				'data'         => array(
					'permission_issues' => $issues,
					'total_issues' => count( $issues ),
					'cache_directories' => $cache_paths,
				),
			);

			set_transient( $cache_key, $result, 12 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}
}
