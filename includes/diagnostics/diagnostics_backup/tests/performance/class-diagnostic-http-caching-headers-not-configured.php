<?php
/**
 * HTTP Caching Headers Not Configured Diagnostic
 *
 * Checks if HTTP caching headers are configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2349
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
 * @since 1.2601.2349
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
	protected static $description = 'Checks if HTTP caching headers are configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2349
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if cache control header filter exists
		if ( ! has_action( 'send_headers', 'wp_cache_control_headers' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'HTTP caching headers are not configured. Add Cache-Control and ETag headers to improve client-side caching.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 35,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/http-caching-headers-not-configured',
			);
		}

		return null;
	}
}
