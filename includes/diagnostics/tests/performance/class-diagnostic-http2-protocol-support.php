<?php
/**
 * HTTP/2 Protocol Support Diagnostic
 *
 * Issue #4967: HTTP/2 Not Enabled (Slower Loading)
 * Pillar: ⚙️ Murphy's Law
 *
 * Checks if server uses HTTP/2.
 * HTTP/1.1 is slower for modern multi-asset pages.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6050.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_HTTP2_Protocol_Support Class
 *
 * @since 1.6050.0000
 */
class Diagnostic_HTTP2_Protocol_Support extends Diagnostic_Base {

	protected static $slug = 'http2-protocol-support';
	protected static $title = 'HTTP/2 Not Enabled (Slower Loading)';
	protected static $description = 'Checks if server supports HTTP/2 protocol';
	protected static $family = 'performance';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Enable HTTP/2 in server configuration', 'wpshadow' );
		$issues[] = __( 'HTTP/2 requires HTTPS (SSL certificate)', 'wpshadow' );
		$issues[] = __( 'Multiplexing: Load multiple files over one connection', 'wpshadow' );
		$issues[] = __( 'Server push: Send resources before browser requests', 'wpshadow' );
		$issues[] = __( 'Apache: mod_http2, Nginx: built-in support', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'HTTP/2 loads multiple files simultaneously over one connection. HTTP/1.1 loads files one at a time, slowing page load.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/http2',
				'details'      => array(
					'recommendations'         => $issues,
					'speed_improvement'       => '20-50% faster page loads',
					'requires_https'          => 'SSL certificate required',
					'browser_support'         => '97%+ browser support',
				),
			);
		}

		return null;
	}
}
