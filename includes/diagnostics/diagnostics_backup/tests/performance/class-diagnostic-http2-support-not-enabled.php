<?php
/**
 * HTTP/2 Support Not Enabled Diagnostic
 *
 * Checks if HTTP/2 is supported on server.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2310
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * HTTP/2 Support Not Enabled Diagnostic Class
 *
 * Detects missing HTTP/2 support.
 *
 * @since 1.2601.2310
 */
class Diagnostic_HTTP2_Support_Not_Enabled extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'http2-support-not-enabled';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'HTTP/2 Support Not Enabled';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if HTTP/2 is supported';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if server supports HTTP/2
		// This is typically indicated by server configuration
		if ( ! function_exists( 'curl_version' ) ) {
			return null; // Cannot check
		}

		$curl_info = curl_version();

		if ( ! isset( $curl_info['protocols'] ) ||
			 ( strpos( $curl_info['protocols'], 'h2' ) === false && strpos( $curl_info['protocols'], 'HTTP/2' ) === false ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'HTTP/2 is not enabled. Enabling HTTP/2 can significantly improve page load times by multiplexing requests.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/http2-support-not-enabled',
			);
		}

		return null;
	}
}
