<?php declare(strict_types=1);
/**
 * Weak Password Hashing Diagnostic
 *
 * Philosophy: Cryptography - use strong password hashing
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check for weak password hashing.
 */
class Diagnostic_Weak_Password_Hashing {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		global $wpdb;
		
		// Check for old MD5/SHA1 password hashes in custom tables
		$results = $wpdb->get_results(
			"SELECT COUNT(*) as count FROM {$wpdb->usermeta} WHERE meta_key = 'legacy_password_hash' AND meta_value REGEXP '^[a-f0-9]{32}$|^[a-f0-9]{40}$'"
		);
		
		if ( ! empty( $results[0]->count ) && $results[0]->count > 0 ) {
			return array(
				'id'          => 'weak-password-hashing',
				'title'       => 'Weak Password Hashing Algorithm Detected',
				'description' => sprintf(
					'Found %d users with weak password hashes (MD5 or SHA1). Rehash using bcrypt/Argon2. Old hashes are vulnerable to rainbow tables.',
					$results[0]->count
				),
				'severity'    => 'high',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/upgrade-password-hashing/',
				'training_link' => 'https://wpshadow.com/training/password-hashing/',
				'auto_fixable' => false,
				'threat_level' => 75,
			);
		}
		
		return null;
	}
}
