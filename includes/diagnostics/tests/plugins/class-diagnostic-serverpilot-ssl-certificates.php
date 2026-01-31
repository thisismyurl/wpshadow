<?php
/**
 * Serverpilot Ssl Certificates Diagnostic
 *
 * Serverpilot Ssl Certificates needs attention.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1031.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Serverpilot Ssl Certificates Diagnostic Class
 *
 * @since 1.1031.0000
 */
class Diagnostic_ServerpilotSslCertificates extends Diagnostic_Base {

	protected static $slug = 'serverpilot-ssl-certificates';
	protected static $title = 'Serverpilot Ssl Certificates';
	protected static $description = 'Serverpilot Ssl Certificates needs attention';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'SERVERPILOT_VERSION' ) && ! get_option( 'serverpilot_app_id' ) ) {
			return null;
		}
		
		$issues = array();

		// Check 1: Verify SSL certificate is active
		$ssl_enabled = get_option( 'serverpilot_ssl_enabled', false );
		if ( ! $ssl_enabled ) {
			$issues[] = __( 'SSL certificate not enabled', 'wpshadow' );
		}

		// Check 2: Check SSL certificate expiration
		$ssl_expiry = get_option( 'serverpilot_ssl_expiry_date', 0 );
		if ( $ssl_expiry > 0 && $ssl_expiry < ( time() + ( 30 * DAY_IN_SECONDS ) ) ) {
			$issues[] = __( 'SSL certificate expires within 30 days', 'wpshadow' );
		}

		// Check 3: Verify auto-renewal is configured
		$auto_renew = get_option( 'serverpilot_ssl_auto_renew', false );
		if ( ! $auto_renew ) {
			$issues[] = __( 'SSL certificate auto-renewal not configured', 'wpshadow' );
		}

		// Check 4: Check wildcard certificate support
		$wildcard_support = get_option( 'serverpilot_ssl_wildcard', false );
		if ( ! $wildcard_support && is_multisite() ) {
			$issues[] = __( 'Wildcard SSL not configured for multisite', 'wpshadow' );
		}

		// Check 5: Verify HTTPS enforcement
		if ( ! is_ssl() ) {
			$issues[] = __( 'HTTPS enforcement not active', 'wpshadow' );
		}

		// Check 6: Check SSL certificate validation
		$cert_validation = get_transient( 'serverpilot_ssl_validation' );
		if ( false === $cert_validation ) {
			$issues[] = __( 'SSL certificate validation status not cached', 'wpshadow' );
		}
		return null;
	}
}
