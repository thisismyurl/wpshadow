<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Mobile_Performance extends Diagnostic_Base {
    
    protected static $slug = 'test-mobile-performance';
    protected static $title = 'Mobile Performance Test';
    protected static $description = 'Tests for mobile-specific performance issues';
    
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
        // Check for large images (not using responsive techniques)
        preg_match_all('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $html, $images);
        $total_images = count($images[0]);
        
        // Count images without srcset (not responsive)
        $non_responsive = 0;
        foreach ($images[0] as $img_tag) {
            if (strpos($img_tag, 'srcset') === false) {
                $non_responsive++;
            }
        }
        
        // Check for render-blocking resources
        preg_match_all('/<link[^>]+rel=["\']stylesheet["\'][^>]*>/i', $html, $stylesheets);
        $blocking_css = 0;
        foreach ($stylesheets[0] as $stylesheet) {
            if (strpos($stylesheet, 'media=') === false || preg_match('/media=["\']all["\']/i', $stylesheet)) {
                $blocking_css++;
            }
        }
        
        // Check for synchronous scripts in head
        preg_match('/<head[^>]*>(.*?)<\/head>/is', $html, $head_match);
        $head_html = $head_match[1] ?? '';
        preg_match_all('/<script[^>]*(?!async|defer)[^>]*>/i', $head_html, $blocking_scripts);
        $blocking_script_count = count($blocking_scripts[0]);
        
        $issues = [];
        if ($non_responsive > $total_images * 0.5) {
            $issues[] = sprintf('%d/%d images not responsive', $non_responsive, $total_images);
        }
        if ($blocking_css > 3) {
            $issues[] = sprintf('%d blocking stylesheets', $blocking_css);
        }
        if ($blocking_script_count > 2) {
            $issues[] = sprintf('%d blocking scripts', $blocking_script_count);
        }
        
        if (!empty($issues)) {
            return [
                'id' => 'mobile-performance-issues',
                'title' => 'Mobile Performance Issues Detected',
                'description' => 'Mobile performance issues: ' . implode(', ', $issues) . '. These can significantly impact mobile user experience and Core Web Vitals.',
                'color' => '#ff9800',
                'bg_color' => '#fff3e0',
                'kb_link' => 'https://wpshadow.com/kb/mobile-performance/',
                'training_link' => 'https://wpshadow.com/training/mobile-optimization/',
                'auto_fixable' => false,
                'threat_level' => 50,
                'module' => 'Performance',
                'priority' => 2,
                'meta' => [
                    'non_responsive_images' => $non_responsive,
                    'total_images' => $total_images,
                    'blocking_css' => $blocking_css,
                    'blocking_scripts' => $blocking_script_count,
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
        return __('Mobile Performance', 'wpshadow');
    }
    
    public static function get_description(): string {
        return __('Checks for mobile-specific performance issues.', 'wpshadow');
    }
}
