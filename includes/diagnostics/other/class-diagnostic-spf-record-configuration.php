<?php
declare(strict_types=1);
/**
 * SPF Record Configuration Diagnostic
 *
 * Philosophy: Email security - prevent spoofing
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if SPF record is configured.
 */
class Diagnostic_SPF_Record_Configuration extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$spf_configured = get_option( 'wpshadow_spf_record_configured' );

		if ( empty( $spf_configured ) ) {
			return array(
				'id'            => 'spf-record-configuration',
				'title'         => 'No SPF Record Configured',
				'description'   => 'SPF (Sender Policy Framework) not configured. Site emails can be spoofed, damaging reputation. Configure SPF record to authorize only legitimate mail servers.',
				'severity'      => 'medium',
				'category'      => 'security',
				'kb_link'       => 'https://wpshadow.com/kb/configure-spf-record/',
				'training_link' => 'https://wpshadow.com/training/email-authentication/',
				'auto_fixable'  => false,
				'threat_level'  => 65,
			);
		}

		return null;
	}
}
