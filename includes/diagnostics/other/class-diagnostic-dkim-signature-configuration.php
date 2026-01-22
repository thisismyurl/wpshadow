<?php
declare(strict_types=1);
/**
 * DKIM Signature Configuration Diagnostic
 *
 * Philosophy: Email security - authenticate emails
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if DKIM is configured.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_DKIM_Signature_Configuration extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$dkim_configured = get_option( 'wpshadow_dkim_configured' );

		if ( empty( $dkim_configured ) ) {
			return array(
				'id'            => 'dkim-signature-configuration',
				'title'         => 'No DKIM Signature Configuration',
				'description'   => 'DKIM (DomainKeys Identified Mail) not configured. Emails not cryptographically signed. Implement DKIM to prevent email spoofing and improve deliverability.',
				'severity'      => 'medium',
				'category'      => 'security',
				'kb_link'       => 'https://wpshadow.com/kb/configure-dkim/',
				'training_link' => 'https://wpshadow.com/training/dkim-setup/',
				'auto_fixable'  => false,
				'threat_level'  => 60,
			);
		}

		return null;
	}
}
