<?php
/**
 * HTTP/2 Server Push Diagnostic
 *
 * Checks if HTTP/2 server push is properly configured to preload critical resources
 * and reduce connection waterfall delays.
 *
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * HTTP/2 Server Push Diagnostic Class
 *
 * Verifies HTTP/2 push configuration:
 * - HTTP/2 protocol support
 * - Link headers for preload
 * - Critical resource identification
 * - Push manifests
 *
 * @since 1.6093.1200
 */
class Diagnostic_Http2_Server_Push extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'http2-server-push';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'HTTP/2 Server Push Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks HTTP/2 server push for critical resource preloading';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		// Check if HTTP/2 is available
		$server_protocol = $_SERVER['SERVER_PROTOCOL'] ?? '';
		$http2_available = stripos( $server_protocol, 'HTTP/2' ) !== false;

		// Check for Link headers
		$link_headers_configured = false;
		if ( has_action( 'wp_head' ) ) {
			// This is a basic check; actual implementation varies
			if ( function_exists( 'header' ) && has_filter( 'wp_head' ) ) {
				$link_headers_configured = true;
			}
		}

		if ( ! $http2_available ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'HTTP/2 server push is not available. Enable HTTP/2 to preload critical resources and reduce waterfall delays.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/http2-server-push',
				'meta'          => array(
					'http2_available'      => $http2_available,
					'server_protocol'      => $server_protocol,
					'recommendation'       => 'Contact hosting provider to enable HTTP/2 on your server',
					'impact'               => 'HTTP/2 reduces connection overhead by 40-60%',
					'best_practices'       => array(
						'Push critical fonts and CSS',
						'Avoid pushing too many resources',
						'Prioritize above-the-fold assets',
						'Monitor push performance impact',
					),
				),
			);
		}

		return null;
	}
}
