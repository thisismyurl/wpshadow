<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_SEO_Pagination extends Diagnostic_Base {
    
    protected static $slug = 'test-seo-pagination';
    protected static $title = 'Pagination SEO Test';
    protected static $description = 'Tests for proper pagination links (rel=next/prev)';
    
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
        // Check if page has pagination
        $has_pagination = preg_match('/page\/[0-9]+|paged=[0-9]+|\?page=[0-9]+/i', $checked_url) ||
                         preg_match('/<a[^>]*class=["\'][^"\']*page-numbers|pagination/i', $html);
        
        if (!$has_pagination) {
            return null; // No pagination to check
        }
        
        // Check for rel=next/prev links
        $has_rel_next = preg_match('/<link[^>]+rel=["\']next["\']/i', $html);
        $has_rel_prev = preg_match('/<link[^>]+rel=["\']prev["\']/i', $html);
        
        if (!$has_rel_next && !$has_rel_prev) {
            return [
                'id' => 'seo-pagination-missing-rel',
                'title' => 'Pagination Missing rel=next/prev',
                'description' => 'Paginated content detected but no rel=next/prev links found. These links help search engines understand the relationship between paginated pages.',
                'color' => '#2196f3',
                'bg_color' => '#e3f2fd',
                'kb_link' => 'https://wpshadow.com/kb/pagination-seo/',
                'training_link' => 'https://wpshadow.com/training/technical-seo/',
                'auto_fixable' => false,
                'threat_level' => 35,
                'module' => 'SEO',
                'priority' => 3,
                'meta' => ['has_pagination' => true, 'has_rel_links' => false, 'checked_url' => $checked_url],
            ];
        }
        
        return null;
    }
    
    protected static function fetch_html(string $url) {
        $response = wp_remote_get($url, ['timeout' => 10, 'sslverify' => false]);
        return is_wp_error($response) ? false : wp_remote_retrieve_body($response);
    }
    
    public static function get_name(): string {
        return __('Pagination SEO', 'wpshadow');
    }
    
    public static function get_description(): string {
        return __('Checks for proper pagination links (rel=next/prev).', 'wpshadow');
    }
}
