<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Is Site Currently Down?
 * 
 * Target Persona: Non-technical Site Owner (Mom/Dad)
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
 */
class Diagnostic_Site_Down extends Diagnostic_Base {
    protected static $slug = 'site-down';
    protected static $title = 'Is Site Currently Down?';
    protected static $description = 'External check to verify site is reachable.';

    public static function check(): ?array {
        $home_url = home_url();
        $response = wp_remote_get($home_url, array(
            'timeout' => 15,
            'sslverify' => false,
        ));
        
        if (is_wp_error($response)) {
            return array(
                'id'            => static::$slug,
                'title'         => __('Site is currently down', 'wpshadow'),
                'description'   => sprintf(
                    __('External check failed: %s. Visitors cannot access your site.', 'wpshadow'),
                    $response->get_error_message()
                ),
                'severity'      => 'critical',
                'category'      => 'monitoring',
                'kb_link'       => 'https://wpshadow.com/kb/site-down/',
                'training_link' => 'https://wpshadow.com/training/site-down/',
                'auto_fixable'  => false,
                'threat_level'  => 100,
            );
        }
        
        $code = wp_remote_retrieve_response_code($response);
        if ($code >= 500) {
            return array(
                'id'            => static::$slug,
                'title'         => sprintf(__('Site returns server error (HTTP %d)', 'wpshadow'), $code),
                'description'   => __('Your server is experiencing errors. Visitors may see error pages.', 'wpshadow'),
                'severity'      => 'critical',
                'category'      => 'monitoring',
                'kb_link'       => 'https://wpshadow.com/kb/site-down/',
                'training_link' => 'https://wpshadow.com/training/site-down/',
                'auto_fixable'  => false,
                'threat_level'  => 95,
            );
        }
        
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