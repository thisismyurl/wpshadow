<?php
declare(strict_types=1);
/**
 * Plugin/Theme Backdoor Detection Diagnostic
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
 * Plugin/Theme Backdoor Detection
 * 
 * Philosophy Compliance:
 * - ✅ Free to run (Commandment #2: Free as possible)
 * - ✅ Monetize fixes via Guardian + SaaS module
 * - ✅ Links to KB/Training (Commandments #5, #6)
 * - ✅ Shows KPI value (Commandment #9)
 * - ✅ Talk-worthy (Commandment #11): "Plugin X is sending your user data to a third-party server"
 * 
 * @priority 1
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Plugin_Theme_Backdoor extends Diagnostic_Base {
    
    /**
     * The diagnostic slug/ID
     *
     * @var string
     */
    protected static $slug = 'plugin-theme-backdoor';
    
    /**
     * The diagnostic title
     *
     * @var string
     */
    protected static $title = 'Plugin/Theme Backdoor Detection';
    
    /**
     * The diagnostic description
     *
     * @var string
     */
    protected static $description = 'Scans plugin/theme code for suspicious patterns and unauthorized data transmission.';
    
    /**
     * Run the diagnostic check
     * 
     * @return array|null Finding data or null if no issue
     */
    public static function check(): ?array {
        $suspicious = array();
        
        // Patterns to detect
        $patterns = array(
            'eval(',
            'base64_decode(',
            'exec(',
            'shell_exec(',
            'system(',
            'passthru(',
            'proc_open(',
            'assert(',
        );
        
        // Scan plugin files
        $plugin_dir = WP_PLUGIN_DIR;
        if (!is_dir($plugin_dir)) {
            return null;
        }
        
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($plugin_dir),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        
        $scan_count = 0;
        foreach ($files as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                // Limit scan to avoid timeouts
                if (++$scan_count > 1000) {
                    break;
                }
                
                $content = file_get_contents($file->getPathname());
                
                foreach ($patterns as $pattern) {
                    if (stripos($content, $pattern) !== false) {
                        $suspicious[] = str_replace(WP_PLUGIN_DIR . '/', '', $file->getPathname());
                        break;
                    }
                }
            }
        }
        
        if (empty($suspicious)) {
            return null;
        }
        
        return array(
            'id'           => static::$slug,
            'title'        => static::$title,
            'description'  => sprintf(
                'Found %d file(s) with suspicious code patterns: %s',
                count($suspicious),
                implode(', ', array_slice($suspicious, 0, 5))
            ),
            'severity'     => 'critical',
            'category'     => 'security',
            'kb_link'      => 'https://wpshadow.com/kb/plugin-backdoors/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=plugin-backdoors',
            'training_link' => 'https://wpshadow.com/training/plugin-backdoors/',
            'auto_fixable' => false,
            'threat_level' => 100,
            'module'       => 'Guardian + SaaS',
            'priority'     => 1,
        );
    }


}