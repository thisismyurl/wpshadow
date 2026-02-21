<?php
/**
 * HTTP/2 Protocol Support Treatment
 *
 * Issue #4967: HTTP/2 Not Enabled (Slower Loading)
 * Pillar: ⚙️ Murphy's Law
 *
 * Checks if server uses HTTP/2.
 * HTTP/1.1 is slower for modern multi-asset pages.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6050.0000
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_HTTP2_Protocol_Support Class
 *
 * @since 1.6050.0000
 */
class Treatment_HTTP2_Protocol_Support extends Treatment_Base {

	protected static $slug = 'http2-protocol-support';
	protected static $title = 'HTTP/2 Not Enabled (Slower Loading)';
	protected static $description = 'Checks if server supports HTTP/2 protocol';
	protected static $family = 'performance';

	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_HTTP2_Protocol_Support' );
	}
}
