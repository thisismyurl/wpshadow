<?php
declare(strict_types=1);
/**
 * Admin HTTPS Enforcement Diagnostic
 *
 * Philosophy: Security hardening - protect admin sessions
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if admin is forced over HTTPS.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Admin_HTTPS extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Only check if site has SSL
		if ( ! is_ssl() ) {
			return null;
		}
		
		// Check if FORCE_SSL_ADMIN is enabled
		if ( ! defined( 'FORCE_SSL_ADMIN' ) || ! FORCE_SSL_ADMIN ) {
			return array(
				'id'          => 'admin-https',
				'title'       => 'Admin Not Forced Over HTTPS',
				'description' => 'Your site has SSL but admin area is not forced over HTTPS. Enable FORCE_SSL_ADMIN to prevent session hijacking.',
				'severity'    => 'high',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/force-admin-https/',
				'training_link' => 'https://wpshadow.com/training/admin-https/',
				'auto_fixable' => false,
				'threat_level' => 80,
			);
		}
		
		return null;
	}

}