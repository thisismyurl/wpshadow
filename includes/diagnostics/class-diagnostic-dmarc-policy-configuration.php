<?php declare(strict_types=1);
/**
 * DMARC Policy Configuration Diagnostic
 *
 * Philosophy: Email security - enforce email authentication
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check if DMARC policy is configured.
 */
class Diagnostic_DMARC_Policy_Configuration {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		$dmarc_configured = get_option( 'wpshadow_dmarc_configured' );
		
		if ( empty( $dmarc_configured ) ) {
			return array(
				'id'          => 'dmarc-policy-configuration',
				'title'       => 'No DMARC Policy Configured',
				'description' => 'DMARC (Domain-based Message Authentication) policy not set. Emails fail SPF/DKIM can be delivered. Configure DMARC policy (enforce) to reject non-compliant emails.',
				'severity'    => 'medium',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/configure-dmarc/',
				'training_link' => 'https://wpshadow.com/training/dmarc-setup/',
				'auto_fixable' => false,
				'threat_level' => 60,
			);
		}
		
		return null;
	}
}
