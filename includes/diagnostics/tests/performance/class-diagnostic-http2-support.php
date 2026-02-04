<?php
/**
 * HTTP/2 Protocol Support Diagnostic
 *
 * Verifies server supports HTTP/2 protocol for improved performance.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6033.2051
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * HTTP/2 Protocol Support Diagnostic Class
 *
 * Checks if server supports HTTP/2 protocol. HTTP/2 significantly
 * improves performance through multiplexing and header compression.
 *
 * @since 1.6033.2051
 */
class Diagnostic_HTTP2_Support extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'http2-support';

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
	protected static $description = 'Checks if server supports HTTP/2 protocol';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks SERVER_PROTOCOL for HTTP/2 support.
	 * HTTP/2 can reduce load time by 15-50%.
	 *
	 * @since  1.6033.2051
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check SERVER_PROTOCOL
		$protocol = isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : '';
		
		// Check if HTTP/2 is being used
		if ( strpos( $protocol, 'HTTP/2' ) !== false ) {
			return null; // HTTP/2 is enabled
		}
		
		// Check if HTTPS is enabled (required for HTTP/2)
		$is_ssl = is_ssl();
		
		if ( ! $is_ssl ) {
			return array(
				'id'           => 'http2-requires-ssl',
				'title'        => __( 'HTTP/2 Requires HTTPS', 'wpshadow' ),
				'description'  => __( 'HTTP/2 requires HTTPS to be enabled. Enable SSL/TLS first, then configure HTTP/2 support on your web server. HTTP/2 can improve performance by 15-50% through multiplexing and header compression.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/enable-https',
				'meta'         => array(
					'current_protocol' => $protocol,
					'https_enabled'    => false,
					'http2_enabled'    => false,
					'recommendation'   => 'Enable HTTPS first',
				),
			);
		}
		
		// HTTPS is enabled but HTTP/2 is not
		return array(
			'id'           => 'http2-not-enabled',
			'title'        => __( 'HTTP/2 Not Enabled', 'wpshadow' ),
			'description'  => __( 'Your server is using HTTP/1.1. Enabling HTTP/2 can improve performance by 15-50% through request multiplexing, header compression, and server push. Most modern web servers support HTTP/2.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 45,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/enable-http2',
			'meta'         => array(
				'current_protocol'   => $protocol,
				'https_enabled'      => true,
				'http2_enabled'      => false,
				'server_software'    => isset( $_SERVER['SERVER_SOFTWARE'] ) ? $_SERVER['SERVER_SOFTWARE'] : 'unknown',
				'performance_impact' => '15-50% improvement possible',
			),
		);
	}
}
