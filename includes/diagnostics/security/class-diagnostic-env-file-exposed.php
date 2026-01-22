<?php
declare(strict_types=1);
/**
 * Exposed Environment Variables Diagnostic
 *
 * @package WPShadow
 * @subpackage DiagnosticsFuture
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Exposed Environment Variables
 * 
 * Philosophy Compliance:
 * - ✅ Free to run (Commandment #2: Free as possible)
 * - ✅ Monetize fixes via Free + Guardian module
 * - ✅ Links to KB/Training (Commandments #5, #6)
 * - ✅ Shows KPI value (Commandment #9)
 * - ✅ Talk-worthy (Commandment #11): "Your .env file with database passwords is publicly accessible"
 * 
 * @priority 1
 */
class Diagnostic_Env_File_Exposed extends Diagnostic_Base {
    
    /**
     * The diagnostic slug/ID
     *
     * @var string
     */
    protected static $slug = 'env-file-exposed';
    
    /**
     * The diagnostic title
     *
     * @var string
     */
    protected static $title = 'Exposed Environment Variables';
    
    /**
     * The diagnostic description
     *
     * @var string
     */
    protected static $description = 'Tests if .env and other sensitive files are publicly accessible.';
    
    /**
     * Run the diagnostic check
     * 
     * @return array|null Finding data or null if no issue
     */
    public static function check(): ?array {
        // Check if .env file exists
        $env_file = ABSPATH . '.env';
        
        if (!file_exists($env_file)) {
            return null;
        }
        
        // Try to access it via HTTP
        $site_url = site_url('/.env');
        $response = wp_remote_get($site_url, array(
            'timeout' => 5,
            'sslverify' => false,
        ));
        
        // If we can access it, it's exposed
        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            $body = wp_remote_retrieve_body($response);
            
            // Verify it's actually the env file content
            if (!empty($body) && strlen($body) > 10) {
                return array(
                    'id'           => static::$slug,
                    'title'        => static::$title,
                    'description'  => 'Your .env file is publicly accessible! This exposes sensitive credentials.',
                    'severity'     => 'critical',
                    'category'     => 'security',
                    'kb_link'      => 'https://wpshadow.com/kb/exposed-env-files/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=exposed-env-files',
                    'training_link' => 'https://wpshadow.com/training/exposed-env-files/',
                    'auto_fixable' => true,
                    'threat_level' => 100,
                    'module'       => 'Core',
                    'priority'     => 1,
                );
            }
        }
        
        return null;
    }

}
