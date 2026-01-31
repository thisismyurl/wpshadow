<?php
/**
 * HTTP Caching Headers Not Configured Diagnostic
 *
 * Checks if HTTP caching is configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * HTTP Caching Headers Not Configured Diagnostic Class
 *
 * Detects missing HTTP caching headers.
 *
 * @since 1.2601.2352
 */
class Diagnostic_HTTP_Caching_Headers_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'http-caching-headers-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'HTTP Caching Headers Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if HTTP caching is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if cache headers are set
		if ( ! has_action( 'wp_headers', 'set_cache_headers' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'HTTP caching headers are not configured. Set Cache-Control and ETag headers to enable browser and CDN caching.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 35,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/http-caching-headers-not-configured',
			);
		}

		return null;
	}
}
