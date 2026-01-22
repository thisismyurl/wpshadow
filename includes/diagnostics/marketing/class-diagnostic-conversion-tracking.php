<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Conversion Tracking Working?
 * 
 * Target Persona: Digital Marketing Agency
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Conversion_Tracking extends Diagnostic_Base {
    protected static $slug = 'conversion-tracking';
    protected static $title = 'Conversion Tracking Working?';
    protected static $description = 'Tests if goal completions are tracked.';

    public static function check(): ?array {
        // Check if any conversion tracking is configured
        // This is informational - checks for common conversion tracking patterns
        ob_start();
        wp_head();
        $header_content = ob_get_clean();
        
        $conversion_patterns = array(
            'gtag.*event.*conversion',
            'fbq.*Purchase',
            'AW-[0-9]+/[A-Za-z0-9_-]+', // Google Ads conversion label
        );
        
        foreach ($conversion_patterns as $pattern) {
            if (preg_match('/' . $pattern . '/i', $header_content)) {
                return null; // Pass - conversion tracking detected
            }
        }
        
        // Check if WooCommerce or EDD active (would typically have conversion tracking)
        if (class_exists('WooCommerce') || class_exists('Easy_Digital_Downloads')) {
            return array(
                'id'            => static::$slug,
                'title'         => static::$title,
                'description'   => 'E-commerce active but no conversion tracking detected.',
                'color'         => '#ff9800',
                'bg_color'      => '#fff3e0',
                'kb_link'       => 'https://wpshadow.com/kb/conversion-tracking/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=conversion-tracking',
                'training_link' => 'https://wpshadow.com/training/conversion-tracking/',
                'auto_fixable'  => false,
                'threat_level'  => 60,
                'module'        => 'Marketing',
                'priority'      => 1,
            );
        }
        
        return null;
    }
}
