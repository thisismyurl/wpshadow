<?php
/**
 * Referrer Policy Configuration Diagnostic
 *
 * Issue #4950: No Referrer-Policy Header Set
 * Pillar: 🛡️ Safe by Default / #10: Beyond Pure (Privacy)
 *
 * Checks if Referrer-Policy header is configured.
 * Referrer leaks URLs containing sensitive information.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6050.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Referrer_Policy_Configuration Class
 *
 * @since 1.6050.0000
 */
class Diagnostic_Referrer_Policy_Configuration extends Diagnostic_Base {

	protected static $slug = 'referrer-policy-configuration';
	protected static $title = 'No Referrer-Policy Header Set';
	protected static $description = 'Checks if Referrer-Policy protects URL privacy';
	protected static $family = 'security';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Set Referrer-Policy header to control URL leakage', 'wpshadow' );
		$issues[] = __( 'Use "strict-origin-when-cross-origin" for balance', 'wpshadow' );
		$issues[] = __( 'Use "no-referrer" for maximum privacy', 'wpshadow' );
		$issues[] = __( 'Never use "unsafe-url" (leaks full URL)', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'The Referrer header can leak sensitive URL information (passwords, tokens, search terms) to external sites. Configure Referrer-Policy to protect user privacy.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/referrer-policy',
				'details'      => array(
					'recommendations'         => $issues,
					'privacy_leak_example'    => 'yoursite.com/reset-password?token=abc123 leaked to external site',
					'recommended_policy'      => 'Referrer-Policy: strict-origin-when-cross-origin',
					'privacy_policy'          => 'Referrer-Policy: no-referrer (maximum privacy)',
					'commandment'             => 'Commandment #10: Beyond Pure (Privacy First)',
				),
			);
		}

		return null;
	}
}
