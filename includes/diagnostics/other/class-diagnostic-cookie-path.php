<?php
declare(strict_types=1);
/**
 * Authentication Cookie Path Diagnostic
 *
 * Philosophy: Security hardening - restrict cookie scope
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check authentication cookie path configuration.
 */
class Diagnostic_Cookie_Path extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Check if ADMIN_COOKIE_PATH is properly set
		if ( ! defined( 'ADMIN_COOKIE_PATH' ) ) {
			return array(
				'id'          => 'cookie-path',
				'title'       => 'Admin Cookie Path Not Restricted',
				'description' => 'Admin authentication cookies are not restricted to admin paths. Define ADMIN_COOKIE_PATH to prevent cookie theft via front-end XSS.',
				'severity'    => 'medium',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/restrict-admin-cookies/',
				'training_link' => 'https://wpshadow.com/training/cookie-security/',
				'auto_fixable' => false,
				'threat_level' => 60,
			);
		}
		
		return null;
	}
}
