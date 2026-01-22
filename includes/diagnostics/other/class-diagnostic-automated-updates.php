<?php
declare(strict_types=1);
/**
 * Automated Security Updates Diagnostic
 *
 * Philosophy: Security best practice - ensure timely patches
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if automatic security updates are enabled.
 */
class Diagnostic_Automated_Updates extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Check if automatic updates are disabled
		if ( defined( 'AUTOMATIC_UPDATER_DISABLED' ) && AUTOMATIC_UPDATER_DISABLED ) {
			return array(
				'id'            => 'automated-updates',
				'title'         => 'Automatic Security Updates Disabled',
				'description'   => 'Automatic security updates are disabled. Enable them to ensure critical patches are applied promptly.',
				'severity'      => 'high',
				'category'      => 'security',
				'kb_link'       => 'https://wpshadow.com/kb/enable-automatic-security-updates/',
				'training_link' => 'https://wpshadow.com/training/automated-updates/',
				'auto_fixable'  => false,
				'threat_level'  => 75,
			);
		}

		return null;
	}
}
