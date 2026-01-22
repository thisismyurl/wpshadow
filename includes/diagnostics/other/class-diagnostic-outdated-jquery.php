<?php
declare(strict_types=1);
/**
 * Outdated jQuery Diagnostic
 *
 * Philosophy: Dependency security - check for vulnerable libraries
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for outdated jQuery with known CVEs.
 */
class Diagnostic_Outdated_jQuery extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wp_scripts;

		if ( ! isset( $wp_scripts->registered['jquery-core'] ) ) {
			return null;
		}

		$jquery  = $wp_scripts->registered['jquery-core'];
		$version = $jquery->ver;

		// Check if version is older than 3.5.0 (has known XSS vulnerabilities)
		if ( version_compare( $version, '3.5.0', '<' ) ) {
			return array(
				'id'            => 'outdated-jquery',
				'title'         => 'Outdated jQuery Version',
				'description'   => sprintf(
					'Your site uses jQuery %s which has known security vulnerabilities. Update to jQuery 3.5.0 or newer.',
					$version
				),
				'severity'      => 'medium',
				'category'      => 'security',
				'kb_link'       => 'https://wpshadow.com/kb/update-jquery-version/',
				'training_link' => 'https://wpshadow.com/training/jquery-security/',
				'auto_fixable'  => false,
				'threat_level'  => 65,
			);
		}

		return null;
	}
}
