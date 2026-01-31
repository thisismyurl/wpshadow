<?php
/**
 * Sitemap Generation Diagnostic
 *
 * Verifies XML sitemap generated and submitted to
 * search engines for better crawling.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Sitemap_Generation Class
 *
 * Verifies XML sitemap generation.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Sitemap_Generation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'sitemap-generation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Sitemap Generation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies XML sitemap is generated';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'quality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if sitemap missing, null otherwise.
	 */
	public static function check() {
		$sitemap_status = self::check_sitemap();

		if ( $sitemap_status['exists'] ) {
			return null; // Sitemap exists
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'XML sitemap not found. Sitemap = map for Google to find all your content. Without it = only most-linked pages indexed = miss 30-50% of content.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 55,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/xml-sitemap',
			'family'       => self::$family,
			'meta'         => array(
				'sitemap_exists' => false,
			),
			'details'      => array(
				'why_sitemaps_matter'             => array(
					'Discovery' => array(
						'Google crawls homepage → follows links',
						'Deep pages: May never get crawled',
						'Sitemap: Tells Google about all pages',
					),
					'Crawl Budget' => array(
						'Large site: 1000+ pages',
						'Google: May only crawl 100 pages/day',
						'Sitemap: Prioritizes important pages',
					),
					'New Content' => array(
						'New post: Sitemap shows immediately',
						'Without sitemap: May take weeks',
						'Indexing: 24 hours vs. 30 days',
					),
				),
				'types_of_sitemaps'               => array(
					'XML Sitemap' => array(
						'File: sitemap.xml',
						'Purpose: For search engines',
						'Contains: All pages + metadata',
					),
					'HTML Sitemap' => array(
						'File: sitemap.html',
						'Purpose: For users (visual)',
						'Not required: XML is priority',
					),
				),
				'wordpress_sitemap_options'       => array(
					'WordPress Native (5.5+)' => array(
						'Built-in: No plugin needed',
						'Location: /sitemap.xml',
						'Enable: Settings → Reading',
					),
					'Yoast SEO Plugin' => array(
						'Features: Advanced sitemap options',
						'Cost: Free',
						'Extra: Image sitemap, video sitemap',
					),
					'Rank Math Plugin' => array(
						'Features: Comprehensive sitemap',
						'Cost: Free',
						'Extra: Video, news, product feeds',
					),
				),
				'generating_sitemap'              => array(
					'WordPress Native' => array(
						'Go to: wp-admin → Settings → Reading',
						'Setting: "Search engine visibility"',
						'Ensure: NOT "Discourage search engines"',
						'Result: /sitemap.xml auto-generated',
					),
					'Yoast SEO' => array(
						'Install: Plugin',
						'Go to: Yoast → XML Sitemaps',
						'Enable: Toggle "XML Sitemaps"',
						'Result: /sitemap_index.xml created',
					),
				),
				'submitting_sitemap'              => array(
					'Google Search Console' => array(
						'URL: search.google.com/search-console',
						'Add: Sitemaps → New sitemap',
						'Submit: sitemap.xml URL',
						'Verify: Google accepts it',
					),
					'Bing Webmaster Tools' => array(
						'URL: bing.com/webmasters/home',
						'Add: Sitemaps',
						'Submit: sitemap.xml URL',
					),
				),
				'sitemap_best_practices'          => array(
					'Include Important Pages' => array(
						'Product pages: Highest priority',
						'Blog posts: Medium priority',
						'Archive pages: Low priority',
					),
					'Update Frequency' => array(
						'Product changed: Every day',
						'Blog: When posted',
						'Auto-update: Plugins handle',
					),
					'Remove Old Content' => array(
						'Deleted posts: Remove from sitemap',
						'Automatic: Plugins handle',
						'Manual: Verify with Search Console',
					),
				),
				'monitoring_sitemap_health'       => array(
					'Google Search Console' => array(
						'Check: Sitemaps section',
						'Stats: Pages submitted vs. indexed',
						'Issues: Google reports problems',
					),
					'Size Check' => array(
						'Limit: 50K URLs per sitemap',
						'Large sites: Split into index',
						'Auto: Plugins handle splitting',
					),
				),
			),
		);
	}

	/**
	 * Check sitemap existence.
	 *
	 * @since  1.2601.2148
	 * @return array Sitemap status.
	 */
	private static function check_sitemap() {
		$sitemap_urls = array(
			home_url( '/sitemap.xml' ),
			home_url( '/sitemap_index.xml' ),
			home_url( '/post-sitemap.xml' ),
		);

		$exists = false;

		foreach ( $sitemap_urls as $url ) {
			$response = wp_remote_head( $url );
			if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
				$exists = true;
				break;
			}
		}

		return array(
			'exists' => $exists,
		);
	}
}
