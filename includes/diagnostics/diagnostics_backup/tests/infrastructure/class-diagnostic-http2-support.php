<?php
/**
 * Diagnostic: HTTP/2 Support Detection
 *
 * Detects if site is served over HTTP/2 for performance.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_HTTP2_Support
 *
 * Checks if the web server supports HTTP/2 protocol, which provides
 * significant performance improvements over HTTP/1.1.
 *
 * @since 1.2601.2148
 */
class Diagnostic_HTTP2_Support extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'http2-support';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'HTTP/2 Support Detection';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Detect if site is served over HTTP/2 for performance';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks server protocol version from $_SERVER variables.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if HTTP/1.1 only, null if HTTP/2.
	 */
	public static function check() {
		// Check server protocol
		$protocol = isset( $_SERVER['SERVER_PROTOCOL'] ) 
			? sanitize_text_field( wp_unslash( $_SERVER['SERVER_PROTOCOL'] ) )
			: '';

		// Check if HTTP/2 is detected
		$is_http2 = false !== stripos( $protocol, 'HTTP/2' );

		// Also check for h2 protocol indicator
		if ( ! $is_http2 && isset( $_SERVER['HTTP2'] ) ) {
			$is_http2 = true;
		}

		if ( $is_http2 ) {
			// HTTP/2 is enabled - this is good
			return null;
		}

		// Check if HTTPS is enabled (HTTP/2 requires HTTPS)
		$is_https = is_ssl();

		$description = __( 'Site is using HTTP/1.1 protocol. HTTP/2 provides significant performance benefits through request multiplexing, header compression, and server push capabilities. Most modern web servers support HTTP/2, which typically requires HTTPS to be enabled.', 'wpshadow' );

		if ( ! $is_https ) {
			$description .= ' ' . __( 'Note: Your site is not using HTTPS. HTTP/2 requires HTTPS (SSL/TLS) to function. Enable HTTPS first, then contact your hosting provider about HTTP/2 support.', 'wpshadow' );
		} else {
			$description .= ' ' . __( 'Your site uses HTTPS which is good. Contact your hosting provider about enabling HTTP/2 for improved performance.', 'wpshadow' );
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => $description,
			'severity'    => 'info',
			'threat_level' => 20,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/infrastructure-http2-support',
			'meta'        => array(
				'protocol' => $protocol,
				'is_http2' => false,
				'is_https' => $is_https,
			),
		);
	}
}
