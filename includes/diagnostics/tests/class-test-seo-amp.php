<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_SEO_AMP extends Diagnostic_Base {
    
    protected static $slug = 'test-seo-amp';
    protected static $title = 'AMP Configuration Test';
    protected static $description = 'Tests for AMP (Accelerated Mobile Pages) implementation';
    
    public static function check(?string $url = null, ?string $html = null): ?array {
        if ($html !== null) {
            return self::analyze_html($html, $url ?? 'provided-html');
        }
        
        $html = self::fetch_html($url ?? home_url('/'));
        if ($html === false) {
            return null;
        }
        
        return self::analyze_html($html, $url ?? home_url('/'));
    }
    
    protected static function analyze_html(string $html, string $checked_url): ?array {
        // Check for AMP link
        $has_amp_link = preg_match('/<link[^>]+rel=["\']amphtml["\']/i', $html);
        
        // Check if page is AMP
        $is_amp = preg_match('/<html[^>]+amp|<html[^>]+⚡/i', $html);
        
        if ($has_amp_link || $is_amp) {
            // AMP is implemented, check for validation
            // We can't fully validate AMP here, but we can check for common issues
            
            if ($is_amp) {
                // Check for required AMP elements
                $has_amp_boilerplate = preg_match('/<style amp-boilerplate>/i', $html);
                $has_amp_script = preg_match('/<script[^>]+src=["\'][^"\']*ampproject/i', $html);
                
                if (!$has_amp_boilerplate || !$has_amp_script) {
                    return [
                        'id' => 'seo-amp-incomplete',
                        'title' => 'AMP Implementation Incomplete',
                        'description' => 'AMP page detected but missing required elements (amp-boilerplate or amp script). Invalid AMP pages won\'t get AMP benefits in search.',
                        'color' => '#ff9800',
                        'bg_color' => '#fff3e0',
                        'kb_link' => 'https://wpshadow.com/kb/amp-implementation/',
                        'training_link' => 'https://wpshadow.com/training/amp/',
                        'auto_fixable' => false,
                        'threat_level' => 40,
                        'module' => 'SEO',
                        'priority' => 2,
                        'meta' => ['is_amp' => true, 'has_boilerplate' => $has_amp_boilerplate, 'has_script' => $has_amp_script],
                    ];
                }
            }
        }
        
        return null;
    }
    
    protected static function fetch_html(string $url) {
        $response = wp_remote_get($url, ['timeout' => 10, 'sslverify' => false]);
        return is_wp_error($response) ? false : wp_remote_retrieve_body($response);
    }
    
    public static function get_name(): string {
        return __('AMP Configuration', 'wpshadow');
    }
    
    public static function get_description(): string {
        return __('Checks for AMP implementation and validity.', 'wpshadow');
    }
}
