<?php declare(strict_types=1);
/**
 * Missing Authorization Checks Diagnostic
 *
 * Philosophy: Access control - require capability checks
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check for missing authorization on sensitive actions.
 */
class Diagnostic_Missing_Authorization_Checks {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		$plugins_dir = WP_PLUGIN_DIR;
		$files = glob( $plugins_dir . '/*/*.php' );
		
		foreach ( $files as $file ) {
			$content = file_get_contents( $file );
			
			// Look for add_action without capability check nearby
			if ( preg_match( '/add_action\s*\(\s*[\'"]admin_init[\'"].*?function\s*\(\s*\)\s*\{[^}]{0,500}update_option|update_post_meta/s', $content ) ) {
				// Check if current_user_can is missing
				if ( ! preg_match( '/current_user_can/', substr( $content, strpos( $content, 'add_action' ), 500 ) ) ) {
					return array(
						'id'          => 'missing-authorization-checks',
						'title'       => 'Missing Authorization Checks on Sensitive Actions',
						'description' => 'Code updates options/posts without checking user capabilities. Add current_user_can() checks before any sensitive operations.',
						'severity'    => 'high',
						'category'    => 'security',
						'kb_link'     => 'https://wpshadow.com/kb/add-authorization-checks/',
						'training_link' => 'https://wpshadow.com/training/capability-checking/',
						'auto_fixable' => false,
						'threat_level' => 80,
					);
				}
			}
		}
		
		return null;
	}
}
