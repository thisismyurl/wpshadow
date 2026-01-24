<?php
declare(strict_types=1);
/**
 * Test: Open Graph Tags Check
 *
 * Tests if HTML contains proper Open Graph meta tags for social sharing.
 * 
 * Philosophy: Show value (#9), educate (#5) - Help users optimize social sharing
 * 
 * @package WPShadow
 */

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_SEO_Open_Graph_Tags extends Diagnostic_Base {
    
    protected static $slug = 'test-seo-open-graph-tags';
    protected static $title = 'Open Graph Tags Test';
    protected static $description = 'Tests for missing or incomplete Open Graph tags';
    
    /**
     * Required OG tags
     */
    const REQUIRED_TAGS = ['og:title', 'og:type', 'og:url', 'og:image'];
    const RECOMMENDED_TAGS = ['og:description', 'og:site_name'];
    
    /**
     * Run the diagnostic check
     *
     * PASS (returns null): All required OG tags present
     * FAIL (returns array): Missing required tags or recommended tags
     *
     * @param string|null $url URL to test (defaults to homepage)
     * @param string|null $html Pre-fetched HTML to analyze
     * @return array|null Finding data or null if no issue
     */
    public static function check(?string $url = null, ?string $html = null): ?array {
        if ($html !== null) {
            return self::analyze_html($html, $url ?? 'provided-html');
        }
        
        $site_url = $url ?? home_url('/');
        
        if ($url !== null && !self::is_internal_url($url)) {
            return self::error_result('Invalid URL', 'URL must be from this WordPress site');
        }
        
        $html = self::fetch_html($site_url);
        if ($html === false) {
            return self::error_result('Fetch Failed', 'Could not retrieve page HTML');
        }
        
        return self::analyze_html($html, $site_url);
    }
    
    /**
     * Run comprehensive Open Graph tests
     *
     * @param string|null $url URL to test
     * @param string|null $html Pre-fetched HTML
     * @return array Test results
     */
    public static function run_og_tests(?string $url = null, ?string $html = null): array {
        $html = $html ?? self::fetch_html($url ?? home_url('/'));
        
        if ($html === false) {
            return [
                'success' => false,
                'error' => 'Could not fetch HTML',
                'url' => $url ?? home_url('/'),
            ];
        }
        
        $og_tags = self::extract_og_tags($html);
        
        return [
            'success' => true,
            'url' => $url ?? home_url('/'),
            'og_tags' => $og_tags,
            'tests' => [
                'has_og_title' => self::test_has_og_title($html),
                'has_og_type' => self::test_has_og_type($html),
                'has_og_url' => self::test_has_og_url($html),
                'has_og_image' => self::test_has_og_image($html),
                'has_og_description' => self::test_has_og_description($html),
                'has_og_site_name' => self::test_has_og_site_name($html),
            ],
            'summary' => [
                'required_present' => self::count_required_tags($og_tags),
                'required_total' => count(self::REQUIRED_TAGS),
                'recommended_present' => self::count_recommended_tags($og_tags),
                'recommended_total' => count(self::RECOMMENDED_TAGS),
                'passed' => self::has_all_required($og_tags),
            ],
        ];
    }
    
    /**
     * Test for og:title
     */
    public static function test_has_og_title(?string $url = null, ?string $html = null): array {
        $html = $html ?? self::fetch_html($url ?? home_url('/'));
        $og_tags = self::extract_og_tags($html);
        
        return [
            'test' => 'has_og_title',
            'passed' => isset($og_tags['og:title']) && !empty($og_tags['og:title']),
            'value' => $og_tags['og:title'] ?? null,
            'message' => isset($og_tags['og:title']) 
                ? 'og:title present'
                : 'og:title missing',
            'impact' => 'Title shown when page is shared on social media',
        ];
    }
    
    /**
     * Test for og:type
     */
    public static function test_has_og_type(?string $url = null, ?string $html = null): array {
        $html = $html ?? self::fetch_html($url ?? home_url('/'));
        $og_tags = self::extract_og_tags($html);
        
        return [
            'test' => 'has_og_type',
            'passed' => isset($og_tags['og:type']) && !empty($og_tags['og:type']),
            'value' => $og_tags['og:type'] ?? null,
            'message' => isset($og_tags['og:type']) 
                ? 'og:type present'
                : 'og:type missing',
            'impact' => 'Tells social platforms what type of content this is',
        ];
    }
    
    /**
     * Test for og:url
     */
    public static function test_has_og_url(?string $url = null, ?string $html = null): array {
        $html = $html ?? self::fetch_html($url ?? home_url('/'));
        $og_tags = self::extract_og_tags($html);
        
        return [
            'test' => 'has_og_url',
            'passed' => isset($og_tags['og:url']) && !empty($og_tags['og:url']),
            'value' => $og_tags['og:url'] ?? null,
            'message' => isset($og_tags['og:url']) 
                ? 'og:url present'
                : 'og:url missing',
            'impact' => 'Canonical URL for social sharing',
        ];
    }
    
    /**
     * Test for og:image
     */
    public static function test_has_og_image(?string $url = null, ?string $html = null): array {
        $html = $html ?? self::fetch_html($url ?? home_url('/'));
        $og_tags = self::extract_og_tags($html);
        
        return [
            'test' => 'has_og_image',
            'passed' => isset($og_tags['og:image']) && !empty($og_tags['og:image']),
            'value' => $og_tags['og:image'] ?? null,
            'message' => isset($og_tags['og:image']) 
                ? 'og:image present'
                : 'og:image missing',
            'impact' => 'Image shown in social media previews (critical for engagement)',
        ];
    }
    
    /**
     * Test for og:description (recommended)
     */
    public static function test_has_og_description(?string $url = null, ?string $html = null): array {
        $html = $html ?? self::fetch_html($url ?? home_url('/'));
        $og_tags = self::extract_og_tags($html);
        
        return [
            'test' => 'has_og_description',
            'passed' => isset($og_tags['og:description']) && !empty($og_tags['og:description']),
            'value' => $og_tags['og:description'] ?? null,
            'message' => isset($og_tags['og:description']) 
                ? 'og:description present (recommended)'
                : 'og:description missing (recommended)',
            'impact' => 'Description shown in social media previews',
        ];
    }
    
    /**
     * Test for og:site_name (recommended)
     */
    public static function test_has_og_site_name(?string $url = null, ?string $html = null): array {
        $html = $html ?? self::fetch_html($url ?? home_url('/'));
        $og_tags = self::extract_og_tags($html);
        
        return [
            'test' => 'has_og_site_name',
            'passed' => isset($og_tags['og:site_name']) && !empty($og_tags['og:site_name']),
            'value' => $og_tags['og:site_name'] ?? null,
            'message' => isset($og_tags['og:site_name']) 
                ? 'og:site_name present (recommended)'
                : 'og:site_name missing (recommended)',
            'impact' => 'Site name shown alongside shared content',
        ];
    }
    
    /**
     * Analyze HTML for Open Graph issues
     *
     * @param string $html HTML content
     * @param string $checked_url URL that was checked
     * @return array|null Finding or null
     */
    protected static function analyze_html(string $html, string $checked_url): ?array {
        $og_tags = self::extract_og_tags($html);
        $missing_required = [];
        $missing_recommended = [];
        
        foreach (self::REQUIRED_TAGS as $tag) {
            if (!isset($og_tags[$tag]) || empty($og_tags[$tag])) {
                $missing_required[] = $tag;
            }
        }
        
        foreach (self::RECOMMENDED_TAGS as $tag) {
            if (!isset($og_tags[$tag]) || empty($og_tags[$tag])) {
                $missing_recommended[] = $tag;
            }
        }
        
        // Perfect: all required and recommended tags present
        if (empty($missing_required) && empty($missing_recommended)) {
            return null; // PASS
        }
        
        // Missing required tags = high severity
        if (!empty($missing_required)) {
            return [
                'id' => 'seo-open-graph-tags',
                'title' => 'Missing Required Open Graph Tags',
                'description' => sprintf(
                    'Your page is missing %d required Open Graph tag(s): %s. These tags control how your content appears when shared on social media.',
                    count($missing_required),
                    implode(', ', $missing_required)
                )
                'kb_link' => 'https://wpshadow.com/kb/open-graph-tags/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=diagnostic',
                'training_link' => 'https://wpshadow.com/training/social-media-seo/',
                'auto_fixable' => false,
                'threat_level' => 70,
                'module' => 'SEO',
                'priority' => 1,
                'meta' => [
                    'missing_required' => $missing_required,
                    'missing_recommended' => $missing_recommended,
                    'present_tags' => $og_tags,
                    'checked_url' => $checked_url,
                ],
            ];
        }
        
        // Only missing recommended = lower severity
        return [
            'id' => 'seo-open-graph-tags',
            'title' => 'Missing Recommended Open Graph Tags',
            'description' => sprintf(
                'Your page has required Open Graph tags but is missing %d recommended tag(s): %s. Adding these will improve social media appearance.',
                count($missing_recommended),
                implode(', ', $missing_recommended)
            )
            'kb_link' => 'https://wpshadow.com/kb/open-graph-tags/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=diagnostic',
            'training_link' => 'https://wpshadow.com/training/social-media-seo/',
            'auto_fixable' => false,
            'threat_level' => 40,
            'module' => 'SEO',
            'priority' => 2,
            'meta' => [
                'missing_recommended' => $missing_recommended,
                'present_tags' => $og_tags,
                'checked_url' => $checked_url,
            ],
        ];
    }
    
    /**
     * Extract all Open Graph tags from HTML
     *
     * @param string $html HTML content
     * @return array OG tags
     */
    protected static function extract_og_tags(string $html): array {
        if (empty($html)) {
            return [];
        }
        
        $og_tags = [];
        preg_match_all('/<meta\s+property=["\']og:([^"\']+)["\']\s+content=["\'](.*?)["\']/i', $html, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            $property = 'og:' . $match[1];
            $content = html_entity_decode($match[2], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $og_tags[$property] = $content;
        }
        
        return $og_tags;
    }
    
    /**
     * Count present required tags
     *
     * @param array $og_tags OG tags
     * @return int Count
     */
    protected static function count_required_tags(array $og_tags): int {
        $count = 0;
        foreach (self::REQUIRED_TAGS as $tag) {
            if (isset($og_tags[$tag]) && !empty($og_tags[$tag])) {
                $count++;
            }
        }
        return $count;
    }
    
    /**
     * Count present recommended tags
     *
     * @param array $og_tags OG tags
     * @return int Count
     */
    protected static function count_recommended_tags(array $og_tags): int {
        $count = 0;
        foreach (self::RECOMMENDED_TAGS as $tag) {
            if (isset($og_tags[$tag]) && !empty($og_tags[$tag])) {
                $count++;
            }
        }
        return $count;
    }
    
    /**
     * Check if all required tags present
     *
     * @param array $og_tags OG tags
     * @return bool
     */
    protected static function has_all_required(array $og_tags): bool {
        foreach (self::REQUIRED_TAGS as $tag) {
            if (!isset($og_tags[$tag]) || empty($og_tags[$tag])) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Fetch HTML from URL
     *
     * @param string $url URL to fetch
     * @return string|false HTML or false on error
     */
    protected static function fetch_html(string $url) {
        $response = wp_remote_get($url, [
            'timeout' => 10,
            'user-agent' => 'WPShadow-Diagnostic/1.0 (SEO Checker)',
            'sslverify' => false,
        ]);
        
        if (is_wp_error($response)) {
            return false;
        }
        
        return wp_remote_retrieve_body($response);
    }
    
    /**
     * Check if URL is internal
     *
     * @param string $url URL to check
     * @return bool
     */
    protected static function is_internal_url(string $url): bool {
        $site_host = wp_parse_url(home_url(), PHP_URL_HOST);
        $test_host = wp_parse_url($url, PHP_URL_HOST);
        return $site_host === $test_host;
    }
    
    /**
     * Generate error result
     *
     * @param string $title Error title
     * @param string $description Error description
     * @return array Error result
     */
    protected static function error_result(string $title, string $description): array {
        return [
            'id' => 'seo-open-graph-tags',
            'title' => $title,
            'description' => $description
            'kb_link' => 'https://wpshadow.com/kb/open-graph-tags/',
            'training_link' => 'https://wpshadow.com/training/social-media-seo/',
            'auto_fixable' => false,
            'threat_level' => 30,
            'module' => 'SEO',
            'priority' => 3,
        ];
    }
    
    /**
     * Get the name for display
     *
     * @return string
     */
    public static function get_name(): string {
        return __('Open Graph Tags Check', 'wpshadow');
    }
    
    /**
     * Get the description for display
     *
     * @return string
     */
    public static function get_description(): string {
        return __('Checks HTML for proper Open Graph tags for social media sharing.', 'wpshadow');
    }
}
