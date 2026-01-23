<?php
declare(strict_types=1);
/**
 * Test: X-Robots-Tag Header Check
 *
 * Tests if the X-Robots-Tag HTTP header is properly set (or not set) on frontend pages.
 * This diagnostic checks whether pages have restrictive X-Robots-Tag headers that might
 * prevent search engine indexing.
 *
 * Philosophy: Educate (#5, #6) - Help users understand HTTP header impact on SEO
 * 
 * @package WPShadow
 */

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_SEO_X_Robots_Tag_Header extends Diagnostic_Base {
    
    protected static $slug = 'test-seo-x-robots-tag-header';
    protected static $title = 'X-Robots-Tag Header Test';
    protected static $description = 'Tests for blocking X-Robots-Tag headers on public pages';
    
    /**
     * Available directive tests
     * Each can be tested individually or as a group
     */
    const DIRECTIVE_NOINDEX = 'noindex';
    const DIRECTIVE_NOFOLLOW = 'nofollow';
    const DIRECTIVE_NONE = 'none';
    const DIRECTIVE_NOARCHIVE = 'noarchive';
    const DIRECTIVE_NOSNIPPET = 'nosnippet';
    
    /**
     * All available directives for testing
     *
     * @var array
     */
    protected static $all_directives = [
        self::DIRECTIVE_NOINDEX,
        self::DIRECTIVE_NOFOLLOW,
        self::DIRECTIVE_NONE,
        self::DIRECTIVE_NOARCHIVE,
        self::DIRECTIVE_NOSNIPPET,
    ];
    
    /**
     * Run the diagnostic check against frontend HTML
     *
     * This test fetches a URL and checks HTTP headers for X-Robots-Tag.
     * Can test homepage, specific URLs, or analyze raw HTML.
     * 
     * PASS (returns null): 
     * - No X-Robots-Tag header present, OR
     * - X-Robots-Tag is set to allow indexing (index, follow)
     * 
     * FAIL (returns array):
     * - X-Robots-Tag header blocks indexing (noindex, nofollow, none)
     * - Unexpected restrictive directives found
     *
     * @param string|null $url Optional URL to test (defaults to homepage)
     * @param array|null $headers Optional pre-fetched headers to analyze
     * @return array|null Finding data or null if no issue
     */
    public static function check(?string $url = null, ?array $headers = null): ?array {
        // If headers provided directly, analyze them
        if ($headers !== null) {
            return self::analyze_headers($headers, $url ?? 'provided-html');
        }
        
        // Get the site URL to test (default to homepage)
        $site_url = $url ?? home_url('/');
        
        // Validate URL is within the site
        if ($url !== null && !self::is_internal_url($url)) {
            return [
                'id' => 'seo-x-robots-tag-header',
                'title' => 'Invalid URL Provided',
                'description' => 'The URL must be from this WordPress site.',
                'color' => '#ff5722',
                'bg_color' => '#ffebee',
                'kb_link' => 'https://wpshadow.com/kb/x-robots-tag/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=diagnostic',
                'training_link' => 'https://wpshadow.com/training/seo-headers/',
                'auto_fixable' => false,
                'threat_level' => 10,
                'module' => 'SEO',
                'priority' => 3,
            ];
        }
        
        // Fetch the URL and capture headers
        $response = wp_remote_get($site_url, [
            'timeout' => 10,
            'redirection' => 5,
            'user-agent' => 'WPShadow-Diagnostic/1.0 (SEO Checker)',
            'sslverify' => false, // For local dev environments
        ]);
        
        // Check for request errors
        if (is_wp_error($response)) {
            return [
                'id' => 'seo-x-robots-tag-header',
                'title' => 'X-Robots-Tag Header Check Failed',
                'description' => 'Could not fetch URL to check headers: ' . $response->get_error_message(),
                'color' => '#ff5722',
                'bg_color' => '#ffebee',
                'kb_link' => 'https://wpshadow.com/kb/x-robots-tag/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=diagnostic',
                'training_link' => 'https://wpshadow.com/training/seo-headers/',
                'auto_fixable' => false,
                'threat_level' => 40,
                'module' => 'SEO',
                'priority' => 2,
                'meta' => [
                    'checked_url' => $site_url,
                    'error' => $response->get_error_message(),
                ],
            ];
        }
        
        // Get response headers
        $response_headers = wp_remote_retrieve_headers($response);
        
        return self::analyze_headers($response_headers, $site_url);
    }
    
    /**
     * Run comprehensive directive tests
     * 
     * Tests each directive individually and returns detailed results.
     * Useful for Guardian to verify each aspect of SEO header configuration.
     *
     * @param string|null $url URL to test (defaults to homepage)
     * @param array|null $headers Pre-fetched headers to test
     * @param array|null $directives_to_test Specific directives to test (null = all)
     * @return array Detailed test results for each directive
     */
    public static function run_directive_tests(?string $url = null, ?array $headers = null, ?array $directives_to_test = null): array {
        $directives = $directives_to_test ?? self::$all_directives;
        
        // Fetch headers if not provided
        if ($headers === null) {
            $headers = self::fetch_headers($url ?? home_url('/'));
            if ($headers === false) {
                return [
                    'success' => false,
                    'error' => 'Could not fetch headers from URL',
                    'url' => $url ?? home_url('/'),
                ];
            }
        }
        
        $results = [
            'success' => true,
            'url' => $url ?? home_url('/'),
            'header_present' => self::has_x_robots_tag($headers),
            'header_value' => self::get_x_robots_tag_value($headers),
            'tests' => [],
            'summary' => [
                'total' => 0,
                'passed' => 0,
                'failed' => 0,
            ],
        ];
        
        // Run each directive test
        foreach ($directives as $directive) {
            $test_result = self::test_directive($directive, $headers);
            $results['tests'][$directive] = $test_result;
            $results['summary']['total']++;
            
            if ($test_result['passed']) {
                $results['summary']['passed']++;
            } else {
                $results['summary']['failed']++;
            }
        }
        
        return $results;
    }
    
    /**
     * Test for noindex directive
     * 
     * PASS: noindex is NOT present (page can be indexed)
     * FAIL: noindex IS present (page cannot be indexed)
     *
     * @param string|null $url URL to test
     * @param array|null $headers Pre-fetched headers
     * @return array Test result
     */
    public static function test_noindex(?string $url = null, ?array $headers = null): array {
        $headers = $headers ?? self::fetch_headers($url ?? home_url('/'));
        return self::test_directive(self::DIRECTIVE_NOINDEX, $headers);
    }
    
    /**
     * Test for nofollow directive
     * 
     * PASS: nofollow is NOT present (links can be followed)
     * FAIL: nofollow IS present (links won't be followed)
     *
     * @param string|null $url URL to test
     * @param array|null $headers Pre-fetched headers
     * @return array Test result
     */
    public static function test_nofollow(?string $url = null, ?array $headers = null): array {
        $headers = $headers ?? self::fetch_headers($url ?? home_url('/'));
        return self::test_directive(self::DIRECTIVE_NOFOLLOW, $headers);
    }
    
    /**
     * Test for none directive
     * 
     * PASS: none is NOT present
     * FAIL: none IS present (equivalent to noindex + nofollow)
     *
     * @param string|null $url URL to test
     * @param array|null $headers Pre-fetched headers
     * @return array Test result
     */
    public static function test_none(?string $url = null, ?array $headers = null): array {
        $headers = $headers ?? self::fetch_headers($url ?? home_url('/'));
        return self::test_directive(self::DIRECTIVE_NONE, $headers);
    }
    
    /**
     * Test for noarchive directive
     * 
     * PASS: noarchive is NOT present (page can be cached)
     * FAIL: noarchive IS present (page won't be cached by search engines)
     *
     * @param string|null $url URL to test
     * @param array|null $headers Pre-fetched headers
     * @return array Test result
     */
    public static function test_noarchive(?string $url = null, ?array $headers = null): array {
        $headers = $headers ?? self::fetch_headers($url ?? home_url('/'));
        return self::test_directive(self::DIRECTIVE_NOARCHIVE, $headers);
    }
    
    /**
     * Test for nosnippet directive
     * 
     * PASS: nosnippet is NOT present (snippets can be shown in search results)
     * FAIL: nosnippet IS present (no snippets will be shown)
     *
     * @param string|null $url URL to test
     * @param array|null $headers Pre-fetched headers
     * @return array Test result
     */
    public static function test_nosnippet(?string $url = null, ?array $headers = null): array {
        $headers = $headers ?? self::fetch_headers($url ?? home_url('/'));
        return self::test_directive(self::DIRECTIVE_NOSNIPPET, $headers);
    }
    
    /**
     * Test for a specific directive in headers
     *
     * @param string $directive Directive to test for
     * @param array|false $headers HTTP headers to check
     * @return array Test result with pass/fail and details
     */
    protected static function test_directive(string $directive, $headers): array {
        if ($headers === false) {
            return [
                'directive' => $directive,
                'passed' => false,
                'found' => false,
                'message' => 'Could not fetch headers',
                'impact' => self::get_directive_impact($directive),
            ];
        }
        
        $x_robots_value = self::get_x_robots_tag_value($headers);
        
        // No header = PASS (no restrictions)
        if ($x_robots_value === null) {
            return [
                'directive' => $directive,
                'passed' => true,
                'found' => false,
                'message' => 'No X-Robots-Tag header present',
                'impact' => self::get_directive_impact($directive),
            ];
        }
        
        // Check if directive is present
        $found = stripos($x_robots_value, $directive) !== false;
        
        return [
            'directive' => $directive,
            'passed' => !$found, // PASS if NOT found (no restriction)
            'found' => $found,
            'message' => $found 
                ? "Directive '{$directive}' found in X-Robots-Tag header"
                : "Directive '{$directive}' not found (good)",
            'header_value' => $x_robots_value,
            'impact' => self::get_directive_impact($directive),
        ];
    }
    
    /**
     * Get the impact description for a directive
     *
     * @param string $directive Directive name
     * @return string Impact description
     */
    protected static function get_directive_impact(string $directive): string {
        $impacts = [
            self::DIRECTIVE_NOINDEX => 'Prevents search engines from indexing this page',
            self::DIRECTIVE_NOFOLLOW => 'Prevents search engines from following links on this page',
            self::DIRECTIVE_NONE => 'Prevents indexing AND following links (combines noindex + nofollow)',
            self::DIRECTIVE_NOARCHIVE => 'Prevents search engines from caching/archiving this page',
            self::DIRECTIVE_NOSNIPPET => 'Prevents search engines from showing text snippets in search results',
        ];
        
        return $impacts[$directive] ?? 'Unknown directive';
    }
    
    /**
     * Fetch headers from a URL
     *
     * @param string $url URL to fetch
     * @return array|false Headers array or false on error
     */
    protected static function fetch_headers(string $url) {
        $response = wp_remote_get($url, [
            'timeout' => 10,
            'redirection' => 5,
            'user-agent' => 'WPShadow-Diagnostic/1.0 (SEO Checker)',
            'sslverify' => false,
        ]);
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $headers = wp_remote_retrieve_headers($response);
        
        // Convert to array if it's an object
        if (is_object($headers) && method_exists($headers, 'getAll')) {
            return $headers->getAll();
        }
        
        return is_array($headers) ? $headers : [];
    }
    
    /**
     * Check if X-Robots-Tag header exists
     *
     * @param array $headers HTTP headers
     * @return bool True if header exists
     */
    protected static function has_x_robots_tag(array $headers): bool {
        return self::get_x_robots_tag_value($headers) !== null;
    }
    
    /**
     * Get X-Robots-Tag header value (case-insensitive)
     *
     * @param array $headers HTTP headers
     * @return string|null Header value or null if not found
     */
    protected static function get_x_robots_tag_value(array $headers): ?string {
        foreach ($headers as $key => $value) {
            if (strtolower($key) === 'x-robots-tag') {
                return is_array($value) ? implode(', ', $value) : (string) $value;
            }
        }
        
        return null;
    }
    
    /**
     * Analyze headers for blocking X-Robots-Tag directives
     *
     * @param array|\WpOrg\Requests\Utility\CaseInsensitiveDictionary $headers HTTP headers
     * @param string $checked_url URL that was checked
     * @return array|null Finding data or null if no issue
     */
    protected static function analyze_headers($headers, string $checked_url): ?array {
        // Convert to array if it's a CaseInsensitiveDictionary
        if (is_object($headers)) {
            $headers = method_exists($headers, 'getAll') ? $headers->getAll() : (array) $headers;
        }
        
        $x_robots_value = self::get_x_robots_tag_value($headers);
        
        if ($x_robots_value === null) {
            // No X-Robots-Tag header = PASS (good for most sites)
            return null;
        }
        
        // Check for blocking directives using individual tests
        $found_blocking = [];
        foreach (self::$all_directives as $directive) {
            $test_result = self::test_directive($directive, $headers);
            if ($test_result['found']) {
                $found_blocking[] = [
                    'directive' => $directive,
                    'impact' => $test_result['impact'],
                ];
            }
        }
        
        // If blocking directives found, FAIL
        if (!empty($found_blocking)) {
            $directive_names = array_column($found_blocking, 'directive');
            
            return [
                'id' => 'seo-x-robots-tag-header',
                'title' => 'Blocking X-Robots-Tag Header Detected',
                'description' => sprintf(
                    'Your site has an X-Robots-Tag HTTP header with blocking directives: <code>%s</code>. This may prevent search engines from indexing your content. Remove or modify this header unless intentionally blocking indexation.',
                    esc_html($x_robots_value)
                ),
                'color' => '#ff9800',
                'bg_color' => '#fff3e0',
                'kb_link' => 'https://wpshadow.com/kb/x-robots-tag/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=diagnostic',
                'training_link' => 'https://wpshadow.com/training/seo-headers/',
                'auto_fixable' => false,
                'threat_level' => 70,
                'module' => 'SEO',
                'priority' => 1,
                'meta' => [
                    'header_value' => $x_robots_value,
                    'blocking_directives' => $directive_names,
                    'directive_details' => $found_blocking,
                    'checked_url' => $checked_url,
                ],
            ];
        }
        
        // Header exists but allows indexing = PASS
        return null;
    }
    
    /**
     * Check if URL belongs to this WordPress site
     *
     * @param string $url URL to validate
     * @return bool True if internal URL
     */
    protected static function is_internal_url(string $url): bool {
        $site_url = home_url();
        $site_host = wp_parse_url($site_url, PHP_URL_HOST);
        $test_host = wp_parse_url($url, PHP_URL_HOST);
        
        return $site_host === $test_host;
    }
    
    /**
     * Test a specific URL within the site
     *
     * @param string $url Full URL to test
     * @return array|null Finding data or null if no issue
     */
    public static function test_url(string $url): ?array {
        return self::check($url);
    }
    
    /**
     * Test raw HTML/headers directly without fetching
     *
     * @param array $headers Associative array of HTTP headers
     * @param string $source_label Label for where headers came from (for reporting)
     * @return array|null Finding data or null if no issue
     */
    public static function test_headers(array $headers, string $source_label = 'raw-html'): ?array {
        return self::check(null, $headers);
    }
    
    /**
     * Get the name for display
     *
     * @return string
     */
    public static function get_name(): string {
        return __('X-Robots-Tag Header Check', 'wpshadow');
    }
    
    /**
     * Get the description for display
     *
     * @return string
     */
    public static function get_description(): string {
        return __('Checks HTTP headers for blocking X-Robots-Tag directives that prevent search engine indexing.', 'wpshadow');
    }
}
