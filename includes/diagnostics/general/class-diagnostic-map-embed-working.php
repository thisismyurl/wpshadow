<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Google Maps Embedded?
 * 
 * Target Persona: Local Business Owner (Bakery/Plumber/Insurance)
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
 */
class Diagnostic_Map_Embed_Working extends Diagnostic_Base {
    protected static $slug = 'map-embed-working';
    protected static $title = 'Google Maps Embedded?';
    protected static $description = 'Checks if location map is embedded and working.';

    public static function check(): ?array {
        $pages = get_posts(array(
            'post_type' => array('page', 'post'),
            'posts_per_page' => -1,
            'post_status' => 'publish',
        ));
        
        $has_map = false;
        foreach ($pages as $page) {
            if (stripos($page->post_content, 'maps.google.com') !== false ||
                stripos($page->post_content, 'google.com/maps') !== false ||
                preg_match('/<iframe[^>]+maps/', $page->post_content)) {
                $has_map = true;
                break;
            }
        }
        
        if ($has_map) {
            return null;
        }
        
        return array(
            'id'            => static::$slug,
            'title'         => __('No location map found', 'wpshadow'),
            'description'   => __('Local businesses benefit from embedding a Google Map. Customers need to find you easily.', 'wpshadow'),
            'severity'      => 'low',
            'category'      => 'general',
            'kb_link'       => 'https://wpshadow.com/kb/map-embed-working/',
            'training_link' => 'https://wpshadow.com/training/map-embed-working/',
            'auto_fixable'  => false,
            'threat_level'  => 30,
        );
    }

    /**
     * IMPLEMENTATION PLAN (Local Business Owner (Bakery/Plumber/Insurance))
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