<?php
declare(strict_types=1);
/**
 * GDPR Consent Mechanism Diagnostic
 *
 * Philosophy: Compliance - consent before data collection
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if GDPR consent is implemented.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_GDPR_Consent_Mechanism extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$gdpr_plugins = array(
			'cookie-notice/cookie-notice.php',
			'cookie-law-info/cookie-law-info.php',
			'complianz-gdpr/complianz-gdpr.php',
		);

		$active = get_option( 'active_plugins', array() );
		foreach ( $gdpr_plugins as $plugin ) {
			if ( in_array( $plugin, $active, true ) ) {
				return null;
			}
		}

		return array(
			'id'            => 'gdpr-consent-mechanism',
			'title'         => 'No GDPR Cookie Consent Banner',
			'description'   => 'GDPR requires explicit consent before tracking cookies. Implement cookie consent banner with clear opt-in before any tracking.',
			'severity'      => 'high',
			'category'      => 'security',
			'kb_link'       => 'https://wpshadow.com/kb/implement-gdpr-consent/',
			'training_link' => 'https://wpshadow.com/training/gdpr-compliance/',
			'auto_fixable'  => false,
			'threat_level'  => 70,
		);
	}

}