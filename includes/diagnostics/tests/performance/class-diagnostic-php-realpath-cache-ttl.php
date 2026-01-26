<?php
/**
 * Diagnostic: PHP realpath_cache_ttl
 *
 * Checks if PHP realpath_cache_ttl is properly configured.
 * Time-to-live for cached file paths; higher values improve performance.
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
 * Class Diagnostic_Php_Realpath_Cache_Ttl
 *
 * Tests PHP realpath_cache_ttl configuration.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Php_Realpath_Cache_Ttl extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'php-realpath-cache-ttl';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'PHP realpath_cache_ttl';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if PHP realpath_cache_ttl is properly configured';

	/**
	 * Check PHP realpath_cache_ttl setting.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Get realpath_cache_ttl setting.
		$realpath_cache_ttl = ini_get( 'realpath_cache_ttl' );

		// Convert to integer (in seconds).
		$cache_ttl = (int) $realpath_cache_ttl;

		// Default is 120 seconds (2 minutes).
		$default_ttl = 120;

		// Warn if at default (conservative but not optimal).
		if ( $cache_ttl === $default_ttl ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'PHP realpath_cache_ttl is at the default value of 120 seconds. For stable WordPress installations, consider increasing this to 600+ seconds to improve cache hit rates and performance.', 'wpshadow' ),
				'severity'    => 'info',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/php_realpath_cache_ttl',
				'meta'        => array(
					'realpath_cache_ttl' => $cache_ttl,
					'is_default'         => true,
				),
			);
		}

		// Warn if very low (below 60 seconds).
		if ( $cache_ttl < 60 && $cache_ttl > 0 ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: Current TTL in seconds */
					__( 'PHP realpath_cache_ttl is set to %d seconds, which is very short. This reduces cache effectiveness. Consider setting it to at least 300-600 seconds.', 'wpshadow' ),
					$cache_ttl
				),
				'severity'    => 'info',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/php_realpath_cache_ttl',
				'meta'        => array(
					'realpath_cache_ttl' => $cache_ttl,
				),
			);
		}

		// Warn if 0 (cache disabled).
		if ( 0 === $cache_ttl ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'PHP realpath_cache_ttl is set to 0 (cache disabled). This disables realpath caching, which can hurt performance. Set this to at least 300-600 seconds.', 'wpshadow' ),
				'severity'    => 'info',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/php_realpath_cache_ttl',
				'meta'        => array(
					'realpath_cache_ttl' => 0,
					'cache_disabled'     => true,
				),
			);
		}

		// Warn if excessively high (unusual but acceptable).
		if ( $cache_ttl > 86400 ) { // More than 24 hours.
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: Current TTL in seconds */
					__( 'PHP realpath_cache_ttl is set to %d seconds (over 24 hours). While this maximizes cache hits, it may prevent WordPress from detecting newly added files if they\'re added during the same request. A value of 300-3600 seconds is usually optimal.', 'wpshadow' ),
					$cache_ttl
				),
				'severity'    => 'info',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/php_realpath_cache_ttl',
				'meta'        => array(
					'realpath_cache_ttl' => $cache_ttl,
				),
			);
		}

		// PHP realpath_cache_ttl is properly configured.
		return null;
	}
}
