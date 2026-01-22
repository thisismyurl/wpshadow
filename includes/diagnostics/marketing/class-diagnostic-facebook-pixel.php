<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Facebook Pixel Firing?
 * 
 * Target Persona: Digital Marketing Agency
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
 */
class Diagnostic_Facebook_Pixel extends Diagnostic_Base {
    protected static $slug = 'facebook-pixel';
    protected static $title = 'Facebook Pixel Firing?';
    protected static $description = 'Tests Meta/Facebook pixel installation.';

    public static function check(): ?array {
        // Check for Facebook Pixel plugins
        $pixel_plugins = array(
            'official-facebook-pixel/facebook-for-wordpress.php',
            'pixelyoursite/pixelyoursite.php',
        );
        
        foreach ($pixel_plugins as $plugin) {
            if (is_plugin_active($plugin)) {
                return null; // Pass - Pixel plugin active
            }
        }
        
        // Check for fbq() code in header
        ob_start();
        wp_head();
        $header_content = ob_get_clean();
        
        if (strpos($header_content, 'fbq(') !== false || strpos($header_content, 'facebook.com/tr?') !== false) {
            return null; // Pass - Facebook Pixel detected
        }
        
        return array(
            'id'            => static::$slug,
            'title'         => static::$title,
            'description'   => 'Facebook Pixel not detected.',
            'color'         => '#ff9800',
            'bg_color'      => '#fff3e0',
            'kb_link'       => 'https://wpshadow.com/kb/facebook-pixel/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=facebook-pixel',
            'training_link' => 'https://wpshadow.com/training/facebook-pixel/',
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