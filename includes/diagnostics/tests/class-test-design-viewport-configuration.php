<?php
declare(strict_types=1);
/**
 * Test: Viewport Meta Tag Configuration
 *
 * Tests if HTML contains proper viewport meta tag for mobile responsiveness.
 * 
 * Philosophy: Inspire confidence (#8) - Help users create mobile-friendly sites
 * 
 * @package WPShadow
 */

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Design_Viewport_Configuration extends Diagnostic_Base {
    
    protected static $slug = 'test-design-viewport-configuration';
    protected static $title = 'Viewport Meta Tag Test';
    protected static $description = 'Tests for proper viewport configuration for mobile devices';
    
    /**
     * Recommended viewport settings
     */
    const RECOMMENDED_VIEWPORT = 'width=device-width, initial-scale=1';
    
    /**
     * Run the diagnostic check
     *
     * PASS (returns null): Viewport tag exists with proper settings
     * FAIL (returns array): Missing viewport or problematic configuration
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
     * Run comprehensive viewport tests
     *
     * @param string|null $url URL to test
     * @param string|null $html Pre-fetched HTML
     * @return array Test results
     */
    public static function run_viewport_tests(?string $url = null, ?string $html = null): array {
        $html = $html ?? self::fetch_html($url ?? home_url('/'));
        
        if ($html === false) {
            return [
                'success' => false,
                'error' => 'Could not fetch HTML',
                'url' => $url ?? home_url('/'),
            ];
        }
        
        $viewport = self::extract_viewport($html);
        $parsed = self::parse_viewport_content($viewport);
        
        return [
            'success' => true,
            'url' => $url ?? home_url('/'),
            'viewport_content' => $viewport,
            'parsed_settings' => $parsed,
            'tests' => [
                'has_viewport' => self::test_has_viewport($html),
                'has_device_width' => self::test_has_device_width($html),
                'has_initial_scale' => self::test_has_initial_scale($html),
                'no_user_scalable_disabled' => self::test_user_scalable($html),
                'no_maximum_scale_limit' => self::test_maximum_scale($html),
            ],
            'summary' => [
                'passed' => !empty($viewport) && self::is_viewport_optimal($parsed),
                'mobile_friendly' => self::is_mobile_friendly($parsed),
                'accessibility_friendly' => self::is_accessibility_friendly($parsed),
            ],
        ];
    }
    
    /**
     * Test if viewport tag exists
     */
    public static function test_has_viewport(?string $url = null, ?string $html = null): array {
        $html = $html ?? self::fetch_html($url ?? home_url('/'));
        $viewport = self::extract_viewport($html);
        
        return [
            'test' => 'has_viewport',
            'passed' => !empty($viewport),
            'value' => $viewport,
            'message' => !empty($viewport) 
                ? 'Viewport meta tag present'
                : 'Viewport meta tag missing',
            'impact' => 'Viewport tag is essential for mobile responsiveness',
        ];
    }
    
    /**
     * Test for width=device-width
     */
    public static function test_has_device_width(?string $url = null, ?string $html = null): array {
        $html = $html ?? self::fetch_html($url ?? home_url('/'));
        $viewport = self::extract_viewport($html);
        $parsed = self::parse_viewport_content($viewport);
        
        $has_device_width = isset($parsed['width']) && $parsed['width'] === 'device-width';
        
        return [
            'test' => 'has_device_width',
            'passed' => $has_device_width,
            'value' => $parsed['width'] ?? null,
            'message' => $has_device_width 
                ? 'width=device-width present (correct)'
                : 'width=device-width missing (recommended)',
            'impact' => 'device-width ensures page adapts to screen size',
        ];
    }
    
    /**
     * Test for initial-scale=1
     */
    public static function test_has_initial_scale(?string $url = null, ?string $html = null): array {
        $html = $html ?? self::fetch_html($url ?? home_url('/'));
        $viewport = self::extract_viewport($html);
        $parsed = self::parse_viewport_content($viewport);
        
        $has_initial_scale = isset($parsed['initial-scale']) && $parsed['initial-scale'] == 1;
        
        return [
            'test' => 'has_initial_scale',
            'passed' => $has_initial_scale,
            'value' => $parsed['initial-scale'] ?? null,
            'message' => $has_initial_scale 
                ? 'initial-scale=1 present (correct)'
                : 'initial-scale=1 missing or incorrect',
            'impact' => 'initial-scale=1 prevents unwanted zoom on page load',
        ];
    }
    
    /**
     * Test for user-scalable (accessibility)
     */
    public static function test_user_scalable(?string $url = null, ?string $html = null): array {
        $html = $html ?? self::fetch_html($url ?? home_url('/'));
        $viewport = self::extract_viewport($html);
        $parsed = self::parse_viewport_content($viewport);
        
        $disabled = isset($parsed['user-scalable']) && 
                   (strtolower($parsed['user-scalable']) === 'no' || $parsed['user-scalable'] == '0');
        
        return [
            'test' => 'no_user_scalable_disabled',
            'passed' => !$disabled,
            'value' => $parsed['user-scalable'] ?? 'yes',
            'message' => $disabled 
                ? 'user-scalable=no found (accessibility issue)'
                : 'User scaling allowed (good)',
            'impact' => 'Disabling zoom hurts accessibility for visually impaired users',
        ];
    }
    
    /**
     * Test for maximum-scale restrictions
     */
    public static function test_maximum_scale(?string $url = null, ?string $html = null): array {
        $html = $html ?? self::fetch_html($url ?? home_url('/'));
        $viewport = self::extract_viewport($html);
        $parsed = self::parse_viewport_content($viewport);
        
        $restricted = isset($parsed['maximum-scale']) && floatval($parsed['maximum-scale']) < 5;
        
        return [
            'test' => 'no_maximum_scale_limit',
            'passed' => !$restricted,
            'value' => $parsed['maximum-scale'] ?? null,
            'message' => $restricted 
                ? 'maximum-scale restricted (accessibility concern)'
                : 'Maximum scale unrestricted (good)',
            'impact' => 'Limiting maximum-scale prevents users from zooming adequately',
        ];
    }
    
    /**
     * Analyze HTML for viewport issues
     *
     * @param string $html HTML content
     * @param string $checked_url URL that was checked
     * @return array|null Finding or null
     */
    protected static function analyze_html(string $html, string $checked_url): ?array {
        $viewport = self::extract_viewport($html);
        
        // Missing viewport = FAIL
        if (empty($viewport)) {
            return [
                'id' => 'design-viewport-configuration',
                'title' => 'Missing Viewport Meta Tag',
                'description' => 'Your page is missing a viewport meta tag. This tag is essential for mobile responsiveness and will cause display issues on mobile devices.',
                'color' => '#ff9800',
                'bg_color' => '#fff3e0',
                'kb_link' => 'https://wpshadow.com/kb/viewport-meta-tag/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=diagnostic',
                'training_link' => 'https://wpshadow.com/training/mobile-responsive-design/',
                'auto_fixable' => false,
                'threat_level' => 80,
                'module' => 'Design',
                'priority' => 1,
                'meta' => [
                    'issue' => 'missing',
                    'recommendation' => '<meta name="viewport" content="' . self::RECOMMENDED_VIEWPORT . '">',
                    'checked_url' => $checked_url,
                ],
            ];
        }
        
        $parsed = self::parse_viewport_content($viewport);
        $issues = [];
        
        // Check for common problems
        if (!isset($parsed['width']) || $parsed['width'] !== 'device-width') {
            $issues[] = 'Missing width=device-width';
        }
        
        if (!isset($parsed['initial-scale']) || $parsed['initial-scale'] != 1) {
            $issues[] = 'initial-scale not set to 1';
        }
        
        // Accessibility issues
        if (isset($parsed['user-scalable']) && 
            (strtolower($parsed['user-scalable']) === 'no' || $parsed['user-scalable'] == '0')) {
            $issues[] = 'user-scalable=no (blocks zoom, accessibility issue)';
        }
        
        if (isset($parsed['maximum-scale']) && floatval($parsed['maximum-scale']) < 5) {
            $issues[] = 'maximum-scale restricted (accessibility concern)';
        }
        
        // Perfect: no issues
        if (empty($issues)) {
            return null; // PASS
        }
        
        // Has viewport but suboptimal = FAIL
        $threat_level = 60;
        if (count($issues) > 2) {
            $threat_level = 70;
        }
        if (in_array('user-scalable=no (blocks zoom, accessibility issue)', $issues)) {
            $threat_level = 75; // Accessibility is important
        }
        
        return [
            'id' => 'design-viewport-configuration',
            'title' => 'Suboptimal Viewport Configuration',
            'description' => sprintf(
                'Your viewport tag has %d issue(s): %s. This may cause mobile display or accessibility problems.',
                count($issues),
                implode(', ', $issues)
            ),
            'color' => '#ff9800',
            'bg_color' => '#fff3e0',
            'kb_link' => 'https://wpshadow.com/kb/viewport-meta-tag/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=diagnostic',
            'training_link' => 'https://wpshadow.com/training/mobile-responsive-design/',
            'auto_fixable' => false,
            'threat_level' => $threat_level,
            'module' => 'Design',
            'priority' => 1,
            'meta' => [
                'current_viewport' => $viewport,
                'issues' => $issues,
                'parsed_settings' => $parsed,
                'recommendation' => '<meta name="viewport" content="' . self::RECOMMENDED_VIEWPORT . '">',
                'checked_url' => $checked_url,
            ],
        ];
    }
    
    /**
     * Extract viewport meta tag content
     *
     * @param string $html HTML content
     * @return string Viewport content or empty string
     */
    protected static function extract_viewport(string $html): string {
        if (empty($html)) {
            return '';
        }
        
        if (preg_match('/<meta\s+name=["\']viewport["\']\s+content=["\'](.*?)["\']/i', $html, $matches)) {
            return $matches[1];
        }
        
        return '';
    }
    
    /**
     * Parse viewport content into key-value pairs
     *
     * @param string $viewport Viewport content
     * @return array Parsed settings
     */
    protected static function parse_viewport_content(string $viewport): array {
        if (empty($viewport)) {
            return [];
        }
        
        $parsed = [];
        $parts = array_map('trim', explode(',', $viewport));
        
        foreach ($parts as $part) {
            if (strpos($part, '=') !== false) {
                list($key, $value) = array_map('trim', explode('=', $part, 2));
                $parsed[strtolower($key)] = $value;
            }
        }
        
        return $parsed;
    }
    
    /**
     * Check if viewport settings are optimal
     *
     * @param array $parsed Parsed viewport settings
     * @return bool
     */
    protected static function is_viewport_optimal(array $parsed): bool {
        return isset($parsed['width']) && $parsed['width'] === 'device-width' &&
               isset($parsed['initial-scale']) && $parsed['initial-scale'] == 1;
    }
    
    /**
     * Check if viewport is mobile friendly
     *
     * @param array $parsed Parsed viewport settings
     * @return bool
     */
    protected static function is_mobile_friendly(array $parsed): bool {
        return isset($parsed['width']) && $parsed['width'] === 'device-width';
    }
    
    /**
     * Check if viewport is accessibility friendly
     *
     * @param array $parsed Parsed viewport settings
     * @return bool
     */
    protected static function is_accessibility_friendly(array $parsed): bool {
        // No user-scalable=no
        if (isset($parsed['user-scalable']) && 
            (strtolower($parsed['user-scalable']) === 'no' || $parsed['user-scalable'] == '0')) {
            return false;
        }
        
        // No restrictive maximum-scale
        if (isset($parsed['maximum-scale']) && floatval($parsed['maximum-scale']) < 5) {
            return false;
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
            'user-agent' => 'WPShadow-Diagnostic/1.0 (Mobile Checker)',
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
            'id' => 'design-viewport-configuration',
            'title' => $title,
            'description' => $description,
            'color' => '#ff5722',
            'bg_color' => '#ffebee',
            'kb_link' => 'https://wpshadow.com/kb/viewport-meta-tag/',
            'training_link' => 'https://wpshadow.com/training/mobile-responsive-design/',
            'auto_fixable' => false,
            'threat_level' => 30,
            'module' => 'Design',
            'priority' => 3,
        ];
    }
    
    /**
     * Get the name for display
     *
     * @return string
     */
    public static function get_name(): string {
        return __('Viewport Configuration Check', 'wpshadow');
    }
    
    /**
     * Get the description for display
     *
     * @return string
     */
    public static function get_description(): string {
        return __('Checks HTML for proper viewport meta tag configuration for mobile devices.', 'wpshadow');
    }
}
