<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: CAPTCHA Performance and Abandonment (SEC-PERF-006)
 *
 * CAPTCHA Performance and Abandonment diagnostic
 * Philosophy: Show value (#9) - Balance security.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_DiagnosticCaptchaPerformance extends Diagnostic_Base {
	public static function check(): ?array {
		$captcha_abandon_rate = (float) get_transient( 'wpshadow_captcha_abandon_rate' );
		$captcha_latency_ms   = (int) get_transient( 'wpshadow_captcha_latency_ms' );

		if ( $captcha_abandon_rate > 5 || $captcha_latency_ms > 800 ) {
			return array(
				'id'            => 'captcha-performance',
				'title'         => __( 'CAPTCHA slowing conversions', 'wpshadow' ),
				'description'   => __( 'CAPTCHA is adding latency or causing abandonment. Consider invisible challenges, hCaptcha/Turnstile, or server-side verification.', 'wpshadow' ),
				'severity'      => 'medium',
				'category'      => 'other',
				'kb_link'       => 'https://wpshadow.com/kb/captcha-performance/',
				'training_link' => 'https://wpshadow.com/training/form-optimization/',
				'auto_fixable'  => false,
				'threat_level'  => 45,
				'abandon_rate'  => $captcha_abandon_rate,
				'latency_ms'    => $captcha_latency_ms,
			);
		}

		return null;
	}
}
