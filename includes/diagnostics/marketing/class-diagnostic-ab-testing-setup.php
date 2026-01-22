<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: A/B Testing Configured?
 * 
 * Target Persona: Digital Marketing Agency
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
 */
class Diagnostic_AB_Testing_Setup extends Diagnostic_Base {
    protected static $slug = 'ab-testing-setup';
    protected static $title = 'A/B Testing Configured?';
    protected static $description = 'Checks if split testing tools are active.';

    public static function check(): ?array {
        // Check for A/B testing plugins
        $ab_plugins = array(
            'nelio-ab-testing/nelio-ab-testing.php',
            'google-optimize/google-optimize.php',
        );
        
        foreach ($ab_plugins as $plugin) {
            if (is_plugin_active($plugin)) {
                return null; // Pass - A/B testing plugin active
            }
        }
        
        // Check for Google Optimize in header
        ob_start();
        wp_head();
        $header_content = ob_get_clean();
        
        if (strpos($header_content, 'optimize.google.com') !== false) {
            return null; // Pass - Google Optimize detected
        }
        
        // A/B testing is advanced, only suggest if significant marketing infrastructure
        if (preg_match('/GTM-[A-Z0-9]+/', $header_content) && preg_match('/G-[A-Z0-9]{10}/', $header_content)) {
            return array(
                'id'            => static::$slug,
                'title'         => static::$title,
                'description'   => 'Advanced marketing tracking detected but no A/B testing configured.',
                'color'         => '#ff9800',
                'bg_color'      => '#fff3e0',
                'kb_link'       => 'https://wpshadow.com/kb/ab-testing-setup/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=ab-testing-setup',
                'training_link' => 'https://wpshadow.com/training/ab-testing-setup/',
                'auto_fixable'  => false,
                'threat_level'  => 60,
                'module'        => 'Marketing',
                'priority'      => 2,
            );
        }
        
        return null;
    }
}
