<?php

declare(strict_types=1);
/**
 * Test: Canonical Link Tag Check
 *
 * Tests if HTML contains proper canonical link tag to prevent duplicate content issues.
 *
 * Philosophy: Educate (#5, #6) - Help users avoid duplicate content penalties
 *
 * @package WPShadow
 */

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_SEO_Canonical_Link extends Diagnostic_Base
{

    protected static $slug = 'test-seo-canonical-link';
    protected static $title = 'Canonical Link Test';
    protected static $description = 'Tests for proper canonical link tags';

    /**
     * Run the diagnostic check
     *
     * PASS (returns null): Canonical tag exists and is properly formatted
     * FAIL (returns array): Missing canonical or has issues
     *
     * @param string|null $url URL to test (defaults to homepage)
     * @param string|null $html Pre-fetched HTML to analyze
     * @return array|null Finding data or null if no issue
     */
    public static function check(?string $url = null, ?string $html = null): ?array
    {
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
     * Run comprehensive canonical link tests
     *
     * @param string|null $url URL to test
     * @param string|null $html Pre-fetched HTML
     * @return array Test results
     */
    public static function run_canonical_tests(?string $url = null, ?string $html = null): array
    {
        $html = $html ?? self::fetch_html($url ?? home_url('/'));
        $checked_url = $url ?? home_url('/');

        if ($html === false) {
            return [
                'success' => false,
                'error' => 'Could not fetch HTML',
                'url' => $checked_url,
            ];
        }

        $canonical = self::extract_canonical($html);

        return [
            'success' => true,
            'url' => $checked_url,
            'canonical_url' => $canonical,
            'tests' => [
                'has_canonical' => self::test_has_canonical($html),
                'is_absolute_url' => self::test_is_absolute_url($html),
                'is_valid_url' => self::test_is_valid_url($html),
                'no_multiple_canonicals' => self::test_no_multiple_canonicals($html),
                'matches_current_url' => self::test_matches_current_url($html, $checked_url),
            ],
            'summary' => [
                'passed' => !empty($canonical) && self::is_canonical_valid($canonical),
                'canonical_present' => !empty($canonical),
                'issues' => self::detect_issues($canonical, $checked_url),
            ],
        ];
    }

    /**
     * Test if canonical link exists
     */
    public static function test_has_canonical(?string $url = null, ?string $html = null): array
    {
        $html = $html ?? self::fetch_html($url ?? home_url('/'));
        $canonical = self::extract_canonical($html);

        return [
            'test' => 'has_canonical',
            'passed' => !empty($canonical),
            'value' => $canonical,
            'message' => !empty($canonical)
                ? 'Canonical link tag present'
                : 'Canonical link tag missing',
            'impact' => 'Canonical tags prevent duplicate content issues in search engines',
        ];
    }

    /**
     * Test if canonical URL is absolute
     */
    public static function test_is_absolute_url(?string $url = null, ?string $html = null): array
    {
        $html = $html ?? self::fetch_html($url ?? home_url('/'));
        $canonical = self::extract_canonical($html);

        if (empty($canonical)) {
            return [
                'test' => 'is_absolute_url',
                'passed' => false,
                'message' => 'No canonical to check',
                'impact' => 'Canonical must be absolute URL',
            ];
        }

        $is_absolute = self::is_absolute_url($canonical);

        return [
            'test' => 'is_absolute_url',
            'passed' => $is_absolute,
            'value' => $canonical,
            'message' => $is_absolute
                ? 'Canonical URL is absolute (correct)'
                : 'Canonical URL is relative (should be absolute)',
            'impact' => 'Relative canonical URLs can cause ambiguity',
        ];
    }

    /**
     * Test if canonical URL is valid
     */
    public static function test_is_valid_url(?string $url = null, ?string $html = null): array
    {
        $html = $html ?? self::fetch_html($url ?? home_url('/'));
        $canonical = self::extract_canonical($html);

        if (empty($canonical)) {
            return [
                'test' => 'is_valid_url',
                'passed' => false,
                'message' => 'No canonical to validate',
                'impact' => 'Invalid canonical URL confuses search engines',
            ];
        }

        $is_valid = filter_var($canonical, FILTER_VALIDATE_URL) !== false;

        return [
            'test' => 'is_valid_url',
            'passed' => $is_valid,
            'value' => $canonical,
            'message' => $is_valid
                ? 'Canonical URL is valid'
                : 'Canonical URL is malformed',
            'impact' => 'Invalid URLs are ignored by search engines',
        ];
    }

    /**
     * Test for multiple canonical tags
     */
    public static function test_no_multiple_canonicals(?string $url = null, ?string $html = null): array
    {
        $html = $html ?? self::fetch_html($url ?? home_url('/'));
        $canonicals = self::extract_all_canonicals($html);
        $count = count($canonicals);

        return [
            'test' => 'no_multiple_canonicals',
            'passed' => $count <= 1,
            'count' => $count,
            'values' => $canonicals,
            'message' => $count <= 1
                ? 'Single canonical tag (correct)'
                : "Multiple canonical tags found ({$count}) - confusing for search engines",
            'impact' => 'Multiple canonicals cause search engines to ignore all of them',
        ];
    }

    /**
     * Test if canonical matches current URL
     */
    public static function test_matches_current_url(?string $url = null, ?string $html = null, ?string $current_url = null): array
    {
        $html = $html ?? self::fetch_html($url ?? home_url('/'));
        $current_url = $current_url ?? $url ?? home_url('/');
        $canonical = self::extract_canonical($html);

        if (empty($canonical)) {
            return [
                'test' => 'matches_current_url',
                'passed' => false,
                'message' => 'No canonical to compare',
                'impact' => 'Canonical should typically match current URL',
            ];
        }

        // Normalize URLs for comparison
        $canonical_normalized = self::normalize_url($canonical);
        $current_normalized = self::normalize_url($current_url);

        $matches = $canonical_normalized === $current_normalized;

        return [
            'test' => 'matches_current_url',
            'passed' => $matches,
            'canonical' => $canonical,
            'current_url' => $current_url,
            'message' => $matches
                ? 'Canonical matches current URL (typical)'
                : 'Canonical differs from current URL (may be intentional)',
            'impact' => 'Different canonical indicates this is a duplicate of another page',
        ];
    }

    /**
     * Analyze HTML for canonical link issues
     *
     * @param string $html HTML content
     * @param string $checked_url URL that was checked
     * @return array|null Finding or null
     */
    protected static function analyze_html(string $html, string $checked_url): ?array
    {
        $canonical = self::extract_canonical($html);

        // Missing canonical = FAIL
        if (empty($canonical)) {
            return [
                'id' => 'seo-canonical-link',
                'title' => 'Missing Canonical Link Tag',
                'description' => 'Your page is missing a canonical link tag. This can lead to duplicate content issues if the same content is accessible via multiple URLs.',
                'color' => '#ff9800',
                'bg_color' => '#fff3e0',
                'kb_link' => 'https://wpshadow.com/kb/canonical-tags/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=diagnostic',
                'training_link' => 'https://wpshadow.com/training/seo-canonical-tags/',
                'auto_fixable' => false,
                'threat_level' => 60,
                'module' => 'SEO',
                'priority' => 2,
                'meta' => [
                    'issue' => 'missing',
                    'recommendation' => sprintf('<link rel="canonical" href="%s" />', $checked_url),
                    'checked_url' => $checked_url,
                ],
            ];
        }

        // Check for issues
        $issues = [];

        // Not absolute URL
        if (!self::is_absolute_url($canonical)) {
            $issues[] = 'Relative URL (should be absolute)';
        }

        // Invalid URL format
        if (filter_var($canonical, FILTER_VALIDATE_URL) === false) {
            $issues[] = 'Malformed URL';
        }

        // Multiple canonicals
        $all_canonicals = self::extract_all_canonicals($html);
        if (count($all_canonicals) > 1) {
            $issues[] = sprintf('Multiple canonical tags (%d found)', count($all_canonicals));
        }

        // Perfect: no issues
        if (empty($issues)) {
            return null; // PASS
        }

        // Has canonical but with issues = FAIL
        $threat_level = 50;
        if (filter_var($canonical, FILTER_VALIDATE_URL) === false) {
            $threat_level = 70; // Invalid URL is serious
        }

        return [
            'id' => 'seo-canonical-link',
            'title' => 'Canonical Link Issues',
            'description' => sprintf(
                'Your canonical tag has %d issue(s): %s. These issues may prevent search engines from properly consolidating duplicate content.',
                count($issues),
                implode(', ', $issues)
            ),
            'color' => '#ff9800',
            'bg_color' => '#fff3e0',
            'kb_link' => 'https://wpshadow.com/kb/canonical-tags/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=diagnostic',
            'training_link' => 'https://wpshadow.com/training/seo-canonical-tags/',
            'auto_fixable' => false,
            'threat_level' => $threat_level,
            'module' => 'SEO',
            'priority' => 2,
            'meta' => [
                'canonical' => $canonical,
                'issues' => $issues,
                'all_canonicals' => $all_canonicals,
                'checked_url' => $checked_url,
            ],
        ];
    }

    /**
     * Extract canonical link from HTML
     *
     * @param string $html HTML content
     * @return string Canonical URL or empty string
     */
    protected static function extract_canonical(string $html): string
    {
        if (empty($html)) {
            return '';
        }

        if (preg_match('/<link\s+rel=["\']canonical["\']\s+href=["\'](.*?)["\']/i', $html, $matches)) {
            return trim($matches[1]);
        }

        return '';
    }

    /**
     * Extract all canonical links (detect duplicates)
     *
     * @param string $html HTML content
     * @return array All canonical URLs found
     */
    protected static function extract_all_canonicals(string $html): array
    {
        if (empty($html)) {
            return [];
        }

        preg_match_all('/<link\s+rel=["\']canonical["\']\s+href=["\'](.*?)["\']/i', $html, $matches);
        return array_map('trim', $matches[1] ?? []);
    }

    /**
     * Check if URL is absolute
     *
     * @param string $url URL to check
     * @return bool
     */
    protected static function is_absolute_url(string $url): bool
    {
        return preg_match('/^https?:\/\//i', $url) === 1;
    }

    /**
     * Check if canonical is valid
     *
     * @param string $canonical Canonical URL
     * @return bool
     */
    protected static function is_canonical_valid(string $canonical): bool
    {
        if (empty($canonical)) {
            return false;
        }

        return self::is_absolute_url($canonical) &&
            filter_var($canonical, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Detect all issues with canonical
     *
     * @param string $canonical Canonical URL
     * @param string $checked_url Current URL
     * @return array List of issues
     */
    protected static function detect_issues(string $canonical, string $checked_url): array
    {
        $issues = [];

        if (empty($canonical)) {
            $issues[] = 'missing';
            return $issues;
        }

        if (!self::is_absolute_url($canonical)) {
            $issues[] = 'relative_url';
        }

        if (filter_var($canonical, FILTER_VALIDATE_URL) === false) {
            $issues[] = 'invalid_url';
        }

        return $issues;
    }

    /**
     * Normalize URL for comparison
     *
     * @param string $url URL to normalize
     * @return string Normalized URL
     */
    protected static function normalize_url(string $url): string
    {
        $url = strtolower(trim($url));
        $url = preg_replace('/\/$/', '', $url); // Remove trailing slash
        $url = preg_replace('/^https?:\/\/www\./', 'https://', $url); // Remove www
        return $url;
    }

    /**
     * Fetch HTML from URL
     *
     * @param string $url URL to fetch
     * @return string|false HTML or false on error
     */
    protected static function fetch_html(string $url)
    {
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
    protected static function is_internal_url(string $url): bool
    {
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
    protected static function error_result(string $title, string $description): array
    {
        return [
            'id' => 'seo-canonical-link',
            'title' => $title,
            'description' => $description,
            'color' => '#ff5722',
            'bg_color' => '#ffebee',
            'kb_link' => 'https://wpshadow.com/kb/canonical-tags/',
            'training_link' => 'https://wpshadow.com/training/seo-canonical-tags/',
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
    public static function get_name(): string
    {
        return __('Canonical Link Check', 'wpshadow');
    }

    /**
     * Get the description for display
     *
     * @return string
     */
    public static function get_description(): string
    {
        return __('Checks HTML for proper canonical link tags to prevent duplicate content issues.', 'wpshadow');
    }
}
