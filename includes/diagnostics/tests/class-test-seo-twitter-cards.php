<?php

declare(strict_types=1);
/**
 * Test: Twitter Card Tags Check
 *
 * Tests if HTML contains proper Twitter Card meta tags for Twitter sharing.
 *
 * Philosophy: Educate (#5, #6) - Help users optimize Twitter engagement
 *
 * @package WPShadow
 */

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_SEO_Twitter_Cards extends Diagnostic_Base
{

    protected static $slug = 'test-seo-twitter-cards';
    protected static $title = 'Twitter Card Tags Test';
    protected static $description = 'Tests for Twitter Card meta tags';

    /**
     * Required Twitter Card tags
     */
    const REQUIRED_TAGS = ['twitter:card'];
    const RECOMMENDED_TAGS = ['twitter:title', 'twitter:description', 'twitter:image'];
    const OPTIONAL_TAGS = ['twitter:site', 'twitter:creator'];

    /**
     * Valid card types
     */
    const VALID_CARD_TYPES = ['summary', 'summary_large_image', 'app', 'player'];

    /**
     * Run the diagnostic check
     *
     * PASS (returns null): Has twitter:card and recommended tags
     * FAIL (returns array): Missing twitter:card or recommended tags
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
     * Run comprehensive Twitter Card tests
     *
     * @param string|null $url URL to test
     * @param string|null $html Pre-fetched HTML
     * @return array Test results
     */
    public static function run_twitter_tests(?string $url = null, ?string $html = null): array
    {
        $html = $html ?? self::fetch_html($url ?? home_url('/'));

        if ($html === false) {
            return [
                'success' => false,
                'error' => 'Could not fetch HTML',
                'url' => $url ?? home_url('/'),
            ];
        }

        $twitter_tags = self::extract_twitter_tags($html);

        return [
            'success' => true,
            'url' => $url ?? home_url('/'),
            'twitter_tags' => $twitter_tags,
            'tests' => [
                'has_card_type' => self::test_has_card_type($html),
                'valid_card_type' => self::test_valid_card_type($html),
                'has_title' => self::test_has_title($html),
                'has_description' => self::test_has_description($html),
                'has_image' => self::test_has_image($html),
                'has_site_handle' => self::test_has_site_handle($html),
            ],
            'summary' => [
                'required_present' => self::count_required_tags($twitter_tags),
                'required_total' => count(self::REQUIRED_TAGS),
                'recommended_present' => self::count_recommended_tags($twitter_tags),
                'recommended_total' => count(self::RECOMMENDED_TAGS),
                'passed' => self::has_required_tags($twitter_tags),
                'card_type' => $twitter_tags['twitter:card'] ?? null,
            ],
        ];
    }

    /**
     * Test for twitter:card
     */
    public static function test_has_card_type(?string $url = null, ?string $html = null): array
    {
        $html = $html ?? self::fetch_html($url ?? home_url('/'));
        $twitter_tags = self::extract_twitter_tags($html);

        return [
            'test' => 'has_card_type',
            'passed' => isset($twitter_tags['twitter:card']) && !empty($twitter_tags['twitter:card']),
            'value' => $twitter_tags['twitter:card'] ?? null,
            'message' => isset($twitter_tags['twitter:card'])
                ? 'twitter:card present'
                : 'twitter:card missing (required)',
            'impact' => 'Card type defines how content appears on Twitter',
        ];
    }

    /**
     * Test if card type is valid
     */
    public static function test_valid_card_type(?string $url = null, ?string $html = null): array
    {
        $html = $html ?? self::fetch_html($url ?? home_url('/'));
        $twitter_tags = self::extract_twitter_tags($html);

        $card_type = $twitter_tags['twitter:card'] ?? '';
        $is_valid = in_array($card_type, self::VALID_CARD_TYPES, true);

        return [
            'test' => 'valid_card_type',
            'passed' => $is_valid,
            'value' => $card_type,
            'valid_types' => self::VALID_CARD_TYPES,
            'message' => $is_valid
                ? "Card type '{$card_type}' is valid"
                : "Card type '{$card_type}' is not recognized",
            'impact' => 'Invalid card types are ignored by Twitter',
        ];
    }

    /**
     * Test for twitter:title
     */
    public static function test_has_title(?string $url = null, ?string $html = null): array
    {
        $html = $html ?? self::fetch_html($url ?? home_url('/'));
        $twitter_tags = self::extract_twitter_tags($html);
        $og_tags = self::extract_og_tags($html);

        // Twitter falls back to og:title if twitter:title missing
        $has_title = isset($twitter_tags['twitter:title']) || isset($og_tags['og:title']);

        return [
            'test' => 'has_title',
            'passed' => $has_title,
            'twitter_value' => $twitter_tags['twitter:title'] ?? null,
            'og_fallback' => $og_tags['og:title'] ?? null,
            'message' => $has_title
                ? 'Title present (twitter:title or og:title fallback)'
                : 'No title found (twitter:title or og:title)',
            'impact' => 'Title shown in Twitter card',
        ];
    }

    /**
     * Test for twitter:description
     */
    public static function test_has_description(?string $url = null, ?string $html = null): array
    {
        $html = $html ?? self::fetch_html($url ?? home_url('/'));
        $twitter_tags = self::extract_twitter_tags($html);
        $og_tags = self::extract_og_tags($html);

        // Twitter falls back to og:description
        $has_desc = isset($twitter_tags['twitter:description']) || isset($og_tags['og:description']);

        return [
            'test' => 'has_description',
            'passed' => $has_desc,
            'twitter_value' => $twitter_tags['twitter:description'] ?? null,
            'og_fallback' => $og_tags['og:description'] ?? null,
            'message' => $has_desc
                ? 'Description present (twitter:description or og:description fallback)'
                : 'No description found',
            'impact' => 'Description shown in Twitter card',
        ];
    }

    /**
     * Test for twitter:image
     */
    public static function test_has_image(?string $url = null, ?string $html = null): array
    {
        $html = $html ?? self::fetch_html($url ?? home_url('/'));
        $twitter_tags = self::extract_twitter_tags($html);
        $og_tags = self::extract_og_tags($html);

        // Twitter falls back to og:image
        $has_image = isset($twitter_tags['twitter:image']) || isset($og_tags['og:image']);

        return [
            'test' => 'has_image',
            'passed' => $has_image,
            'twitter_value' => $twitter_tags['twitter:image'] ?? null,
            'og_fallback' => $og_tags['og:image'] ?? null,
            'message' => $has_image
                ? 'Image present (twitter:image or og:image fallback)'
                : 'No image found (recommended for engagement)',
            'impact' => 'Image significantly improves Twitter engagement',
        ];
    }

    /**
     * Test for twitter:site (site handle)
     */
    public static function test_has_site_handle(?string $url = null, ?string $html = null): array
    {
        $html = $html ?? self::fetch_html($url ?? home_url('/'));
        $twitter_tags = self::extract_twitter_tags($html);

        return [
            'test' => 'has_site_handle',
            'passed' => isset($twitter_tags['twitter:site']),
            'value' => $twitter_tags['twitter:site'] ?? null,
            'message' => isset($twitter_tags['twitter:site'])
                ? 'Site handle present (optional but recommended)'
                : 'No site handle (twitter:site optional)',
            'impact' => 'Site handle attributes shared content to your Twitter account',
        ];
    }

    /**
     * Analyze HTML for Twitter Card issues
     *
     * @param string $html HTML content
     * @param string $checked_url URL that was checked
     * @return array|null Finding or null
     */
    protected static function analyze_html(string $html, string $checked_url): ?array
    {
        $twitter_tags = self::extract_twitter_tags($html);
        $og_tags = self::extract_og_tags($html);

        // Missing twitter:card = FAIL
        if (!isset($twitter_tags['twitter:card'])) {
            return [
                'id' => 'seo-twitter-cards',
                'title' => 'Missing Twitter Card',
                'description' => 'Your page is missing Twitter Card tags. Without these, your content will appear as plain text when shared on Twitter instead of a rich media card.',
                'color' => '#ff9800',
                'bg_color' => '#fff3e0',
                'kb_link' => 'https://wpshadow.com/kb/twitter-cards/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=diagnostic',
                'training_link' => 'https://wpshadow.com/training/social-media-seo/',
                'auto_fixable' => false,
                'threat_level' => 50,
                'module' => 'SEO',
                'priority' => 2,
                'meta' => [
                    'issue' => 'missing',
                    'has_og_tags' => !empty($og_tags),
                    'fallback_available' => isset($og_tags['og:title']),
                    'checked_url' => $checked_url,
                ],
            ];
        }

        // Check card type validity
        $card_type = $twitter_tags['twitter:card'];
        if (!in_array($card_type, self::VALID_CARD_TYPES, true)) {
            return [
                'id' => 'seo-twitter-cards',
                'title' => 'Invalid Twitter Card Type',
                'description' => sprintf(
                    'Your twitter:card value "%s" is not recognized. Valid types: %s.',
                    $card_type,
                    implode(', ', self::VALID_CARD_TYPES)
                ),
                'color' => '#ff9800',
                'bg_color' => '#fff3e0',
                'kb_link' => 'https://wpshadow.com/kb/twitter-cards/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=diagnostic',
                'training_link' => 'https://wpshadow.com/training/social-media-seo/',
                'auto_fixable' => false,
                'threat_level' => 60,
                'module' => 'SEO',
                'priority' => 2,
                'meta' => [
                    'issue' => 'invalid_card_type',
                    'current_type' => $card_type,
                    'valid_types' => self::VALID_CARD_TYPES,
                    'checked_url' => $checked_url,
                ],
            ];
        }

        // Check recommended tags (with OG fallback)
        $missing_recommended = [];

        if (!isset($twitter_tags['twitter:title']) && !isset($og_tags['og:title'])) {
            $missing_recommended[] = 'twitter:title (no og:title fallback)';
        }

        if (!isset($twitter_tags['twitter:description']) && !isset($og_tags['og:description'])) {
            $missing_recommended[] = 'twitter:description (no og:description fallback)';
        }

        if (!isset($twitter_tags['twitter:image']) && !isset($og_tags['og:image'])) {
            $missing_recommended[] = 'twitter:image (no og:image fallback)';
        }

        // Perfect: has card type and all recommended tags (or OG fallbacks)
        if (empty($missing_recommended)) {
            return null; // PASS
        }

        // Has twitter:card but missing recommended = lower severity warning
        return [
            'id' => 'seo-twitter-cards',
            'title' => 'Incomplete Twitter Card Tags',
            'description' => sprintf(
                'Your Twitter Card is missing %d recommended tag(s): %s. While Twitter can fall back to Open Graph tags, specific Twitter tags provide better control.',
                count($missing_recommended),
                implode(', ', $missing_recommended)
            ),
            'color' => '#ff9800',
            'bg_color' => '#fff3e0',
            'kb_link' => 'https://wpshadow.com/kb/twitter-cards/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=diagnostic',
            'training_link' => 'https://wpshadow.com/training/social-media-seo/',
            'auto_fixable' => false,
            'threat_level' => 40,
            'module' => 'SEO',
            'priority' => 2,
            'meta' => [
                'card_type' => $card_type,
                'missing_recommended' => $missing_recommended,
                'present_tags' => $twitter_tags,
                'og_fallbacks' => $og_tags,
                'checked_url' => $checked_url,
            ],
        ];
    }

    /**
     * Extract Twitter Card tags from HTML
     *
     * @param string $html HTML content
     * @return array Twitter tags
     */
    protected static function extract_twitter_tags(string $html): array
    {
        if (empty($html)) {
            return [];
        }

        $twitter_tags = [];
        preg_match_all('/<meta\s+name=["\']twitter:([^"\']+)["\']\s+content=["\'](.*?)["\']/i', $html, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $name = 'twitter:' . $match[1];
            $content = html_entity_decode($match[2], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $twitter_tags[$name] = $content;
        }

        return $twitter_tags;
    }

    /**
     * Extract Open Graph tags (for fallback checking)
     *
     * @param string $html HTML content
     * @return array OG tags
     */
    protected static function extract_og_tags(string $html): array
    {
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
     * Count required tags present
     *
     * @param array $twitter_tags Twitter tags
     * @return int Count
     */
    protected static function count_required_tags(array $twitter_tags): int
    {
        $count = 0;
        foreach (self::REQUIRED_TAGS as $tag) {
            if (isset($twitter_tags[$tag]) && !empty($twitter_tags[$tag])) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Count recommended tags present
     *
     * @param array $twitter_tags Twitter tags
     * @return int Count
     */
    protected static function count_recommended_tags(array $twitter_tags): int
    {
        $count = 0;
        foreach (self::RECOMMENDED_TAGS as $tag) {
            if (isset($twitter_tags[$tag]) && !empty($twitter_tags[$tag])) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Check if required tags present
     *
     * @param array $twitter_tags Twitter tags
     * @return bool
     */
    protected static function has_required_tags(array $twitter_tags): bool
    {
        foreach (self::REQUIRED_TAGS as $tag) {
            if (!isset($twitter_tags[$tag]) || empty($twitter_tags[$tag])) {
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
            'id' => 'seo-twitter-cards',
            'title' => $title,
            'description' => $description,
            'color' => '#ff5722',
            'bg_color' => '#ffebee',
            'kb_link' => 'https://wpshadow.com/kb/twitter-cards/',
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
    public static function get_name(): string
    {
        return __('Twitter Card Check', 'wpshadow');
    }

    /**
     * Get the description for display
     *
     * @return string
     */
    public static function get_description(): string
    {
        return __('Checks HTML for proper Twitter Card meta tags for Twitter sharing.', 'wpshadow');
    }
}
