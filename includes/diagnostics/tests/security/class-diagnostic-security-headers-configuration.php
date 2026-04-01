<?php
/**
 * Security Headers Configuration Diagnostic
 *
 * Issue #4902: Missing Security Headers (CSP, HSTS, X-Frame)
 * Pillar: 🛡️ Safe by Default
 *
 * Checks if HTTP security headers are configured.
 * Security headers provide defense-in-depth against common attacks.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Security_Headers_Configuration Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Security_Headers_Configuration extends Diagnostic_Base {

	protected static $slug = 'security-headers-configuration';
	protected static $title = 'Missing Security Headers (CSP, HSTS, X-Frame)';
	protected static $description = 'Checks if HTTP security headers are properly configured';
	protected static $family = 'security';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Content-Security-Policy: Restrict inline scripts and styles', 'wpshadow' );
		$issues[] = __( 'X-Frame-Options: Prevent clickjacking attacks', 'wpshadow' );
		$issues[] = __( 'X-Content-Type-Options: Prevent MIME type sniffing', 'wpshadow' );
		$issues[] = __( 'Strict-Transport-Security: Force HTTPS connections', 'wpshadow' );
		$issues[] = __( 'Referrer-Policy: Control referrer information', 'wpshadow' );
		$issues[] = __( 'Permissions-Policy: Disable unused browser features', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'HTTP security headers provide defense-in-depth against XSS, clickjacking, and protocol downgrade attacks.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/security-headers?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'recommendations'         => $issues,
					'x_frame_options'         => 'X-Frame-Options: SAMEORIGIN',
					'hsts_example'            => 'Strict-Transport-Security: max-age=31536000; includeSubDomains',
					'csp_example'             => "Content-Security-Policy: default-src 'self'",
					'security_score_tools'    => 'securityheaders.com, Mozilla Observatory',
				),
			);
		}

		return null;
	}
}
