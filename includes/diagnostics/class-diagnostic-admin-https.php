<?php declare(strict_types=1);
/**
 * Admin HTTPS Enforcement Diagnostic
 *
 * Philosophy: Security hardening - protect admin sessions
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check if admin is forced over HTTPS.
 */
class Diagnostic_Admin_HTTPS {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
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
