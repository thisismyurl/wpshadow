<?php
/**
 * Diagnostic: Reverse Proxy IP Header
 *
 * Checks if reverse proxy IP headers are present and warns to configure trusted proxies.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Configuration
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Reverse_Proxy_Ip_Header
 *
 * Tests for reverse proxy headers indicating potential IP spoofing risks.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Reverse_Proxy_Ip_Header extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'reverse-proxy-ip-header';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Reverse Proxy IP Header';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks reverse proxy headers and trusted proxy configuration';

	/**
	 * Check reverse proxy headers.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$forwarded = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
		$real_ip   = $_SERVER['HTTP_X_REAL_IP'] ?? '';

		if ( empty( $forwarded ) && empty( $real_ip ) ) {
			return null; // No proxy headers present.
		}

		return array(
			'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Reverse proxy headers detected. Ensure WordPress is configured with trusted proxies (e.g., via server config) to prevent IP spoofing and to log real client IPs.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/reverse_proxy_ip_header',
				'meta'        => array(
					'x_forwarded_for' => $forwarded,
					'x_real_ip'       => $real_ip,
				),
		);
	}
}
