<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_UX_Heading_Hierarchy extends Diagnostic_Base {
    
    protected static $slug = 'test-ux-heading-hierarchy-visual';
    protected static $title = 'Visual Heading Hierarchy Test';
    protected static $description = 'Tests that heading sizes match semantic hierarchy';
    
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
        // Check for CSS that makes lower-level headings larger than higher ones
        $size_violations = 0;
        
        // Extract heading styles (simplified check)
        if (preg_match('/h4[^}]*font-size:\s*([0-9.]+)(px|em|rem)/i', $html, $h4_size) &&
            preg_match('/h2[^}]*font-size:\s*([0-9.]+)(px|em|rem)/i', $html, $h2_size)) {
            
            $h4_val = (float)$h4_size[1];
            $h2_val = (float)$h2_size[1];
            
            // If h4 is larger than h2, that's wrong
            if ($h4_val > $h2_val) {
                $size_violations++;
            }
        }
        
        // Check for inline styles that might override hierarchy
        preg_match_all('/<h([1-6])[^>]*style=["\']([^"\']+)["\']/i', $html, $inline_heading_styles);
        
        if (!empty($inline_heading_styles[0])) {
            // Parse font sizes from inline styles (complex, simplified here)
            $inline_violations = count($inline_heading_styles[0]);
            if ($inline_violations > 3) {
                $size_violations += $inline_violations;
            }
        }
        
        if ($size_violations > 2) {
            return [
                'id' => 'ux-heading-visual-hierarchy',
                'title' => 'Visual Heading Hierarchy Mismatched',
                'description' => sprintf(
                    'Found %d instances where visual heading sizes may not match semantic hierarchy (h4 larger than h2, etc). Visual hierarchy should reinforce semantic structure.',
                    $size_violations
                ),
                'color' => '#ff9800',
                'bg_color' => '#fff3e0',
                'kb_link' => 'https://wpshadow.com/kb/heading-hierarchy/',
                'training_link' => 'https://wpshadow.com/training/typography/',
                'auto_fixable' => false,
                'threat_level' => 30,
                'module' => 'UX',
                'priority' => 3,
                'meta' => [
                    'size_violations' => $size_violations,
                    'checked_url' => $checked_url,
                ],
            ];
        }
        
        return null;
    }
    
    protected static function fetch_html(string $url) {
        $response = wp_remote_get($url, ['timeout' => 10, 'sslverify' => false]);
        return is_wp_error($response) ? false : wp_remote_retrieve_body($response);
    }
    
    public static function get_name(): string {
        return __('Visual Heading Hierarchy', 'wpshadow');
    }
    
    public static function get_description(): string {
        return __('Checks that heading sizes match semantic hierarchy.', 'wpshadow');
    }
}
