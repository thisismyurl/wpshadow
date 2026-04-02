<?php
/**
 * Treatment: HTTPS Enabled
 *
 * Provides guidance for enabling HTTPS on the WordPress site.
 * HTTPS cannot be programmatically enabled by a plugin — it must be
 * configured at the hosting/server level. This treatment returns
 * step-by-step instructions tailored to the most common hosting environments.
 *
 * Risk level: n/a (guidance only)
 *
 * @package WPShadow
 * @subpackage Treatments
 * @since 0.6093.1300
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns hosting-level instructions for enabling HTTPS on this site.
 */
class Treatment_Https_Enabled extends Treatment_Base {

	/** @var string */
	protected static $slug = 'https-enabled';

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
	 * Return step-by-step HTTPS setup guidance.
	 *
	 * @return array{success:bool, message:string}
	 */
	public static function apply(): array {
		return [
			'success' => false,
			'message' => __(
				"HTTPS must be enabled at the server/hosting level — this cannot be changed by a plugin.\n\n"
				. "OPTION 1 — cPanel / Shared Hosting (most common):\n"
				. "  1. Log in to your hosting control panel (cPanel, Plesk, etc.).\n"
				. "  2. Look for 'SSL/TLS', 'Let's Encrypt', or 'AutoSSL'.\n"
				. "  3. Select your domain and issue a free Let's Encrypt certificate.\n"
				. "  4. Wait 1–5 minutes for provisioning, then visit https://yoursite.com to verify.\n"
				. "  5. In WordPress: go to Settings → General and update Site URL and WordPress Address to https://.\n"
				. "  6. Install the 'Really Simple SSL' plugin (free) to automatically redirect HTTP → HTTPS.\n\n"
				. "OPTION 2 — Cloudflare (DNS proxy):\n"
				. "  1. Add your site to Cloudflare (free plan supports SSL).\n"
				. "  2. Enable 'Full (strict)' SSL mode under SSL/TLS in Cloudflare dashboard.\n"
				. "  3. Cloudflare handles HTTPS termination; your server needs a valid certificate too.\n\n"
				. "OPTION 3 — VPS / Dedicated (Certbot / Let's Encrypt):\n"
				. "  1. SSH into your server.\n"
				. "  2. Install certbot: sudo apt-get install certbot python3-certbot-apache (or nginx).\n"
				. "  3. Run: sudo certbot --apache (or --nginx) -d yourdomain.com -d www.yourdomain.com\n"
				. "  4. Follow prompts; certbot configures and auto-renews the certificate.\n\n"
				. "After enabling HTTPS, re-run the WPShadow scan to confirm this diagnostic is resolved.",
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
