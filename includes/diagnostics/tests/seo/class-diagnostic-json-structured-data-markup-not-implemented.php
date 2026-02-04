<?php
/**
 * JSON Structured Data Markup Not Implemented Diagnostic
 *
 * Checks if JSON structured data is implemented.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * JSON Structured Data Markup Not Implemented Diagnostic Class
 *
 * Detects missing JSON structured data.
 *
 * @since 1.6030.2352
 */
class Diagnostic_JSON_Structured_Data_Markup_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'json-structured-data-markup-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'JSON Structured Data Markup Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if JSON structured data is implemented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for SEO plugins that add structured data.
		$seo_plugins = array(
			'wordpress-seo/wp-seo.php'                       => 'Yoast SEO',
			'seo-by-rank-math/rank-math.php'                 => 'Rank Math SEO',
			'all-in-one-seo-pack/all_in_one_seo_pack.php'    => 'All in One SEO',
			'seopress/seopress.php'                          => 'SEOPress',
			'wp-seopress/seopress.php'                       => 'SEOPress',
		);

		$seo_plugin_detected = false;
		$seo_plugin_name     = '';

		foreach ( $seo_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$seo_plugin_detected = true;
				$seo_plugin_name     = $name;
				break;
			}
		}

		// Check for WooCommerce (has built-in product schema).
		$has_woocommerce = is_plugin_active( 'woocommerce/woocommerce.php' );

		// Check for schema plugins.
		$schema_plugins = array(
			'schema-and-structured-data-for-wp/structured-data-for-wp.php' => 'Schema & Structured Data',
			'wp-schema-pro/wp-schema-pro.php'                              => 'WP Schema Pro',
		);

		$schema_plugin = false;
		foreach ( $schema_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$schema_plugin = $name;
				break;
			}
		}

		// If no structured data implementation detected.
		if ( ! $seo_plugin_detected && ! $schema_plugin && ! $has_woocommerce ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'JSON structured data markup not implemented. No SEO plugin detected to add schema.org markup (Organization, BreadcrumbList, Article, etc.). Google uses structured data for rich results: star ratings, breadcrumbs, FAQ accordions in search. Install Yoast SEO or Rank Math for automatic schema generation.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/structured-data-markup',
				'details'     => array(
					'seo_plugin'     => false,
					'schema_plugin'  => false,
					'woocommerce'    => false,
					'recommendation' => __( 'Install Rank Math SEO (free, 1M+ installs) or Yoast SEO (free, 5M+ installs). Both automatically add: Organization schema (your business info), Article schema (blog posts), BreadcrumbList schema (navigation), Person schema (authors), WebSite schema (search box in Google).', 'wpshadow' ),
					'schema_types'   => array(
						'Organization' => 'Business name, logo, social profiles',
						'Article' => 'Blog posts with author, date, headline',
						'BreadcrumbList' => 'Navigation path in search results',
						'Product' => 'E-commerce items with price, ratings',
						'FAQPage' => 'FAQ accordion in search results',
						'HowTo' => 'Step-by-step instructions',
						'Review' => 'Star ratings in search results',
					),
					'rich_results'   => array(
						'star_ratings' => 'Product/review stars in search',
						'breadcrumbs' => 'Navigation path under URL',
						'faq_accordion' => 'Expandable Q&A in results',
						'recipe_cards' => 'Cooking time, calories, ratings',
						'event_listings' => 'Date, location, ticket info',
					),
					'seo_benefits'   => array(
						'visibility' => 'Rich results take more screen space',
						'ctr' => '10-30% higher click-through rate',
						'trust' => 'Star ratings build credibility',
						'voice_search' => 'Structured data helps voice assistants',
					),
					'testing'        => array(
						'tool' => 'Google Rich Results Test',
						'url' => 'https://search.google.com/test/rich-results',
						'validation' => 'Test your pages for schema errors',
					),
				),
			);
		}

		// No issues - structured data implemented.
		return null;
	}
}
