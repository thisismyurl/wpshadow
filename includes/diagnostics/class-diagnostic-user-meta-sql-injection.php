<?php declare(strict_types=1);
/**
 * User Meta SQL Injection Diagnostic
 *
 * Philosophy: Code security - detect unsafe user meta queries
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check for SQL injection in user meta queries.
 */
class Diagnostic_User_Meta_SQL_Injection {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		// Scan active plugins for dangerous patterns (limited scope)
		$active_plugins = get_option( 'active_plugins', array() );
		$vulnerable_plugins = array();
		
		foreach ( array_slice( $active_plugins, 0, 5 ) as $plugin ) {
			$plugin_file = WP_PLUGIN_DIR . '/' . $plugin;
			if ( file_exists( $plugin_file ) ) {
				$content = file_get_contents( $plugin_file );
				
				// Look for get_user_meta with $_GET/$_POST as meta_key
				if ( preg_match( '/get_user_meta\s*\([^,]+,\s*\$_(GET|POST|REQUEST)\[/i', $content ) ||
				     preg_match( '/update_user_meta\s*\([^,]+,\s*\$_(GET|POST|REQUEST)\[/i', $content ) ) {
					$vulnerable_plugins[] = dirname( $plugin );
				}
			}
		}
		
		if ( ! empty( $vulnerable_plugins ) ) {
			return array(
				'id'          => 'user-meta-sql-injection',
				'title'       => 'User Meta SQL Injection Risk',
				'description' => sprintf(
					'Plugins with potential user meta SQL injection: %s. User-controlled meta_key in get_user_meta() allows SQL injection. Sanitize with sanitize_key() before meta queries.',
					implode( ', ', $vulnerable_plugins )
				),
				'severity'    => 'high',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/prevent-user-meta-sql-injection/',
				'training_link' => 'https://wpshadow.com/training/wordpress-sql-security/',
				'auto_fixable' => false,
				'threat_level' => 80,
			);
		}
		
		return null;
	}
}
