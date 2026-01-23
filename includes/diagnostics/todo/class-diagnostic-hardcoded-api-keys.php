<?php
declare(strict_types=1);
/**
 * Hardcoded API Keys in Code Diagnostic
 *
 * @package WPShadow
 * @subpackage DiagnosticsFuture
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Hardcoded API Keys in Code
 * 
 * Philosophy Compliance:
 * - ✅ Free to run (Commandment #2: Free as possible)
 * - ✅ Monetize fixes via Guardian module
 * - ✅ Links to KB/Training (Commandments #5, #6)
 * - ✅ Shows KPI value (Commandment #9)
 * - ✅ Talk-worthy (Commandment #11): "Your Stripe secret key is hardcoded in theme functions.php"
 * 
 * @priority 1
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Hardcoded_API_Keys extends Diagnostic_Base {
    
    /**
     * The diagnostic slug/ID
     *
     * @var string
     */
    protected static $slug = 'hardcoded-api-keys';
    
    /**
     * The diagnostic title
     *
     * @var string
     */
    protected static $title = 'Hardcoded API Keys in Code';
    
    /**
     * The diagnostic description
     *
     * @var string
     */
    protected static $description = 'Scans code for exposed API keys and secrets in public files.';
    
    /**
     * Run the diagnostic check
     * 
     * @return array|null Finding data or null if no issue
     */
    public static function check(): ?array {
        $found_keys = array();
        
        // Patterns for API key detection
        $patterns = array(
            '/["\']api[_-]?key["\']\s*=>\s*["\']([a-zA-Z0-9_-]{20,})["\']/',
            '/["\']secret[_-]?key["\']\s*=>\s*["\']([a-zA-Z0-9_-]{20,})["\']/',
            '/["\']token["\']\s*=>\s*["\']([a-zA-Z0-9_-]{20,})["\']/',
            '/define\s*\(\s*["\'].*API.*KEY["\']\s*,\s*["\']([a-zA-Z0-9_-]{20,})["\']/',
        );
        
        // Scan theme files
        $theme_dir = get_template_directory();
        $plugin_dir = WP_PLUGIN_DIR;
        
        $dirs = array($theme_dir, $plugin_dir);
        
        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                continue;
            }
            
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($dir),
                \RecursiveIteratorIterator::SELF_FIRST
            );
            
            $scan_count = 0;
            foreach ($files as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    // Limit scan
                    if (++$scan_count > 500) {
                        break;
                    }
                    
                    $content = file_get_contents($file->getPathname());
                    
                    foreach ($patterns as $pattern) {
                        if (preg_match($pattern, $content)) {
                            $found_keys[] = str_replace(array($theme_dir, $plugin_dir), array('theme', 'plugins'), $file->getPathname());
                            break;
                        }
                    }
                }
            }
        }
        
        if (empty($found_keys)) {
            return null;
        }
        
        return array(
            'id'           => static::$slug,
            'title'        => static::$title,
            'description'  => sprintf(
                'Found hardcoded API keys in %d file(s): %s',
                count($found_keys),
                implode(', ', array_slice($found_keys, 0, 3))
            ),
            'severity'     => 'critical',
            'category'     => 'security',
            'kb_link'      => 'https://wpshadow.com/kb/hardcoded-api-keys/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=hardcoded-api-keys',
            'training_link' => 'https://wpshadow.com/training/hardcoded-api-keys/',
            'auto_fixable' => false,
            'threat_level' => 100,
            'module'       => 'Core',
            'priority'     => 1,
        );
    }




	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Hardcoded API Keys in Code
	 * Slug: hardcoded-api-keys
	 * 
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Scans code for exposed API keys and secrets in public files.
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_hardcoded_api_keys(): array {
		/*
		 * IMPLEMENTATION NOTES:
		 * - This test validates the actual WordPress site state
		 * - Do not use mocks or stubs
		 * - Call self::check() to get the diagnostic result
		 * - Verify the result matches expected site state
		 * - Return [ 'passed' => bool, 'message' => string ]
		 */
		
		$result = self::check();
		
		// TODO: Implement actual test logic
		return array(
			'passed' => false,
			'message' => 'Test not yet implemented for ' . self::$slug,
		);
	}

}
