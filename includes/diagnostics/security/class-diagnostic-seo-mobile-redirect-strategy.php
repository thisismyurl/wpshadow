<?php
declare(strict_types=1);
/**
 * Mobile Redirect Strategy Diagnostic
 *
 * Philosophy: Separate mobile URLs need proper configuration
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Mobile_Redirect_Strategy extends Diagnostic_Base {
    public static function check(): ?array {
        // Check if site uses separate mobile URLs
        $site_url = get_site_url();
        $parsed = parse_url($site_url);
        
        if (!isset($parsed['host'])) {
            return null;
        }
        
        // Check if this is a mobile subdomain (m.example.com)
        if (strpos($parsed['host'], 'm.') === 0 || strpos($parsed['host'], 'mobile.') === 0) {
            // This IS a mobile URL, check for alternate link
            return [
                'id' => 'seo-mobile-redirect-strategy',
                'title' => 'Mobile URL Configuration Review',
                'description' => 'Using separate mobile subdomain. Ensure proper rel=alternate and rel=canonical tags.',
                'severity' => 'medium',
                'category' => 'seo',
                'kb_link' => 'https://wpshadow.com/kb/mobile-redirects/',
                'training_link' => 'https://wpshadow.com/training/mobile-url-structure/',
                'auto_fixable' => false,
                'threat_level' => 50,
            ];
        }
        
        // Most sites use responsive design now, not separate mobile URLs
        return null;
    }

}