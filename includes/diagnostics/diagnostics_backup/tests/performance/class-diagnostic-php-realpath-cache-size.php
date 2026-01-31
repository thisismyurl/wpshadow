<?php
/**
 * Diagnostic: PHP realpath_cache_size
 *
 * Checks if PHP realpath_cache_size is adequate for performance.
 * Caches resolved file paths to reduce system calls and improve performance.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Performance
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Php_Realpath_Cache_Size
 *
 * Tests PHP realpath_cache_size configuration.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Php_Realpath_Cache_Size extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'php-realpath-cache-size';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'PHP realpath_cache_size';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if PHP realpath_cache_size is adequate';

	/**
	 * Check PHP realpath_cache_size setting.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Get realpath_cache_size setting.
		$realpath_cache_size = ini_get( 'realpath_cache_size' );

		// Default is 32K.
		$default_size = 32768;

		// Convert to bytes if needed.
		$cache_size_bytes = (int) $realpath_cache_size;

		if ( strpos( (string) $realpath_cache_size, 'K' ) !== false ) {
			$cache_size_bytes = (int) str_replace( 'K', '', $realpath_cache_size ) * 1024;
		} elseif ( strpos( (string) $realpath_cache_size, 'M' ) !== false ) {
			$cache_size_bytes = (int) str_replace( 'M', '', $realpath_cache_size ) * 1024 * 1024;
		}

		// Warn if at default (not intentionally increased).
		if ( $cache_size_bytes === $default_size ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'PHP realpath_cache_size is at the default value of 32K. For WordPress sites with many files/plugins, consider increasing this to 256K or 512K to improve performance by reducing filesystem calls.', 'wpshadow' ),
				'severity'    => 'info',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/php_realpath_cache_size',
				'meta'        => array(
					'realpath_cache_size'  => $realpath_cache_size,
					'is_default'           => true,
				),
			);
		}

		// Warn if very low (below 16K).
		if ( $cache_size_bytes < 16 * 1024 ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: Current realpath_cache_size */
					__( 'PHP realpath_cache_size is set to %s, which is very low. This may cause frequent filesystem lookups and hurt performance. Consider increasing it to at least 256K.', 'wpshadow' ),
					$realpath_cache_size
				),
				'severity'    => 'info',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/php_realpath_cache_size',
				'meta'        => array(
					'realpath_cache_size' => $realpath_cache_size,
				),
			);
		}

		// Warn if excessively high (unlikely but possible misconfiguration).
		if ( $cache_size_bytes > 10 * 1024 * 1024 ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: Current realpath_cache_size */
					__( 'PHP realpath_cache_size is set to %s, which is very high. This is unlikely to provide additional benefit and wastes memory. A reasonable value is 256K to 1M.', 'wpshadow' ),
					$realpath_cache_size
				),
				'severity'    => 'info',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/php_realpath_cache_size',
				'meta'        => array(
					'realpath_cache_size' => $realpath_cache_size,
				),
			);
		}

		// PHP realpath_cache_size is properly configured.
		return null;
	}
}
