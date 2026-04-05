<?php
/**
 * Treatment: HTTP/2 or HTTP/3 Enabled
 *
 * Provides guidance for enabling HTTP/2 or HTTP/3 on the web server.
 * Protocol upgrades must be configured at the server or hosting level.
 * They cannot be toggled from within a WordPress plugin.
 *
 * Risk level: n/a (guidance only)
 *
 * @package WPShadow
 * @subpackage Treatments
 * @since 0.6095
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns server-level instructions for enabling HTTP/2 or HTTP/3.
 */
class Treatment_Http2_Or_Http3_Enabled extends Treatment_Base {

	/** @var string */
	protected static $slug = 'http2-or-http3-enabled';

	// =========================================================================
	// Treatment_Base contract
	// =========================================================================

	public static function get_finding_id(): string {
		return self::$slug;
	}

	public static function get_risk_level(): string {
		return 'none';
	}

	/**
	 * Return HTTP/2 and HTTP/3 enablement guidance.
	 *
	 * @return array{success:bool, message:string}
	 */
	public static function apply(): array {
		return [
			'success' => false,
			'message' => __(
				"HTTP/2 and HTTP/3 must be enabled at the web server or hosting level.\n\n"
				. "PREREQUISITES:\n"
				. "  • HTTPS must be active (HTTP/2+ requires TLS/SSL).\n"
				. "  • Web server must be Apache 2.4.17+, Nginx 1.9.5+, or LiteSpeed.\n\n"
				. "OPTION 1 — cPanel / Managed Hosting:\n"
				. "  1. In cPanel, look for 'MultiPHP Manager' or 'Apache Configuration'.\n"
				. "  2. Some hosts (SiteGround, Kinsta, WP Engine) enable HTTP/2 by default.\n"
				. "  3. Contact your host's support and ask: 'Can you enable HTTP/2 for my domain?'\n\n"
				. "OPTION 2 — Apache on VPS/Dedicated:\n"
				. "  1. Enable the http2 module: sudo a2enmod http2 && sudo service apache2 restart\n"
				. "  2. Ensure your VHost uses 'Protocols h2 http/1.1'.\n"
				. "  3. Verify: curl -sI --http2 https://yoursite.com | grep 'HTTP/'\n\n"
				. "OPTION 3 — Nginx on VPS/Dedicated:\n"
				. "  1. In your server block: listen 443 ssl http2;\n"
				. "  2. Reload: sudo nginx -s reload\n"
				. "  3. Verify: curl -sI --http2 https://yoursite.com | grep 'HTTP/'\n\n"
				. "OPTION 4 — Cloudflare:\n"
				. "  1. In Cloudflare: Network → HTTP/2 → toggle On.\n"
				. "  2. Cloudflare also supports HTTP/3 (QUIC): Network → HTTP/3.\n"
				. "  3. No server configuration changes required.\n\n"
				. "After enabling, re-run the WPShadow scan to verify.",
				'wpshadow'
			),
		];
	}

	/**
	 * No state to undo (guidance only).
	 *
	 * @return array{success:bool, message:string}
	 */
	public static function undo(): array {
		return [
			'success' => true,
			'message' => __( 'This is a guidance-only treatment — no changes were made by WPShadow.', 'wpshadow' ),
		];
	}
}
