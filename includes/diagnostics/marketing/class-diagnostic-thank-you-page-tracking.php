<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Thank You Page Tracking?
 * 
 * Target Persona: Digital Marketing Agency
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
 */
class Diagnostic_Thank_You_Page_Tracking extends Diagnostic_Base {
    protected static $slug = 'thank-you-page-tracking';
    protected static $title = 'Thank You Page Tracking?';
    protected static $description = 'Verifies conversion confirmation tracking.';

    public static function check(): ?array {
        // Check if conversion tracking is present (implies thank you page tracking)
        ob_start();
        wp_head();
        $header_content = ob_get_clean();
        
        if (preg_match('/gtag.*event.*conversion/i', $header_content) || 
            preg_match('/fbq.*Purchase/i', $header_content)) {
            return null; // Pass - conversion tracking detected (implies thank you pages)
        }
        
        // If e-commerce active, suggest thank you page tracking
        if (class_exists('WooCommerce') || class_exists('Easy_Digital_Downloads')) {
            return array(
                'id'            => static::$slug,
                'title'         => static::$title,
                'description'   => 'E-commerce active but no thank you page conversion tracking detected.',
                'color'         => '#ff9800',
                'bg_color'      => '#fff3e0',
                'kb_link'       => 'https://wpshadow.com/kb/thank-you-page-tracking/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=thank-you-page-tracking',
                'training_link' => 'https://wpshadow.com/training/thank-you-page-tracking/',
                'auto_fixable'  => false,
                'threat_level'  => 60,
                'module'        => 'Marketing',
                'priority'      => 2,
            );
        }
        
        return null;
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