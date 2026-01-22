<?php declare(strict_types=1);
/**
 * WordPress Nonce Expiration Diagnostic
 *
 * Philosophy: Session security - reasonable nonce lifetime
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check WordPress nonce expiration time.
 */
class Diagnostic_Nonce_Expiration {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		// Check nonce lifetime (default is 1 day)
		$nonce_life = apply_filters( 'nonce_life', DAY_IN_SECONDS );
		
		// If nonce lifetime is longer than 12 hours
		if ( $nonce_life > ( 12 * HOUR_IN_SECONDS ) ) {
			return array(
				'id'          => 'nonce-expiration',
				'title'       => 'Long Nonce Expiration Time',
				'description' => sprintf(
					'WordPress security nonces remain valid for %s. Long-lived nonces increase CSRF attack window. Consider reducing nonce lifetime to 8-12 hours for sensitive operations.',
					human_time_diff( 0, $nonce_life )
				),
				'severity'    => 'medium',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/configure-nonce-lifetime/',
				'training_link' => 'https://wpshadow.com/training/nonce-security/',
				'auto_fixable' => false,
				'threat_level' => 60,
			);
		}
		
		return null;
	}
}
