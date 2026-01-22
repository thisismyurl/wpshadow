<?php
declare(strict_types=1);
/**
 * Hardcoded API Keys in Code Diagnostic
 *
 * @package WPShadow
 * @subpackage DiagnosticsFuture
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
     * IMPLEMENTATION PLAN
     * 
     * "Holy Shit" Moment: "Your Stripe secret key is hardcoded in theme functions.php"
     * Revenue Path: Guardian
     * KB Article: https://wpshadow.com/kb/hardcoded-api-keys/
     * Training Video: https://wpshadow.com/training/hardcoded-api-keys/
     * 
     * Implementation Steps:
     * Scan PHP/JS files in themes/plugins (active only)
     * Regex patterns: Stripe keys (sk_live_, pk_live_), AWS (AKIA...), Google API, PayPal, OpenAI
     * Search for: api_key, apiKey, secret_key, access_token patterns
     * Exclude wp-config.php (expected location)
     * Show exact file + line number
     * Display redacted key: "sk_live_***************ABC"
     * Estimate exposure cost: "Could result in $25,000 AWS bill"
     * One-click "Move to wp-config" treatment
     * Guardian: Continuous code scanning
     * 
     * KPI Tracking:
     * - Time saved: [Calculate based on severity]
     * - Issues found: [Count of findings]
     * - Value delivered: [Show $ impact if applicable]
     * 
     * Treatment Options (Future):
     * - Free: Basic remediation steps (KB link)
     * - Guardian: Advanced automation + monitoring
     * 
     * Philosophy Compliance:
     * - Free detection: ✅ Always accessible
     * - Paid fixes: ✅ Module-gated advanced features
     * - Education: ✅ KB + Training links
     * - KPI: ✅ Track measurable value
     * - Talk-worthy: ✅ Creates "holy shit" moments
     */
}
