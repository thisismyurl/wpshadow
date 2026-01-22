<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Google Tag Manager Installed?
 * 
 * Target Persona: Digital Marketing Agency
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
 */
class Diagnostic_GTM_Installed extends Diagnostic_Base {
    protected static $slug = 'gtm-installed';
    protected static $title = 'Google Tag Manager Installed?';
    protected static $description = 'Verifies GTM container is present and firing.';

    public static function check(): ?array {
        // Check for GTM plugins
        $gtm_plugins = array(
            'duracelltomi-google-tag-manager/duracelltomi-google-tag-manager-for-wordpress.php',
            'google-site-kit/google-site-kit.php',
        );
        
        foreach ($gtm_plugins as $plugin) {
            if (is_plugin_active($plugin)) {
                return null; // Pass - GTM plugin active
            }
        }
        
        // Check for GTM code in header (GTM-XXXXXXX format)
        ob_start();
        wp_head();
        $header_content = ob_get_clean();
        
        if (preg_match('/GTM-[A-Z0-9]+/', $header_content)) {
            return null; // Pass - GTM container detected
        }
        
        return array(
            'id'            => static::$slug,
            'title'         => static::$title,
            'description'   => 'Google Tag Manager not detected.',
            'color'         => '#ff9800',
            'bg_color'      => '#fff3e0',
            'kb_link'       => 'https://wpshadow.com/kb/gtm-installed/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=gtm-installed',
            'training_link' => 'https://wpshadow.com/training/gtm-installed/',
            'auto_fixable'  => false,
            'threat_level'  => 60,
            'module'        => 'Marketing',
            'priority'      => 1,
        );
    }

    /**
     * IMPLEMENTATION PLAN (Digital Marketing Agency)
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