<?php
/**
 * Treatment: Send security response headers via PHP
 *
 * Modern browsers honour a set of well-defined HTTP response headers that
 * restrict dangerous behaviour (MIME-type sniffing, framing attacks,
 * cross-site leakage via Referer). When a hosting environment does not emit
 * these headers at the server level, WordPress can add them through the
 * send_headers action.
 *
 * This treatment stores a flag that instructs the WPShadow bootstrap to hook
 * send_headers and emit:
 *   - X-Content-Type-Options: nosniff
 *   - X-Frame-Options: SAMEORIGIN
 *   - Referrer-Policy: strict-origin-when-cross-origin
 *   - Strict-Transport-Security: max-age=31536000 (HTTPS sites only)
 *
 * The flag approach means the headers fire on every page load (front end and
 * admin) without requiring .htaccess or nginx modification.
 *
 * Undo: removes the flag; bootstrap stops emitting the headers on next load.
 *
 * @package WPShadow
 * @since   0.6093.1900
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enables security response headers via the WordPress send_headers hook.
 */
class Treatment_Security_Headers_Present extends Treatment_Base {

	/** @var string */
	protected static $slug = 'security-headers-present';

	/** @return string */
	public static function get_risk_level(): string {
		return 'safe';
	}

	/**
	 * Store the flag so the bootstrap applies the send_headers hook.
	 *
	 * @return array
	 */
	public static function apply(): array {
		update_option( 'wpshadow_send_security_headers', true );

		$headers = array(
			'X-Content-Type-Options: nosniff',
			'X-Frame-Options: SAMEORIGIN',
			'Referrer-Policy: strict-origin-when-cross-origin',
		);

		if ( 'https' === substr( get_option( 'siteurl' ), 0, 5 ) ) {
			$headers[] = 'Strict-Transport-Security: max-age=31536000';
		}

		return array(
			'success' => true,
			'message' => sprintf(
				/* translators: %s: comma-separated list of header names */
				__( 'Security headers enabled via WordPress send_headers hook: %s. Headers will be sent on the next page load.', 'wpshadow' ),
				implode( ', ', $headers )
			),
		);
	}

	/**
	 * Remove the flag; bootstrap stops emitting the security headers.
	 *
	 * @return array
	 */
	public static function undo(): array {
		delete_option( 'wpshadow_send_security_headers' );

		return array(
			'success' => true,
			'message' => __( 'Security headers disabled. WPShadow will no longer emit security response headers.', 'wpshadow' ),
		);
	}
}
