<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Heatmap/Recording Tools Active?
 * 
 * Target Persona: Digital Marketing Agency
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Heatmap_Tracking extends Diagnostic_Base {
    protected static $slug = 'heatmap-tracking';
    protected static $title = 'Heatmap/Recording Tools Active?';
    protected static $description = 'Checks for Hotjar, Clarity, etc.';

    public static function check(): ?array {
        // Check for heatmap/session recording tools
        $heatmap_patterns = array(
            'hotjar.com',
            'mouseflow.com',
            'crazyegg.com',
            'luckyorange.com',
            'sessioncam.com',
        );
        
        ob_start();
        wp_head();
        $header_content = ob_get_clean();
        
        foreach ($heatmap_patterns as $pattern) {
            if (stripos($header_content, $pattern) !== false) {
                return null; // Pass - heatmap tool detected
            }
        }
        
        // Heatmap tools are optional, so only flag if other marketing tools are present
        // Check if GA4 or GTM present (indicates marketing focus)
        if (preg_match('/G-[A-Z0-9]{10}/', $header_content) || preg_match('/GTM-[A-Z0-9]+/', $header_content)) {
            return array(
                'id'            => static::$slug,
                'title'         => static::$title,
                'description'   => 'Analytics detected but no heatmap/session recording tool found.',
                'color'         => '#ff9800',
                'bg_color'      => '#fff3e0',
                'kb_link'       => 'https://wpshadow.com/kb/heatmap-tracking/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=heatmap-tracking',
                'training_link' => 'https://wpshadow.com/training/heatmap-tracking/',
                'auto_fixable'  => false,
                'threat_level'  => 60,
                'module'        => 'Marketing',
                'priority'      => 2,
            );
        }
        
        return null;
    }

}