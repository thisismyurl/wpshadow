<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Can You Still Log In?
 * 
 * Target Persona: Non-technical Site Owner (Mom/Dad)
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
 */
class Diagnostic_Login_Works extends Diagnostic_Base {
    protected static $slug = 'login-works';
    protected static $title = 'Can You Still Log In?';
    protected static $description = 'Tests admin login functionality.';

    public static function check(): ?array {
        // If we're logged in and can run this diagnostic, login works!
        // This is more of a sanity check diagnostic
        
        // Check if user is logged in
        if (!is_user_logged_in()) {
            return array(
                'id'            => static::$slug,
                'title'         => static::$title,
                'description'   => 'Cannot verify - you are not logged in.',
                'severity'      => 'info',
                'category'      => 'security',
                'kb_link'       => 'https://wpshadow.com/kb/login-works/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=login-works',
                'training_link' => 'https://wpshadow.com/training/login-works/',
                'auto_fixable'  => false,
                'threat_level'  => 60,
                'module'        => 'Security',
                'priority'      => 1,
            );
        }
        
        // Check if wp-login.php is accessible
        $login_url = wp_login_url();
        $response = wp_remote_get($login_url, array('timeout' => 10));
        
        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            return array(
                'id'            => static::$slug,
                'title'         => 'Login Page May Be Blocked',
                'description'   => 'wp-login.php appears to be inaccessible or blocked.',
                'severity'      => 'high',
                'category'      => 'security',
                'kb_link'       => 'https://wpshadow.com/kb/login-works/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=login-works',
                'training_link' => 'https://wpshadow.com/training/login-works/',
                'auto_fixable'  => false,
                'threat_level'  => 60,
                'module'        => 'Security',
                'priority'      => 1,
            );
        }
        
        // Login works!
        return null;
    }

    /**
     * IMPLEMENTATION PLAN (Non-technical Site Owner (Mom/Dad))
     * 
     * What This Checks:
     * - [Technical implementation details]
     * 
     * Why It Matters:
     * - [Business value in plain English]
     * 
     * Success Criteria:
     * - [What "passing" means]
     * 
     * How to Fix:
     * - Step 1: [Clear instruction]
     * - Step 2: [Next step]
     * - KB Article: Detailed explanation and examples
     * - Training Video: Visual walkthrough
     * 
     * KPIs Tracked:
     * - Issues found and fixed
     * - Time saved (estimated minutes)
     * - Site health improvement %
     * - Business value delivered ($)
     */
}