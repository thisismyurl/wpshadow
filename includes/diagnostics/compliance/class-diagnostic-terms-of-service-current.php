<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Terms of Service Current?
 * 
 * Target Persona: Enterprise IT/Compliance Team
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Terms_Of_Service_Current extends Diagnostic_Base {
    protected static $slug = 'terms-of-service-current';
    protected static $title = 'Terms of Service Current?';
    protected static $description = 'Checks if ToS updated within 12 months.';

    public static function check(): ?array {
        // Look for common ToS page titles/slugs
        $tos_page = get_page_by_path('terms-of-service');
        if (!$tos_page) {
            $tos_page = get_page_by_path('terms');
        }
        if (!$tos_page) {
            $tos_page = get_page_by_path('tos');
        }
        
        // Also search by title
        if (!$tos_page) {
            $query = new \WP_Query(array(
                'post_type' => 'page',
                'post_status' => 'publish',
                's' => 'terms of service',
                'posts_per_page' => 1,
            ));
            if ($query->have_posts()) {
                $tos_page = $query->posts[0];
            }
        }
        
        if (!$tos_page) {
            return array(
                'id'            => static::$slug,
                'title'         => static::$title,
                'description'   => 'No Terms of Service page found.',
                'color'         => '#ff9800',
                'bg_color'      => '#fff3e0',
                'kb_link'       => 'https://wpshadow.com/kb/terms-of-service-current/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=terms-of-service-current',
                'training_link' => 'https://wpshadow.com/training/terms-of-service-current/',
                'auto_fixable'  => false,
                'threat_level'  => 60,
                'module'        => 'Compliance',
                'priority'      => 2,
            );
        }
        
        // Check if updated within 12 months
        $last_modified = strtotime($tos_page->post_modified);
        $twelve_months_ago = strtotime('-12 months');
        
        if ($last_modified >= $twelve_months_ago) {
            return null; // Pass - ToS is current
        }
        
        $months_old = floor((time() - $last_modified) / (30 * 24 * 60 * 60));
        
        return array(
            'id'            => static::$slug,
            'title'         => static::$title,
            'description'   => "Terms of Service last updated {$months_old} months ago (recommend annual review).",
            'color'         => '#ff9800',
            'bg_color'      => '#fff3e0',
            'kb_link'       => 'https://wpshadow.com/kb/terms-of-service-current/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=terms-of-service-current',
            'training_link' => 'https://wpshadow.com/training/terms-of-service-current/',
            'auto_fixable'  => false,
            'threat_level'  => 60,
            'module'        => 'Compliance',
            'priority'      => 2,
        );
    }

}