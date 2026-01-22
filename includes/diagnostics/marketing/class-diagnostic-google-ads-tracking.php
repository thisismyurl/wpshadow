<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Google Ads Conversion Tracking?
 * 
 * Target Persona: Digital Marketing Agency
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
 */
class Diagnostic_Google_Ads_Tracking extends Diagnostic_Base {
    protected static $slug = 'google-ads-tracking';
    protected static $title = 'Google Ads Conversion Tracking?';
    protected static $description = 'Verifies Google Ads remarketing tag.';

    public static function check(): ?array {
        // Check for Google Ads conversion tracking code (AW-XXXXXXXXX)
        ob_start();
        wp_head();
        $header_content = ob_get_clean();
        
        if (preg_match('/AW-[0-9]+/', $header_content) || strpos($header_content, 'gtag/js?id=AW-') !== false) {
            return null; // Pass - Google Ads tracking detected
        }
        
        return array(
            'id'            => static::$slug,
            'title'         => static::$title,
            'description'   => 'Google Ads conversion tracking not detected.',
            'color'         => '#ff9800',
            'bg_color'      => '#fff3e0',
            'kb_link'       => 'https://wpshadow.com/kb/google-ads-tracking/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=google-ads-tracking',
            'training_link' => 'https://wpshadow.com/training/google-ads-tracking/',
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