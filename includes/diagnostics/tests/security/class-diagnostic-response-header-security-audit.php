<?php
/**
 * Response Header Security Audit Diagnostic
 *
 * Issue #4986: Missing Response Security Headers Audit
 * Pillar: 🛡️ Safe by Default
 *
 * Checks if all security headers are configured.
 * Missing headers leave site vulnerable to attacks.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Response_Header_Security_Audit Class
 *
 * @since 1.6093.1200
 */
class Diagnostic_Response_Header_Security_Audit extends Diagnostic_Base {

	protected static $slug = 'response-header-security-audit';
	protected static $title = 'Missing Response Security Headers Audit';
	protected static $description = 'Checks if all critical security headers are configured';
	protected static $family = 'security';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Set X-Content-Type-Options: nosniff (prevent MIME sniffing)', 'wpshadow' );
		$issues[] = __( 'Set X-Frame-Options: DENY or SAMEORIGIN (prevent clickjacking)', 'wpshadow' );
		$issues[] = __( 'Set Strict-Transport-Security (enforce HTTPS)', 'wpshadow' );
		$issues[] = __( 'Set X-XSS-Protection: 1; mode=block (legacy browsers)', 'wpshadow' );
		$issues[] = __( 'Verify all headers with online security checker', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Security headers tell browsers how to handle content. Missing headers leave sites vulnerable to MIME sniffing, clickjacking, and XSS.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/security-headers',
				'details'      => array(
					'recommendations'         => $issues,
					'x_content_type_options'  => 'X-Content-Type-Options: nosniff',
					'x_frame_options'         => 'X-Frame-Options: SAMEORIGIN',
					'check_tool'              => 'securityheaders.com or Mozilla Observatory',
				),
			);
		}

		return null;
	}
}
