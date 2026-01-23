<?php
declare(strict_types=1);
/**
 * CAA Records Configuration Diagnostic
 *
 * Philosophy: Certificate security - authorize certificate authorities
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if CAA records are configured.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_CAA_Records_Configuration extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$caa_configured = get_option( 'wpshadow_caa_records_configured' );

		if ( empty( $caa_configured ) ) {
			return array(
				'id'            => 'caa-records-configuration',
				'title'         => 'No CAA Records Configured',
				'description'   => 'CAA (Certification Authority Authorization) DNS records not set. Attackers can request SSL certificates from rogue CAs. Configure CAA records to authorize only trusted CAs.',
				'severity'      => 'medium',
				'category'      => 'security',
				'kb_link'       => 'https://wpshadow.com/kb/configure-caa-records/',
				'training_link' => 'https://wpshadow.com/training/certificate-security/',
				'auto_fixable'  => false,
				'threat_level'  => 65,
			);
		}

		return null;
	}

}