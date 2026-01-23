<?php
declare(strict_types=1);
/**
 * Test: Image Lazy Loading Check
 *
 * Tests if images use lazy loading for better performance.
 * 
 * Philosophy: Show value (#9) - Help users improve page load performance
 * 
 * @package WPShadow
 */

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Performance_Image_Lazy_Loading extends Diagnostic_Base {
    
    protected static $slug = 'test-performance-image-lazy-loading';
    protected static $title = 'Image Lazy Loading Test';
    protected static $description = 'Tests for lazy loading attributes on images';
    
    /**
     * Images above this threshold should be lazy loaded
     */
    const LAZY_LOAD_THRESHOLD = 3;
    
    /**
     * Run the diagnostic check
     *
     * PASS (returns null): Images use lazy loading appropriately
     * FAIL (returns array): Missing lazy loading on eligible images
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
     * Run comprehensive lazy loading tests
     *
     * @param string|null $url URL to test
     * @param string|null $html Pre-fetched HTML
     * @return array Test results
     */
    public static function run_lazy_loading_tests(?string $url = null, ?string $html = null): array {
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
            'lazy_loaded' => $analysis['lazy_loaded'],
            'eager_loaded' => $analysis['eager_loaded'],
            'tests' => [
                'has_lazy_images' => self::test_has_lazy_images($html),
                'below_fold_lazy' => self::test_below_fold_lazy($html),
                'native_loading' => self::test_native_loading($html),
            ],
            'summary' => [
                'passed' => self::is_lazy_loading_optimal($analysis),
                'lazy_rate' => $analysis['total'] > 0 
                    ? round(($analysis['lazy_loaded'] / $analysis['total']) * 100, 1)
                    : 0,
                'recommendation' => self::get_recommendation($analysis),
            ],
        ];
    }
    
    /**
     * Test if any images use lazy loading
     */
    public static function test_has_lazy_images(?string $url = null, ?string $html = null): array {
        $html = $html ?? self::fetch_html($url ?? home_url('/'));
        $analysis = self::analyze_images($html);
        
        return [
            'test' => 'has_lazy_images',
            'passed' => $analysis['lazy_loaded'] > 0,
            'lazy_count' => $analysis['lazy_loaded'],
            'total_count' => $analysis['total'],
            'message' => $analysis['lazy_loaded'] > 0 
                ? sprintf('%d of %d images use lazy loading', $analysis['lazy_loaded'], $analysis['total'])
                : 'No images use lazy loading',
            'impact' => 'Lazy loading improves initial page load performance',
        ];
    }
    
    /**
     * Test if below-fold images are lazy loaded
     */
    public static function test_below_fold_lazy(?string $url = null, ?string $html = null): array {
        $html = $html ?? self::fetch_html($url ?? home_url('/'));
        $analysis = self::analyze_images($html);
        
        // Heuristic: Images after the 3rd one are likely below fold
        $should_lazy = max(0, $analysis['total'] - self::LAZY_LOAD_THRESHOLD);
        $could_improve = $should_lazy > $analysis['lazy_loaded'];
        
        return [
            'test' => 'below_fold_lazy',
            'passed' => !$could_improve,
            'should_lazy' => $should_lazy,
            'actually_lazy' => $analysis['lazy_loaded'],
            'message' => $could_improve 
                ? sprintf('%d more images could use lazy loading', $should_lazy - $analysis['lazy_loaded'])
                : 'Images appear to use lazy loading appropriately',
            'impact' => 'Below-fold images should lazy load to reduce initial load time',
        ];
    }
    
    /**
     * Test for native loading="lazy" attribute
     */
    public static function test_native_loading(?string $url = null, ?string $html = null): array {
        $html = $html ?? self::fetch_html($url ?? home_url('/'));
        $analysis = self::analyze_images($html);
        
        $uses_native = $analysis['native_lazy'] > 0;
        
        return [
            'test' => 'native_loading',
            'passed' => $uses_native,
            'native_count' => $analysis['native_lazy'],
            'message' => $uses_native 
                ? sprintf('%d images use native loading="lazy"', $analysis['native_lazy'])
                : 'No native loading="lazy" found (consider using it)',
            'impact' => 'Native lazy loading is widely supported and requires no JavaScript',
        ];
    }
    
    /**
     * Analyze HTML for lazy loading issues
     *
     * @param string $html HTML content
     * @param string $checked_url URL that was checked
     * @return array|null Finding or null
     */
    protected static function analyze_html(string $html, string $checked_url): ?array {
        $analysis = self::analyze_images($html);
        
        // No images = N/A (not a failure)
        if ($analysis['total'] === 0) {
            return null; // PASS (no images to optimize)
        }
        
        // Few images = probably fine
        if ($analysis['total'] <= self::LAZY_LOAD_THRESHOLD) {
            return null; // PASS (too few images to matter)
        }
        
        // Calculate how many should be lazy loaded (all except first 2-3)
        $should_lazy = $analysis['total'] - self::LAZY_LOAD_THRESHOLD;
        $lazy_rate = $analysis['total'] > 0 ? ($analysis['lazy_loaded'] / $analysis['total']) : 0;
        
        // Good lazy loading rate = PASS
        if ($lazy_rate >= 0.5 && $analysis['lazy_loaded'] >= $should_lazy * 0.7) {
            return null; // PASS (good enough)
        }
        
        // Low or no lazy loading = FAIL
        $missing = max(0, $should_lazy - $analysis['lazy_loaded']);
        
        $threat_level = 40;
        if ($analysis['lazy_loaded'] === 0 && $analysis['total'] > 10) {
            $threat_level = 60; // Many images, none lazy loaded
        }
        
        return [
            'id' => 'performance-image-lazy-loading',
            'title' => 'Images Missing Lazy Loading',
            'description' => sprintf(
                '%d of %d images could benefit from lazy loading. This would reduce initial page load time by deferring off-screen images.',
                $missing,
                $analysis['total']
            ),
            'color' => '#ff9800',
            'bg_color' => '#fff3e0',
            'kb_link' => 'https://wpshadow.com/kb/lazy-loading/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=diagnostic',
            'training_link' => 'https://wpshadow.com/training/performance-optimization/',
            'auto_fixable' => false,
            'threat_level' => $threat_level,
            'module' => 'Performance',
            'priority' => 2,
            'meta' => [
                'total_images' => $analysis['total'],
                'lazy_loaded' => $analysis['lazy_loaded'],
                'should_lazy' => $should_lazy,
                'missing_lazy' => $missing,
                'lazy_rate' => round($lazy_rate * 100, 1),
                'uses_native' => $analysis['native_lazy'] > 0,
                'checked_url' => $checked_url,
            ],
        ];
    }
    
    /**
     * Analyze all images for lazy loading
     *
     * @param string $html HTML content
     * @return array Analysis results
     */
    protected static function analyze_images(string $html): array {
        if (empty($html)) {
            return [
                'total' => 0,
                'lazy_loaded' => 0,
                'eager_loaded' => 0,
                'native_lazy' => 0,
            ];
        }
        
        preg_match_all('/<img\s+([^>]+)>/i', $html, $matches);
        $images = $matches[1] ?? [];
        
        $total = count($images);
        $lazy_loaded = 0;
        $native_lazy = 0;
        
        foreach ($images as $img_attrs) {
            // Check for loading="lazy" (native)
            if (preg_match('/loading\s*=\s*["\']lazy["\']/i', $img_attrs)) {
                $lazy_loaded++;
                $native_lazy++;
                continue;
            }
            
            // Check for data-src (JS lazy loading libraries)
            if (preg_match('/data-src\s*=/i', $img_attrs)) {
                $lazy_loaded++;
                continue;
            }
            
            // Check for lazy class names
            if (preg_match('/class\s*=\s*["\'][^"\']*\b(lazy|lazyload|lazy-load)\b/i', $img_attrs)) {
                $lazy_loaded++;
            }
        }
        
        return [
            'total' => $total,
            'lazy_loaded' => $lazy_loaded,
            'eager_loaded' => $total - $lazy_loaded,
            'native_lazy' => $native_lazy,
        ];
    }
    
    /**
     * Check if lazy loading is optimal
     *
     * @param array $analysis Analysis results
     * @return bool
     */
    protected static function is_lazy_loading_optimal(array $analysis): bool {
        if ($analysis['total'] <= self::LAZY_LOAD_THRESHOLD) {
            return true; // Too few images to matter
        }
        
        $should_lazy = $analysis['total'] - self::LAZY_LOAD_THRESHOLD;
        $lazy_rate = $analysis['total'] > 0 ? ($analysis['lazy_loaded'] / $analysis['total']) : 0;
        
        return $lazy_rate >= 0.5 && $analysis['lazy_loaded'] >= $should_lazy * 0.7;
    }
    
    /**
     * Get recommendation
     *
     * @param array $analysis Analysis results
     * @return string
     */
    protected static function get_recommendation(array $analysis): string {
        if ($analysis['total'] === 0) {
            return 'No images on page';
        }
        
        if ($analysis['total'] <= self::LAZY_LOAD_THRESHOLD) {
            return 'Too few images to need lazy loading';
        }
        
        if ($analysis['lazy_loaded'] === 0) {
            return 'Add loading="lazy" to images below the fold';
        }
        
        $should_lazy = $analysis['total'] - self::LAZY_LOAD_THRESHOLD;
        if ($analysis['lazy_loaded'] < $should_lazy) {
            return sprintf('Add lazy loading to %d more images', $should_lazy - $analysis['lazy_loaded']);
        }
        
        return 'Lazy loading is optimized!';
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
            'user-agent' => 'WPShadow-Diagnostic/1.0 (Performance Checker)',
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
            'id' => 'performance-image-lazy-loading',
            'title' => $title,
            'description' => $description,
            'color' => '#ff5722',
            'bg_color' => '#ffebee',
            'kb_link' => 'https://wpshadow.com/kb/lazy-loading/',
            'training_link' => 'https://wpshadow.com/training/performance-optimization/',
            'auto_fixable' => false,
            'threat_level' => 30,
            'module' => 'Performance',
            'priority' => 3,
        ];
    }
    
    /**
     * Get the name for display
     *
     * @return string
     */
    public static function get_name(): string {
        return __('Image Lazy Loading Check', 'wpshadow');
    }
    
    /**
     * Get the description for display
     *
     * @return string
     */
    public static function get_description(): string {
        return __('Checks HTML images for lazy loading attributes to improve performance.', 'wpshadow');
    }
}
