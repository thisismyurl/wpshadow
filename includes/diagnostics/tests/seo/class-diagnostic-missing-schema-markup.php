<?php
/**
 * Missing Schema Markup Diagnostic
 *
 * Detects missing structured data (schema.org) that helps search engines
 * understand content and improves rich snippets in search results.
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
 * Diagnostic_Missing_Schema_Markup Class
 *
 * Checks for structured data/JSON-LD implementation.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Missing_Schema_Markup extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'missing-schema-markup';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Missing Schema Markup Analysis';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects missing structured data for search engine understanding';

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
	 * @return array|null Finding array if schema issues detected, null otherwise.
	 */
	public static function check() {
		$schema_status = self::check_schema_markup();

		if ( $schema_status['has_schema'] ) {
			return null; // Schema is implemented
		}

		return array(
			'id'            => self::$slug,
			'title'         => self::$title,
			'description'   => __( 'No structured data (schema.org) detected. Rich snippets are missing, reducing search visibility.', 'wpshadow' ),
			'severity'      => 'medium',
			'threat_level'  => 55,
			'auto_fixable'  => false,
			'kb_link'       => 'https://wpshadow.com/kb/schema-markup-setup',
			'family'        => self::$family,
			'meta'          => array(
				'schema_implemented'   => false,
				'schema_types_missing' => array(
					'Organization',
					'Article',
					'WebSite',
					'BreadcrumbList',
					'Product (if e-commerce)',
					'FAQPage',
				),
				'seo_benefit'         => __( 'Rich snippets improve CTR by 20-30% in search results' ),
				'competitive_advantage' => __( 'Many competitors already using schema markup' ),
			),
			'details'       => array(
				'what_is_schema'     => array(
					'Definition' => __( 'Structured data format using schema.org vocabulary' ),
					'Format' => __( 'JSON-LD embedded in <script> tags (recommended)' ),
					'Purpose' => __( 'Helps search engines understand your content' ),
					'Benefit' => __( 'Enables rich snippets, enhanced search results' ),
				),
				'schema_types_for_wordpress' => array(
					'Organization' => 'Company info, logo, contact details',
					'Article' => 'Blog posts, news articles',
					'WebSite' => 'Site-wide info, logo, search functionality',
					'BreadcrumbList' => 'Navigation hierarchy, breadcrumb links',
					'Product' => 'WooCommerce products, pricing, reviews',
					'FAQPage' => 'FAQ sections with questions and answers',
					'Event' => 'Event dates, locations, tickets',
					'LocalBusiness' => 'Brick & mortar shops with location',
				),
				'quick_setup'        => array(
					'Option 1: Yoast SEO (Easiest)' => array(
						'Install Yoast SEO plugin',
						'Go to Settings → Search Appearance → Schema',
						'Enable Organization schema, choose schema types',
						'Auto-generates JSON-LD for posts/pages',
					),
					'Option 2: Rank Math' => array(
						'Install Rank Math plugin',
						'Settings → Schema → Select schema types',
						'Simpler than Yoast, excellent defaults',
					),
					'Option 3: Manual' => array(
						'Use schema.org generator tools',
						'Add JSON-LD script to theme footer',
						'Verify with Google Rich Results Test',
					),
				),
				'rich_snippet_examples' => array(
					'Review Stars' => 'Shows 5-star rating in search results',
					'Breadcrumbs' => 'Shows page hierarchy in search results',
					'Article Details' => 'Shows publication date, author, image',
					'FAQ Rich Results' => 'Shows Q&A directly in search results',
					'Product Info' => 'Shows price, availability, reviews',
				),
				'validation_and_testing' => array(
					'Google Rich Results Test' => array(
						'Visit: https://search.google.com/test/rich-results',
						'Paste your site URL',
						'See what rich snippets appear',
						'Check for schema errors or warnings',
					),
					'Schema.org Validator' => array(
						'Visit: https://validator.schema.org/',
						'Paste your page HTML',
						'Validates schema syntax',
					),
					'Google Search Console' => array(
						'Check "Rich Results" report',
						'See which pages have valid schema',
						'Monitor for validation errors',
					),
				),
			),
		);
	}

	/**
	 * Check for schema markup implementation.
	 *
	 * @since  1.2601.2148
	 * @return array Schema status.
	 */
	private static function check_schema_markup() {
		// Check if SEO plugin with schema is active
		$seo_plugins_with_schema = array(
			'wordpress-seo/wp-seo.php' => 'Yoast SEO',
			'rank-math/rank-math.php' => 'Rank Math',
			'all-in-one-seo-pack/all_in_one_seo_pack.php' => 'All in One SEO',
		);

		foreach ( $seo_plugins_with_schema as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				return array(
					'has_schema' => true,
					'plugin'    => $name,
				);
			}
		}

		// Check page HTML for JSON-LD
		$response = wp_remote_get( home_url() );
		if ( ! is_wp_error( $response ) ) {
			$body = wp_remote_retrieve_body( $response );
			if ( strpos( $body, '"@type"' ) !== false || strpos( $body, '"@context"' ) !== false ) {
				return array(
					'has_schema' => true,
					'type'      => 'JSON-LD found in HTML',
				);
			}
		}

		return array(
			'has_schema' => false,
			'type'      => 'No schema markup detected',
		);
	}
}
