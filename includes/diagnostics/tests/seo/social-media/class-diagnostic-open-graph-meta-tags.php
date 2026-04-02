<?php
/**
 * Open Graph & Meta Tags for Social Sharing
 *
 * Validates proper Open Graph implementation for social media content preview.
 *
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Diagnostics\Helpers\Diagnostic_HTML_Helper;
use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Open_Graph_Meta_Tags Class
 *
 * Checks if Open Graph meta tags are properly implemented for social media sharing.
 * These tags control how content appears when shared on Facebook, LinkedIn, and other platforms.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Open_Graph_Meta_Tags extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'open-graph-meta-tags';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Open Graph Meta Tags';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates Open Graph implementation for social sharing';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'social-media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Check if social media optimization plugins are installed
		$has_seo_plugin = self::has_seo_plugin();
		$has_social_plugin = self::has_social_plugin();

		// Get home page content
		$homepage_content = Diagnostic_HTML_Helper::fetch_html( home_url() );

		if ( null === $homepage_content ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Unable to validate Open Graph tags (homepage unreachable)', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/open-graph-meta-tags',
				'details'      => array(
					'issue' => 'homepage_unreachable',
					'message' => __( 'Could not fetch homepage to validate Open Graph tags', 'wpshadow' ),
				),
			);
		}

		// Pattern 1: No Open Graph tags at all
		if ( ! Diagnostic_URL_And_Pattern_Helper::has_meta_tag( $homepage_content, Diagnostic_URL_And_Pattern_Helper::PATTERN_OG_TITLE ) ) {
			if ( ! $has_seo_plugin && ! $has_social_plugin ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Your site could display better previews when shared on social media (Facebook, LinkedIn, Twitter). Open Graph tags are like instructions that tell social networks which image and text to show. Think of it like the thumbnail and title on a YouTube video.', 'wpshadow' ),
					'severity'     => 'medium',
					'threat_level' => 65,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/open-graph-meta-tags',
					'details'      => array(
						'issue' => 'missing_og_tags',
						'message' => __( 'Open Graph meta tags are not present on your site', 'wpshadow' ),
						'recommendation' => __( 'Install a social media optimization plugin or SEO plugin that adds Open Graph support', 'wpshadow' ),
						'solutions' => array(
							'Yoast SEO - Includes full Open Graph support',
							'Rank Math - Comprehensive social meta management',
							'All in One SEO - Social optimization included',
							'The SEO Framework - Built-in social settings',
							'Social Warfare - Dedicated social optimization',
						),
						'business_impact' => __( 'Missing OG tags can reduce social sharing CTR by 30-40%', 'wpshadow' ),
						'social_platforms' => 'Facebook, LinkedIn, Pinterest, Discord, Slack',
						'standards' => 'Open Graph Protocol (ogp.me)',
					),
				);
			}
		}

		// Pattern 2: Incomplete Open Graph tags (missing critical tags)
		$required_tags = array( 'og:title', 'og:description', 'og:image', 'og:url' );
		$missing_tags = array();

		foreach ( $required_tags as $tag ) {
			if ( ! preg_match( '/<meta\s+property=["\']' . preg_quote( $tag ) . '["\']/', $homepage_content ) ) {
				$missing_tags[] = $tag;
			}
		}

		if ( ! empty( $missing_tags ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Incomplete Open Graph tags configuration', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/open-graph-meta-tags',
				'details'      => array(
					'issue' => 'incomplete_og_tags',
						'message' => sprintf(
							/* translators: %s: missing tag names */
							__( 'Missing critical Open Graph tags: %s', 'wpshadow' ),
							implode( ', ', $missing_tags )
						),
						'recommendation' => __( 'Ensure your SEO or social plugin outputs all required Open Graph meta tags', 'wpshadow' ),
						'required_tags' => array(
							'og:title' => 'Page title for social preview',
							'og:description' => 'Page description (50-300 chars)',
							'og:image' => 'Image for social preview (1200x630px minimum)',
							'og:url' => 'Canonical URL',
							'og:type' => 'Content type (website, article, etc.)',
						),
						'current_missing' => $missing_tags,
						'social_preview_impact' => __( 'Posts without images get 40% fewer shares', 'wpshadow' ),
						'user_perception' => __( 'Incomplete previews look unprofessional and reduce engagement', 'wpshadow' ),
					),
			);
		}

		// Pattern 3: Image tag without proper image metadata
		if ( preg_match( '/<meta\s+property=["\']og:image["\']/', $homepage_content ) ) {
			if ( ! preg_match( '/<meta\s+property=["\']og:image:width/', $homepage_content ) || 
				 ! preg_match( '/<meta\s+property=["\']og:image:height/', $homepage_content ) ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Open Graph image lacks dimension metadata', 'wpshadow' ),
					'severity'     => 'low',
					'threat_level' => 40,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/open-graph-meta-tags',
					'details'      => array(
						'issue' => 'missing_image_dimensions',
						'message' => __( 'og:image present but missing width/height metadata', 'wpshadow' ),
						'recommendation' => __( 'Add og:image:width and og:image:height meta tags', 'wpshadow' ),
						'image_specs' => array(
							'minimum_width' => 1200,
							'minimum_height' => 630,
							'aspect_ratio' => '1.91:1',
							'recommended_size' => '1200x630',
							'max_size' => '5000x5000',
						),
						'benefit' => __( 'Dimensions help platforms select proper display size without distortion', 'wpshadow' ),
					),
				);
			}
		}

		// Pattern 4: Using local images instead of fully qualified URLs
		if ( preg_match( '/<meta\s+property=["\']og:image["\'].*?content=["\']([^"\']+)["\']/', $homepage_content, $matches ) ) {
			$image_url = $matches[1];
			if ( strpos( $image_url, 'http' ) !== 0 ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Open Graph image URL is not fully qualified', 'wpshadow' ),
					'severity'     => 'medium',
					'threat_level' => 50,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/open-graph-meta-tags',
					'details'      => array(
						'issue' => 'relative_image_urls',
						'message' => __( 'og:image contains relative path instead of absolute URL', 'wpshadow' ),
						'current_url' => $image_url,
						'expected_format' => 'https://example.com/path/to/image.jpg',
						'why_important' => __( 'Social crawlers need full URLs to fetch images from different domain', 'wpshadow' ),
						'impact' => __( 'Social platforms may not be able to display preview image', 'wpshadow' ),
					),
				);
			}
		}

		// Pattern 5: Missing alternate language versions (for multilingual sites)
		$is_multilingual = self::is_multilingual_site();
		if ( $is_multilingual && ! preg_match( '/<meta\s+property=["\']og:locale:alternate/', $homepage_content ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Multilingual site missing alternate locale Open Graph tags', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/open-graph-meta-tags',
				'details'      => array(
					'issue' => 'missing_locale_alternates',
					'message' => __( 'og:locale:alternate tags not found for other language versions', 'wpshadow' ),
					'recommendation' => __( 'Add alternate locale meta tags for each language version', 'wpshadow' ),
					'example_format' => array(
						'<meta property="og:locale" content="en_US" />',
						'<meta property="og:locale:alternate" content="fr_FR" />',
						'<meta property="og:locale:alternate" content="es_ES" />',
					),
					'benefit' => __( 'Helps social platforms display content in user\'s preferred language', 'wpshadow' ),
					'user_engagement' => __( 'Localized previews increase engagement by 25-40%', 'wpshadow' ),
				),
			);
		}

		// Pattern 6: Missing article-specific tags for blog posts
		if ( is_home() || is_category() || is_tag() ) {
			if ( ! preg_match( '/<meta\s+property=["\']article:published_time/', $homepage_content ) ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Blog lacking article-specific Open Graph tags', 'wpshadow' ),
					'severity'     => 'low',
					'threat_level' => 45,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/open-graph-meta-tags',
					'details'      => array(
						'issue' => 'missing_article_tags',
						'message' => __( 'Article pages missing published_time and other article-specific tags', 'wpshadow' ),
						'article_tags' => array(
							'article:published_time' => 'ISO 8601 format (2025-02-03T10:00:00Z)',
							'article:modified_time' => 'Last update timestamp',
							'article:author' => 'Author URL or name',
							'article:section' => 'Content category/section',
							'article:tag' => 'Individual article tags/keywords',
						),
						'benefit' => __( 'Article tags provide rich preview with publication metadata', 'wpshadow' ),
						'engagement_impact' => __( 'Facebook prioritizes article shares with proper metadata', 'wpshadow' ),
					),
				);
			}
		}

		return null; // No issues found
	}

	/**
	 * Check if SEO plugin is active.
	 *
	 * @since 1.6093.1200
	 * @return bool True if SEO plugin active.
	 */
	private static function has_seo_plugin() {
		$seo_plugins = array(
			'wordpress-seo/wp-seo.php',
			'all-in-one-seo-pack/all_in_one_seo_pack.php',
			'seo-by-rank-math/rank-math.php',
			'the-seo-framework/the-seo-framework.php',
			'seopress/seopress.php',
		);

		foreach ( $seo_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if social media plugin is active.
	 *
	 * @since 1.6093.1200
	 * @return bool True if social plugin active.
	 */
	private static function has_social_plugin() {
		$social_plugins = array(
			'social-warfare/index.php',
			'social-pug/index.php',
			'jetpack/jetpack.php',
			'sharethis-share-buttons/sharethis.php',
		);

		foreach ( $social_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if site is multilingual.
	 *
	 * @since 1.6093.1200
	 * @return bool True if multilingual plugins detected.
	 */
	private static function is_multilingual_site() {
		$multilingual_plugins = array(
			'polylang/polylang.php',
			'wpml/sitepress.php',
			'translatepress-multilingual/index.php',
		);

		foreach ( $multilingual_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}
}
