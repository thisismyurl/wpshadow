<?php
/**
 * No HTTP/2 Server Push Diagnostic
 *
 * Detects when HTTP/2 server push is not configured,
 * wasting opportunity to preload critical resources.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Performance
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No HTTP/2 Server Push
 *
 * Checks whether HTTP/2 server push is configured
 * for preloading critical resources.
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_HTTP_2_Server_Push extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-http2-server-push';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'HTTP/2 Server Push';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether HTTP/2 push is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Whether this diagnostic is auto-fixable
	 *
	 * @var bool
	 */
	protected static $auto_fixable = false;

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check server HTTP/2 support
		$homepage = wp_remote_head( home_url() );
		if ( is_wp_error( $homepage ) ) {
			return null;
		}

		$headers = wp_remote_retrieve_headers( $homepage );
		
		// Check for HTTP/2
		$http_version = isset( $headers['http_version'] ) ? $headers['http_version'] : '';
		$is_http2 = strpos( $http_version, '2' ) !== false;

		if ( ! $is_http2 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'Server doesn\'t support HTTP/2, which limits performance optimization. HTTP/2 allows server push: preload critical resources (CSS, fonts, JS) without waiting for browser request. This is especially powerful for fonts and critical CSS. Modern servers support HTTP/2, but older hosting may not. Check with hosting provider about HTTP/2 support and server push configuration.',
					'wpshadow'
				),
				'severity'      => 'medium',
				'threat_level'  => 45,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Resource Preloading Efficiency',
					'potential_gain' => 'Faster critical resource delivery',
					'roi_explanation' => 'HTTP/2 server push preloads critical resources, reducing time to meaningful paint.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/http2-server-push',
			);
		}

		return null;
	}
}
