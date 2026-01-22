<?php
declare(strict_types=1);
/**
 * XSS Vulnerability Pattern Detection Diagnostic
 *
 * Philosophy: Output security - prevent cross-site scripting
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for XSS vulnerabilities.
 */
class Diagnostic_XSS_Patterns extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$plugins_dir = WP_PLUGIN_DIR;
		$files = glob( $plugins_dir . '/*/*.php' );
		
		foreach ( $files as $file ) {
			$content = file_get_contents( $file );
			
			// Look for unescaped output of user input
			if ( preg_match( '/echo\s+\$_(?:GET|POST|REQUEST)(?![^"\']*(?:esc_html|esc_attr|wp_kses))/', $content ) ) {
				return array(
					'id'          => 'xss-patterns',
					'title'       => 'Cross-Site Scripting (XSS) Vulnerability Pattern',
					'description' => 'Code outputs user input without escaping. Attackers inject malicious JavaScript. Always escape output: echo esc_html( $var ).',
					'severity'    => 'critical',
					'category'    => 'security',
					'kb_link'     => 'https://wpshadow.com/kb/prevent-xss-attacks/',
					'training_link' => 'https://wpshadow.com/training/output-escaping/',
					'auto_fixable' => false,
					'threat_level' => 90,
				);
			}
		}
		
		return null;
	}
}
