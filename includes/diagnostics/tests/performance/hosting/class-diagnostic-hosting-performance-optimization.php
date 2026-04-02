<?php
/**
 * Hosting Performance Optimization Diagnostic
 *
 * Checks if hosting is optimized for performance.
 *
 * @package WPShadow\Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Hosting Performance Optimization
 *
 * Detects hosting-level performance optimization opportunities.
 */
class Diagnostic_Hosting_Performance_Optimization extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'hosting-performance-optimization';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Hosting Performance Optimization';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for hosting-level performance optimizations';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'hosting';

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Finding array if issues detected, null otherwise
	 */
	public static function check() {
		$issues = array();
		$stats  = array();

		// Check for common hosting environments
		$server_software = $_SERVER['SERVER_SOFTWARE'] ?? '';
		$stats['server_software'] = $server_software;

		// Check PHP version
		$php_version = phpversion();
		$stats['php_version'] = $php_version;
		if ( version_compare( $php_version, '8.0', '<' ) ) {
			$issues[] = sprintf( __( 'PHP version %s is outdated (current: %s)', 'wpshadow' ), '8.0+', $php_version );
		}

		// Check for object cache
		$has_object_cache = wp_using_ext_object_cache();
		$stats['object_cache_enabled'] = $has_object_cache;
		if ( ! $has_object_cache ) {
			$issues[] = __( 'Object cache not enabled', 'wpshadow' );
		}

		// Check for GZIP compression
		$gzip_enabled = false;
		if ( function_exists( 'gzip_compression_enabled' ) ) {
			$gzip_enabled = gzip_compression_enabled();
		} elseif ( ini_get( 'zlib.output_compression' ) ) {
			$gzip_enabled = true;
		}
		$stats['gzip_compression'] = $gzip_enabled;

		// Check for HTTP/2
		$http_version = $_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.1';
		$stats['http_version'] = $http_version;

		if ( ! empty( $issues ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Hosting optimization includes modern PHP versions, object caching, compression, and HTTP/2 support. These fundamental improvements significantly boost site speed and user experience, directly impacting SEO and conversions.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 65,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/hosting-performance',
				'context'       => array(
					'stats'  => $stats,
					'issues' => $issues,
				),
			);
		}

		return null;
	}
}
