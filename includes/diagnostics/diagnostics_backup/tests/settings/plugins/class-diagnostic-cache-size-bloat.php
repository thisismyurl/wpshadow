<?php
/**
 * Cache Size Bloat Diagnostic
 *
 * Monitors cache directory size.
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
 * Cache Size Bloat Class
 *
 * Detects excessive cache size.
 *
 * @since 1.5029.1810
 */
class Diagnostic_Cache_Size_Bloat extends Diagnostic_Base {

	protected static $slug        = 'cache-size-bloat';
	protected static $title       = 'Cache Size Bloat';
	protected static $description = 'Monitors cache directory size';
	protected static $family      = 'plugins';

	public static function check() {
		$cache_key = 'wpshadow_cache_size';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$wp_content = WP_CONTENT_DIR;
		$cache_dirs = array(
			'cache' => $wp_content . '/cache',
			'w3tc' => $wp_content . '/w3tc-cache',
			'wp-rocket' => $wp_content . '/wp-rocket-cache',
			'cache-enabler' => $wp_content . '/cache-enabler',
		);

		$total_size = 0;
		$oversized = array();

		foreach ( $cache_dirs as $name => $dir ) {
			if ( file_exists( $dir ) ) {
				$size = self::get_directory_size( $dir );
				$size_mb = round( $size / 1024 / 1024, 2 );
				$total_size += $size;

				if ( $size_mb > 500 ) {
					$oversized[] = sprintf( '%s: %sMB', $name, number_format( $size_mb, 2 ) );
				}
			}
		}

		$total_mb = round( $total_size / 1024 / 1024, 2 );

		if ( $total_mb > 1000 || ! empty( $oversized ) ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: size */
					__( 'Cache directories using %sMB. Consider cleanup.', 'wpshadow' ),
					number_format( $total_mb, 2 )
				),
				'severity'     => $total_mb > 2000 ? 'high' : 'medium',
				'threat_level' => $total_mb > 2000 ? 60 : 45,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/cache-size-management',
				'data'         => array(
					'total_size_mb' => $total_mb,
					'oversized_dirs' => $oversized,
					'recommendation' => 'Clear cache or configure automatic cleanup',
				),
			);

			set_transient( $cache_key, $result, 6 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}

	private static function get_directory_size( $dir ) {
		$size = 0;
		$files = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $dir, \RecursiveDirectoryIterator::SKIP_DOTS ),
			\RecursiveIteratorIterator::LEAVES_ONLY
		);

		foreach ( $files as $file ) {
			if ( $file->isFile() ) {
				$size += $file->getSize();
			}
		}

		return $size;
	}
}
