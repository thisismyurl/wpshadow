<?php
declare(strict_types=1);
/**
 * Test: Missing Image Alt Text
 *
 * Tests if HTML images have proper alt attributes for accessibility and SEO.
 * 
 * Philosophy: Inspire confidence (#8), educate (#5) - Help users create accessible sites
 * 
 * @package WPShadow
 */

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Accessibility_Missing_Image_Alt extends Diagnostic_Base {
    
    protected static $slug = 'test-accessibility-missing-image-alt';
    protected static $title = 'Image Alt Text Test';
    protected static $description = 'Tests for images missing alt attributes';
    
    /**
     * Run the diagnostic check
     *
     * PASS (returns null): All images have alt attributes
     * FAIL (returns array): Some images missing alt or have empty alt
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
     * Run comprehensive image alt tests
     *
     * @param string|null $url URL to test
     * @param string|null $html Pre-fetched HTML
     * @return array Test results
     */
    public static function run_alt_tests(?string $url = null, ?string $html = null): array {
        $html = $html ?? self::fetch_html($url ?? home_url('/'));
        
        if ($html === false) {
            return [
                'success' => false,
                'error' => 'Could not fetch HTML',
                'url' => $url ?? home_url('/'),
            ];
        }
        
        $analysis = self::analyze_images($html);
        
        return [
            'success' => true,
            'url' => $url ?? home_url('/'),
            'total_images' => $analysis['total'],
            'images_with_alt' => $analysis['with_alt'],
            'images_without_alt' => $analysis['without_alt'],
            'images_empty_alt' => $analysis['empty_alt'],
            'tests' => [
                'all_have_alt' => self::test_all_have_alt($html),
                'no_empty_alt' => self::test_no_empty_alt($html),
                'decorative_properly_marked' => self::test_decorative_images($html),
            ],
            'summary' => [
                'passed' => $analysis['without_alt'] === 0,
                'compliance_rate' => $analysis['total'] > 0 
                    ? round(($analysis['with_alt'] / $analysis['total']) * 100, 1)
                    : 100,
            ],
            'problematic_images' => $analysis['problematic'],
        ];
    }
    
    /**
     * Test if all images have alt attributes
     *
     * @param string|null $url URL to test
     * @param string|null $html Pre-fetched HTML
     * @return array Test result
     */
    public static function test_all_have_alt(?string $url = null, ?string $html = null): array {
        $html = $html ?? self::fetch_html($url ?? home_url('/'));
        $analysis = self::analyze_images($html);
        
        return [
            'test' => 'all_have_alt',
            'passed' => $analysis['without_alt'] === 0,
            'total_images' => $analysis['total'],
            'missing_count' => $analysis['without_alt'],
            'message' => $analysis['without_alt'] === 0 
                ? 'All images have alt attributes'
                : sprintf('%d images missing alt attributes', $analysis['without_alt']),
            'impact' => 'Alt text is crucial for screen readers and SEO',
        ];
    }
    
    /**
     * Test for empty alt attributes (non-decorative)
     *
     * @param string|null $url URL to test
     * @param string|null $html Pre-fetched HTML
     * @return array Test result
     */
    public static function test_no_empty_alt(?string $url = null, ?string $html = null): array {
        $html = $html ?? self::fetch_html($url ?? home_url('/'));
        $analysis = self::analyze_images($html);
        
        return [
            'test' => 'no_empty_alt',
            'passed' => $analysis['suspicious_empty'] === 0,
            'empty_count' => $analysis['empty_alt'],
            'suspicious_count' => $analysis['suspicious_empty'],
            'message' => $analysis['suspicious_empty'] === 0 
                ? 'No suspicious empty alt attributes'
                : sprintf('%d images have empty alt (may not be decorative)', $analysis['suspicious_empty']),
            'impact' => 'Empty alt should only be used for purely decorative images',
        ];
    }
    
    /**
     * Test if decorative images are properly marked
     *
     * @param string|null $url URL to test
     * @param string|null $html Pre-fetched HTML
     * @return array Test result
     */
    public static function test_decorative_images(?string $url = null, ?string $html = null): array {
        $html = $html ?? self::fetch_html($url ?? home_url('/'));
        $analysis = self::analyze_images($html);
        
        return [
            'test' => 'decorative_properly_marked',
            'passed' => true, // Informational test
            'empty_alt_count' => $analysis['empty_alt'],
            'message' => $analysis['empty_alt'] > 0 
                ? sprintf('%d images have empty alt (verify these are decorative)', $analysis['empty_alt'])
                : 'No images marked as decorative',
            'impact' => 'Decorative images should have alt="" to reduce screen reader noise',
        ];
    }
    
    /**
     * Analyze HTML for image alt issues
     *
     * @param string $html HTML content
     * @param string $checked_url URL that was checked
     * @return array|null Finding or null
     */
    protected static function analyze_html(string $html, string $checked_url): ?array {
        $analysis = self::analyze_images($html);
        
        // Perfect: all images have alt
        if ($analysis['without_alt'] === 0 && $analysis['suspicious_empty'] === 0) {
            return null; // PASS
        }
        
        // Calculate severity
        $total = $analysis['total'];
        $missing = $analysis['without_alt'] + $analysis['suspicious_empty'];
        $percentage = $total > 0 ? round(($missing / $total) * 100) : 0;
        
        $threat_level = 40;
        if ($percentage > 50) {
            $threat_level = 80;
        } elseif ($percentage > 25) {
            $threat_level = 60;
        }
        
        return [
            'id' => 'accessibility-missing-image-alt',
            'title' => 'Images Missing Alt Text',
            'description' => sprintf(
                '%d of %d images are missing alt attributes or have suspicious empty alt. This hurts accessibility and SEO.',
                $missing,
                $total
            )
            'kb_link' => 'https://wpshadow.com/kb/image-alt-text/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=diagnostic',
            'training_link' => 'https://wpshadow.com/training/accessibility-images/',
            'auto_fixable' => false,
            'threat_level' => $threat_level,
            'module' => 'Accessibility',
            'priority' => 1,
            'meta' => [
                'total_images' => $total,
                'missing_alt' => $analysis['without_alt'],
                'empty_alt' => $analysis['empty_alt'],
                'suspicious_empty' => $analysis['suspicious_empty'],
                'compliance_rate' => $total > 0 ? round((($total - $missing) / $total) * 100, 1) : 100,
                'problematic_images' => array_slice($analysis['problematic'], 0, 10), // First 10
                'checked_url' => $checked_url,
            ],
        ];
    }
    
    /**
     * Analyze all images in HTML
     *
     * @param string $html HTML content
     * @return array Analysis results
     */
    protected static function analyze_images(string $html): array {
        if (empty($html)) {
            return [
                'total' => 0,
                'with_alt' => 0,
                'without_alt' => 0,
                'empty_alt' => 0,
                'suspicious_empty' => 0,
                'problematic' => [],
            ];
        }
        
        preg_match_all('/<img\s+([^>]+)>/i', $html, $matches);
        $images = $matches[1] ?? [];
        
        $total = count($images);
        $with_alt = 0;
        $without_alt = 0;
        $empty_alt = 0;
        $suspicious_empty = 0;
        $problematic = [];
        
        foreach ($images as $img_attrs) {
            // Check for alt attribute
            if (preg_match('/alt\s*=\s*["\']([^"\']*)["\']/', $img_attrs, $alt_match)) {
                $alt_text = trim($alt_match[1]);
                $with_alt++;
                
                if (empty($alt_text)) {
                    $empty_alt++;
                    // Check if likely decorative (look for role or class hints)
                    if (!preg_match('/(role\s*=\s*["\']presentation["\']|class\s*=\s*["\'][^"\']*decorative)/i', $img_attrs)) {
                        // Not obviously decorative, may be suspicious
                        if (self::is_likely_content_image($img_attrs)) {
                            $suspicious_empty++;
                            $problematic[] = [
                                'issue' => 'empty_alt',
                                'src' => self::extract_src($img_attrs),
                            ];
                        }
                    }
                }
            } else {
                $without_alt++;
                $problematic[] = [
                    'issue' => 'missing_alt',
                    'src' => self::extract_src($img_attrs),
                ];
            }
        }
        
        return [
            'total' => $total,
            'with_alt' => $with_alt,
            'without_alt' => $without_alt,
            'empty_alt' => $empty_alt,
            'suspicious_empty' => $suspicious_empty,
            'problematic' => $problematic,
        ];
    }
    
    /**
     * Check if image is likely content (not decorative)
     *
     * @param string $img_attrs Image attributes
     * @return bool
     */
    protected static function is_likely_content_image(string $img_attrs): bool {
        // Content images often have certain patterns
        $content_patterns = [
            '/wp-content\/uploads/',
            '/class\s*=\s*["\'][^"\']*attachment/',
            '/class\s*=\s*["\'][^"\']*wp-image/',
        ];
        
        foreach ($content_patterns as $pattern) {
            if (preg_match($pattern, $img_attrs)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Extract src from image attributes
     *
     * @param string $img_attrs Image attributes
     * @return string
     */
    protected static function extract_src(string $img_attrs): string {
        if (preg_match('/src\s*=\s*["\']([^"\']+)["\']/', $img_attrs, $src_match)) {
            return $src_match[1];
        }
        return 'unknown';
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
            'user-agent' => 'WPShadow-Diagnostic/1.0 (Accessibility Checker)',
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
            'id' => 'accessibility-missing-image-alt',
            'title' => $title,
            'description' => $description
            'kb_link' => 'https://wpshadow.com/kb/image-alt-text/',
            'training_link' => 'https://wpshadow.com/training/accessibility-images/',
            'auto_fixable' => false,
            'threat_level' => 30,
            'module' => 'Accessibility',
            'priority' => 3,
        ];
    }
    
    /**
     * Get the name for display
     *
     * @return string
     */
    public static function get_name(): string {
        return __('Image Alt Text Check', 'wpshadow');
    }
    
    /**
     * Get the description for display
     *
     * @return string
     */
    public static function get_description(): string {
        return __('Checks HTML images for proper alt attributes (accessibility and SEO).', 'wpshadow');
    }
}
