<?php
/**
 * XML Sitemap Status Diagnostic
 *
 * Verifies that XML sitemaps are configured and accessible for search
 * engine crawlers to discover and index all pages.
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
 * Diagnostic_XML_Sitemap_Status Class
 *
 * Checks for XML sitemap availability and validity.
 *
 * @since 1.2601.2148
 */
class Diagnostic_XML_Sitemap_Status extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'xml-sitemap-status';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'XML Sitemap Availability Check';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies XML sitemaps are configured for search engines';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if sitemap issues detected, null otherwise.
	 */
	public static function check() {
		$sitemap_status = self::check_sitemap_status();

		if ( $sitemap_status['is_accessible'] ) {
			return null; // Sitemap is good
		}

		return array(
			'id'            => self::$slug,
			'title'         => self::$title,
			'description'   => sprintf(
				/* translators: %s: issue */
				__( 'XML sitemap issue: %s. Search engines need sitemaps to discover and index all pages.', 'wpshadow' ),
				$sitemap_status['issue']
			),
			'severity'      => 'medium',
			'threat_level'  => 55,
			'auto_fixable'  => false,
			'kb_link'       => 'https://wpshadow.com/kb/xml-sitemap-setup',
			'family'        => self::$family,
			'meta'          => array(
				'sitemap_status'     => $sitemap_status['is_accessible'] ? 'Active' : 'Inactive',
				'issue'              => $sitemap_status['issue'],
				'seo_impact'         => __( 'Search engines may not discover all pages, reducing organic traffic' ),
				'quick_solution'     => $sitemap_status['solution'],
			),
			'details'       => array(
				'issue_details' => $sitemap_status['issue'],
				'setup_options' => array(
					'Option 1: Yoast SEO (Recommended)' => array(
						'Install Yoast SEO plugin',
						'Settings → XML Sitemaps (toggle ON)',
						'Automatically generates sitemaps for posts, pages, taxonomies',
						'Submits to Google/Bing automatically',
					),
					'Option 2: Rank Math' => array(
						'Install Rank Math plugin',
						'Automatic sitemap generation',
						'One-click submission to search engines',
						'Advanced features: video, image, news sitemaps',
					),
					'Option 3: Manual / WordPress Native' => array(
						'Activate: Settings → Reading → "Search Engine Visibility"',
						'WordPress native sitemaps (in newer versions)',
						'Limited but works for basic blogs',
					),
				),
				'sitemap_types'   => array(
					'Post Sitemap' => 'Lists all published posts (primary)',
					'Page Sitemap' => 'Lists all pages',
					'Category Sitemap' => 'Lists category archives',
					'Tag Sitemap' => 'Lists tag archives',
					'Image Sitemap' => 'Helps Google discover and index images',
					'Video Sitemap' => 'For WooCommerce product videos',
				),
				'submission_to_search_engines' => array(
					'Google Search Console' => array(
						'Go to: https://search.google.com/search-console',
						'Add property for your domain',
						'Submit sitemap via "Sitemaps" menu',
						'Check indexation status',
					),
					'Bing Webmaster Tools' => array(
						'Go to: https://www.bing.com/webmaster',
						'Submit sitemap in "Sitemaps" section',
						'Monitor crawl stats and errors',
					),
					'Auto-Submission' => array(
						'Yoast/Rank Math automatically notify Google/Bing',
						'Still should manually verify in Search Console',
					),
				),
				'verification_steps' => array(
					'Step 1' => __( 'Visit: site.com/sitemap.xml (or site.com/sitemap_index.xml)' ),
					'Step 2' => __( 'Should see XML with <urlset> and <url> entries' ),
					'Step 3' => __( 'If not found (404), install Yoast SEO or Rank Math' ),
					'Step 4' => __( 'Verify sitemap appears in Google Search Console' ),
					'Step 5' => __( 'Monitor indexation: should see pages being discovered' ),
				),
			),
		);
	}

	/**
	 * Check XML sitemap status.
	 *
	 * @since  1.2601.2148
	 * @return array Sitemap status.
	 */
	private static function check_sitemap_status() {
		$sitemaps_to_check = array(
			'/sitemap.xml',
			'/sitemap_index.xml',
			'/wp-sitemap.xml',
		);

		foreach ( $sitemaps_to_check as $sitemap_path ) {
			$sitemap_url = home_url() . $sitemap_path;
			$response = wp_remote_head( $sitemap_url );

			if ( ! is_wp_error( $response ) ) {
				$code = wp_remote_retrieve_response_code( $response );
				if ( $code === 200 ) {
					return array(
						'is_accessible' => true,
						'url'           => $sitemap_url,
						'issue'         => 'None',
						'solution'      => 'Sitemap is properly configured',
					);
				}
			}
		}

		// Check if SEO plugin that generates sitemaps is active
		$seo_plugins = array(
			'wordpress-seo/wp-seo.php' => 'Yoast SEO',
			'rank-math/rank-math.php' => 'Rank Math',
			'all-in-one-seo-pack/all_in_one_seo_pack.php' => 'All in One SEO',
		);

		foreach ( $seo_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				return array(
					'is_accessible' => false,
					'issue'         => "{$name} is active but sitemaps may not be enabled in settings",
					'solution'      => "Go to {$name} settings and enable XML Sitemaps",
				);
			}
		}

		return array(
			'is_accessible' => false,
			'issue'         => 'No XML sitemap found and no SEO plugin detected',
			'solution'      => 'Install Yoast SEO or Rank Math plugin and enable XML Sitemaps',
		);
	}
}
