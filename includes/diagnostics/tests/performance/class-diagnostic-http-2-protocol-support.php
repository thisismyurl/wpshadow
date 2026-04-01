<?php
/**
 * HTTP/2 Protocol Support Diagnostic
 *
 * Detects whether the web server is configured to use HTTP/2 protocol.
 * HTTP/2 provides significant performance improvements over HTTP/1.1.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Performance
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( __NAMESPACE__ . '\\Diagnostic_HTTP2_Protocol_Support' ) ) {
	return;
}

/**
 * HTTP/2 Protocol Support Diagnostic Class
 *
 * Checks whether the server supports HTTP/2 protocol, which offers
 * multiplexing, header compression, and server push for better performance.
 *
 * **Why This Matters:**
 * - 30-50% faster page loads vs HTTP/1.1
 * - Multiplexing eliminates head-of-line blocking
 * - Header compression reduces overhead
 * - Server push enables proactive resource delivery
 * - Required for modern web performance
 *
 * **Benefits:**
 * - Faster resource loading (CSS, JS, images)
 * - Better mobile performance
 * - Improved Core Web Vitals scores
 * - Free upgrade (no code changes needed)
 *
 * @since 0.6093.1200
 */
class Diagnostic_HTTP2_Protocol_Support extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'http-2-protocol-support';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'HTTP/2 Protocol Support';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether the web server is configured to use HTTP/2 protocol';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if HTTP/2 not detected, null if enabled.
	 */
	public static function check() {
		// Check $_SERVER['SERVER_PROTOCOL']
		$protocol = isset( $_SERVER['SERVER_PROTOCOL'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_PROTOCOL'] ) ) : '';

		// Check if HTTP/2 is in use
		if ( strpos( $protocol, 'HTTP/2' ) !== false ) {
			return null; // HTTP/2 is enabled
		}

		// Check if HTTPS is enabled (HTTP/2 requires HTTPS)
		$is_https = is_ssl();

		if ( ! $is_https ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Site is not using HTTPS. HTTP/2 requires HTTPS and provides 30-50% faster page loads.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/performance-http2-protocol?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'protocol'     => $protocol,
					'https'        => false,
					'http2'        => false,
					'requirement'  => 'HTTPS is required for HTTP/2',
					'action'       => 'Enable SSL certificate first',
				),
			);
		}

		// HTTPS enabled but not HTTP/2
		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Server is not configured for HTTP/2. Enabling HTTP/2 provides 30-50% faster page loads at no cost.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 50,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/performance-http2-protocol?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'protocol'     => $protocol,
				'https'        => true,
				'http2'        => false,
				'action'       => 'Contact hosting provider to enable HTTP/2',
				'benefit'      => '30-50% faster page loads',
			),
		);
	}
}
