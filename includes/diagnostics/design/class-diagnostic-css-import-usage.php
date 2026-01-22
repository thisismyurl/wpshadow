<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: CSS @import Usage (ASSET-010)
 * 
 * Detects @import in CSS files (blocks parallel loading).
 * Philosophy: Educate (#5) about CSS loading best practices.
 * 
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Css_Import_Usage extends Diagnostic_Base {
	
	/**
	 * Run the diagnostic check
	 * 
	 * @return array|null Array with finding details or null if no issue found
	 */
	public static function check(): ?array {
		// Check for @import usage in CSS (blocks rendering)
        $css_imports = get_transient('wpshadow_css_import_count');
        
        if ($css_imports && $css_imports > 0) {
            return array(
                'id' => 'css-import-usage',
                'title' => sprintf(__('%d @import Statements Found', 'wpshadow'), $css_imports),
                'description' => __('@import statements block rendering and prevent parallel downloads. Use <link> tags or CSS concatenation instead.', 'wpshadow'),
                'severity' => 'medium',
                'category' => 'design',
                'kb_link' => 'https://wpshadow.com/kb/css-import-performance/',
                'training_link' => 'https://wpshadow.com/training/stylesheet-optimization/',
                'auto_fixable' => false,
                'threat_level' => 50,
            );
        }
        return null;
}
}
