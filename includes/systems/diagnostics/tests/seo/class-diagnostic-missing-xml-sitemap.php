<?php
/**
 * Missing XML Sitemap Diagnostic
 *
 * Detects when XML sitemap is not generated or submitted,
 * reducing search engine crawling efficiency.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\SEO
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: Missing XML Sitemap
 *
 * Checks whether XML sitemap is generated and
 * submitted to search engines.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Missing_XML_Sitemap extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'missing-xml-sitemap';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Missing XML Sitemap';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether XML sitemap is generated and submitted';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Whether this diagnostic is auto-fixable
	 *
	 * @var bool
	 */
	protected static $auto_fixable = false;

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for SEO plugins
		$has_seo_plugin = is_plugin_active( 'yoast-seo/wp-seo.php' ) ||
			is_plugin_active( 'rank-math-seo/rank-math.php' ) ||
			is_plugin_active( 'all-in-one-seo-pack/all_in_one_seo_pack.php' );

		// Check if sitemap exists
		$sitemap_url = home_url( '/sitemap.xml' );
		$sitemap_check = wp_remote_get( $sitemap_url );
		$has_sitemap = ! is_wp_error( $sitemap_check ) && wp_remote_retrieve_response_code( $sitemap_check ) === 200;

		if ( ! $has_sitemap && ! $has_seo_plugin ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'Your site doesn\'t have an XML sitemap, which is like not giving Google a map of your content. Sitemaps tell Google: what pages exist, when they were updated, how important they are. Without a sitemap, Google relies on crawling links to discover pages—which is slower and misses some content. Sitemaps are especially important for large sites, new sites, or sites with content that\'s hard to link to.',
					'wpshadow'
				),
				'severity'      => 'medium',
				'threat_level'  => 45,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Search Engine Crawling',
					'potential_gain' => 'Faster discovery of all content',
					'roi_explanation' => 'XML sitemap helps Google discover and index all your content faster, especially important for new or large sites.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/xml-sitemap',
			);
		}

		return null;
	}
}
