<?php
declare(strict_types=1);
/**
 * Template Injection Vulnerability Diagnostic
 *
 * Philosophy: Code injection - prevent template injection
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for template injection patterns.
 */
class Diagnostic_Template_Injection extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$plugins_dir = WP_PLUGIN_DIR;
		$files = glob( $plugins_dir . '/*/*.php' );
		
		foreach ( $files as $file ) {
			$content = file_get_content( $file );
			
			// Look for Twig or template patterns with user input
			if ( preg_match( '/->render\s*\(\s*\$_(?:GET|POST|REQUEST)|Twig.*render.*\$_/', $content ) ) {
				return array(
					'id'          => 'template-injection',
					'title'       => 'Server-Side Template Injection (SSTI) Risk',
					'description' => 'Code passes user input to template engines (Twig, Handlebars). Template injection allows arbitrary code execution. Sanitize before rendering.',
					'severity'    => 'critical',
					'category'    => 'security',
					'kb_link'     => 'https://wpshadow.com/kb/prevent-template-injection/',
					'training_link' => 'https://wpshadow.com/training/template-security/',
					'auto_fixable' => false,
					'threat_level' => 90,
				);
			}
		}
		
		return null;
	}
}
