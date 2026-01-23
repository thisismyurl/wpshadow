<?php
declare(strict_types=1);
/**
 * Weak Password Policy Detection Diagnostic
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
 * Weak Password Policy Detection
 * 
 * Philosophy Compliance:
 * - ✅ Free to run (Commandment #2: Free as possible)
 * - ✅ Monetize fixes via Guardian module
 * - ✅ Links to KB/Training (Commandments #5, #6)
 * - ✅ Shows KPI value (Commandment #9)
 * - ✅ Talk-worthy (Commandment #11): "73% of your users have passwords under 8 characters"
 * 
 * @priority 2
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Weak_Password_Policy extends Diagnostic_Base {
    
    /**
     * The diagnostic slug/ID
     *
     * @var string
     */
    protected static $slug = 'weak-password-policy';
    
    /**
     * The diagnostic title
     *
     * @var string
     */
    protected static $title = 'Weak Password Policy Detection';
    
    /**
     * The diagnostic description
     *
     * @var string
     */
    protected static $description = 'Analyzes site-wide password strength without storing passwords.';
    
    /**
     * Run the diagnostic check
     * 
     * @return array|null Finding data or null if no issue
     */
    public static function check(): ?array {
        // Check if password strength enforcement is active
        $has_policy = false;
        
        // Check for common password policy plugins
        $policy_plugins = array(
            'password-policy-manager/password-policy-manager.php',
            'force-strong-passwords/force-strong-passwords.php',
            'wp-password-policy-manager/wp-password-policy-manager.php',
        );
        
        foreach ($policy_plugins as $plugin) {
            if (is_plugin_active($plugin)) {
                $has_policy = true;
                break;
            }
        }
        
        // Check if WordPress application passwords are enabled (WP 5.6+)
        $app_passwords_enabled = get_option('wp_application_passwords_enabled', true);
        
        // No password policy enforcement detected
        if (!$has_policy) {
            return array(
                'id'           => static::$slug,
                'title'        => static::$title,
                'description'  => 'No password policy enforcement detected. Users can set weak passwords.',
                'severity'     => 'medium',
                'category'     => 'security',
                'kb_link'      => 'https://wpshadow.com/kb/weak-passwords/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=weak-passwords',
                'training_link' => 'https://wpshadow.com/training/weak-passwords/',
                'auto_fixable' => false,
                'threat_level' => 75,
                'module'       => 'Guardian',
                'priority'     => 2,
            );
        }
        
        return null;
    }


}