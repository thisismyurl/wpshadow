<?php
declare(strict_types=1);
/**
 * Database Prefix Security Diagnostic
 *
 * Philosophy: Security hardening - obscurity layer against SQL injection
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if using default wp_ database prefix.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Database_Prefix extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wpdb;
		
		// Check if using default wp_ prefix
		if ( $wpdb->prefix === 'wp_' ) {
			return array(
				'id'          => 'database-prefix',
				'title'       => 'Default Database Prefix in Use',
				'description' => 'Your site uses the default "wp_" database prefix, making SQL injection attacks easier. Consider changing the prefix during initial setup.',
				'severity'    => 'medium',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/change-wordpress-database-prefix/',
				'training_link' => 'https://wpshadow.com/training/database-prefix/',
				'auto_fixable' => false,
				'threat_level' => 55,
			);
		}
		
		return null;
	}

}