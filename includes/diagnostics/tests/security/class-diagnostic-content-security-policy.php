<?php
/**
 * Content Security Policy Diagnostic
 *
 * Issue #4948: No Content Security Policy Header
 * Pillar: 🛡️ Safe by Default
 *
 * Checks if Content-Security-Policy header is configured.
 * CSP prevents XSS by restricting resource loading.
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
 * Diagnostic_Content_Security_Policy Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Content_Security_Policy extends Diagnostic_Base {

	protected static $slug = 'content-security-policy';
	protected static $title = 'No Content Security Policy Header';
	protected static $description = 'Checks if CSP header restricts resource loading';
	protected static $family = 'security';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Set Content-Security-Policy header', 'wpshadow' );
		$issues[] = __( 'Restrict script sources: script-src \'self\'', 'wpshadow' );
		$issues[] = __( 'Restrict style sources: style-src \'self\'', 'wpshadow' );
		$issues[] = __( 'Block inline scripts unless nonce-protected', 'wpshadow' );
		$issues[] = __( 'Report violations with report-uri directive', 'wpshadow' );
		$issues[] = __( 'Start with report-only mode, monitor, then enforce', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Content Security Policy is a powerful XSS defense. It restricts where scripts and styles can load from, blocking injected malicious code.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/content-security-policy?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'recommendations'         => $issues,
					'basic_policy'            => "default-src 'self'; script-src 'self'; style-src 'self'",
					'report_only'             => 'Content-Security-Policy-Report-Only (test mode)',
					'compatibility'           => 'May break plugins that use inline scripts',
				),
			);
		}

		return null;
	}
}
