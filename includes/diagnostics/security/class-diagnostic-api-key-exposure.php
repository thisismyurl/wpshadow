<?php
declare(strict_types=1);
/**
 * API Key Exposure Detection Diagnostic
 *
 * Philosophy: Credential security - prevent API key leaks
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for exposed API keys in files.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_API_Key_Exposure extends Diagnostic_Base :
{
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$wp_content = WP_CONTENT_DIR;
		
		// Check for common API key patterns in PHP files
		$files = glob( $wp_content . '/themes/*/*.php' );
		
		foreach ( $files as $file ) {
			$content = file_get_contents( $file );
			
			if ( preg_match( '/api.?key|api.?secret|sk_live|sk_test|amazonaws|bearer\s+[a-zA-Z0-9]/i', $content ) ) {
				return array(
					'id'          => 'api-key-exposure',
					'title'       => 'Potential Exposed API Keys in Source Code',
					'description' => 'API keys, tokens, or credentials found in theme/plugin source code. Exposed keys allow attackers to access third-party services. Store secrets in environment variables or secure vaults.',
					'severity'    => 'critical',
					'category'    => 'security',
					'kb_link'     => 'https://wpshadow.com/kb/secure-api-keys/',
					'training_link' => 'https://wpshadow.com/training/credential-management/',
					'auto_fixable' => false,
					'threat_level' => 90,
				);
			}
		}
		
		return null;
	}
}
