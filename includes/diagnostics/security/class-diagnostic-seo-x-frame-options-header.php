<?php
declare(strict_types=1);
/**
 * X-Frame-Options Header Diagnostic
 *
 * Philosophy: Prevent clickjacking attacks
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_X_Frame_Options_Header extends Diagnostic_Base {
    public static function check(): ?array {
        // Check for X-Frame-Options header
        $response = wp_remote_head(home_url(), array('timeout' => 10));
        
        if (is_wp_error($response)) {
            return null;
        }
        
        $headers = wp_remote_retrieve_headers($response);
        
        if (isset($headers['X-Frame-Options']) || isset($headers['x-frame-options'])) {
            return null;
        }
        
        return [
            'id' => 'seo-x-frame-options-header',
            'title' => 'X-Frame-Options Header Missing',
            'description' => 'X-Frame-Options header not set. Add to prevent clickjacking attacks.',
            'severity' => 'medium',
            'category' => 'security',
            'kb_link' => 'https://wpshadow.com/kb/x-frame-options/',
            'training_link' => 'https://wpshadow.com/training/clickjacking-prevention/',
            'auto_fixable' => true,
            'threat_level' => 50,
        ];
    }

}