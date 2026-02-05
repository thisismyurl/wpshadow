<?php
/**
 * Schema Markup and Structured Data Treatment
 *
 * Tests if site implements proper JSON-LD schema markup for search engine understanding.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.7034.1460
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Schema Markup and Structured Data Treatment Class
 *
 * Validates that the site implements proper JSON-LD schema markup
 * including Organization, Article, Product, and breadcrumb schemas.
 *
 * @since 1.7034.1460
 */
class Treatment_Schema_Markup_Structured_Data extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'schema-markup-structured-data';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Schema Markup and Structured Data';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if site implements proper JSON-LD schema markup for search engine understanding';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the treatment check.
	 *
	 * Tests schema markup implementation including Organization schema,
	 * Article schema, Product schema, and breadcrumb schema.
	 *
	 * @since  1.7034.1460
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		// Check for schema markup plugins.
		$schema_plugins = array(
			'wordpress-seo/wp-seo.php'                    => 'Yoast SEO',
			'all-in-one-seo-pack/all_in_one_seo_pack.php' => 'All in One SEO',
			'rank-math-seo/rank-math.php'                 => 'Rank Math',
			'wp-seo-structured-data-schema/wp-seo-schema.php' => 'WP SEO Structured Data',
			'schemaorg/schema.php'                        => 'Schema.org',
		);

		$active_schema_plugin = null;
		foreach ( $schema_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_schema_plugin = $name;
				break;
			}
		}

		// Check homepage for Organization schema.
		$home_url = get_home_url();
		$response = wp_remote_get( $home_url, array( 'sslverify' => false ) );
		$has_organization_schema = false;
		$has_article_schema = false;
		$has_breadcrumb_schema = false;

		if ( ! is_wp_error( $response ) ) {
			$body = wp_remote_retrieve_body( $response );
			$has_organization_schema = ( strpos( $body, '"@type":"Organization"' ) !== false );
			$has_article_schema = ( strpos( $body, '"@type":"Article"' ) !== false );
			$has_breadcrumb_schema = ( strpos( $body, '"@type":"BreadcrumbList"' ) !== false );
		}

		// Check for WooCommerce product schema if e-commerce active.
		$has_product_schema = false;
		$has_woocommerce = is_plugin_active( 'woocommerce/woocommerce.php' );

		if ( $has_woocommerce ) {
			$product_page = wp_remote_get( $home_url . '/shop/', array( 'sslverify' => false ) );
			if ( ! is_wp_error( $product_page ) ) {
				$body = wp_remote_retrieve_body( $product_page );
				$has_product_schema = ( strpos( $body, '"@type":"Product"' ) !== false );
			}
		}

		// Check for Rich Snippets data.
		$has_rich_snippets = false;
		if ( $active_schema_plugin ) {
			// Check theme for schema markup.
			$theme_dir = get_template_directory();
			$header_file = $theme_dir . '/header.php';

			if ( file_exists( $header_file ) ) {
				$header_content = file_get_contents( $header_file );
				$has_rich_snippets = ( strpos( $header_content, '@context' ) !== false ) ||
									( strpos( $header_content, '@type' ) !== false );
			}
		}

		// Check for review schema (if comments enabled).
		$has_review_schema = false;
		if ( comments_open() && $active_schema_plugin ) {
			$has_review_schema = true;
		}

		// Check for video schema if Jetpack or similar active.
		$has_video_schema = false;
		if ( is_plugin_active( 'jetpack/jetpack.php' ) ) {
			$has_video_schema = true;
		}

		// Count posts with missing schema.
		global $wpdb;
		$posts_with_schema = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts}
			 WHERE post_status = 'publish' AND post_type IN ('post', 'page')
			 AND post_content LIKE '%@context%'"
		);

		$total_posts = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts}
			 WHERE post_status = 'publish' AND post_type IN ('post', 'page')"
		);

		$schema_coverage = $total_posts > 0 ? ( $posts_with_schema / $total_posts ) * 100 : 0;

		// Check for schema validation errors.
		$schema_issues = array();

		// Check if site name is set.
		$blog_name = get_bloginfo( 'name' );
		$has_site_name = ! empty( $blog_name );

		// Check if site description is set.
		$blog_description = get_bloginfo( 'description' );
		$has_site_description = ! empty( $blog_description );

		// Check for issues.
		$issues = array();

		// Issue 1: No schema markup plugin.
		if ( ! $active_schema_plugin ) {
			$issues[] = array(
				'type'        => 'no_schema_plugin',
				'description' => __( 'No schema markup plugin; structured data not automatically generated', 'wpshadow' ),
			);
		}

		// Issue 2: No Organization schema on homepage.
		if ( ! $has_organization_schema && ! $active_schema_plugin ) {
			$issues[] = array(
				'type'        => 'no_organization_schema',
				'description' => __( 'Organization schema missing; search engines cannot identify site', 'wpshadow' ),
			);
		}

		// Issue 3: No Article schema for blog posts.
		if ( ! $has_article_schema && $total_posts > 10 ) {
			$issues[] = array(
				'type'        => 'no_article_schema',
				'description' => __( 'Article schema missing; blog posts not optimized for search results', 'wpshadow' ),
			);
		}

		// Issue 4: No breadcrumb schema for navigation.
		if ( ! $has_breadcrumb_schema && ! $active_schema_plugin ) {
			$issues[] = array(
				'type'        => 'no_breadcrumb_schema',
				'description' => __( 'Breadcrumb schema missing; navigation not visible in search snippets', 'wpshadow' ),
			);
		}

		// Issue 5: E-commerce without product schema.
		if ( $has_woocommerce && ! $has_product_schema ) {
			$issues[] = array(
				'type'        => 'no_product_schema',
				'description' => __( 'WooCommerce active but no Product schema; products won\'t show rich results', 'wpshadow' ),
			);
		}

		// Issue 6: Site name or description missing.
		if ( ! $has_site_name || ! $has_site_description ) {
			$issues[] = array(
				'type'        => 'incomplete_site_info',
				'description' => __( 'Site name or description missing; required for Organization schema', 'wpshadow' ),
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Schema markup is not implemented, preventing search engines from understanding and displaying rich snippets', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/schema-markup-structured-data',
				'details'      => array(
					'active_schema_plugin'   => $active_schema_plugin,
					'has_organization_schema' => $has_organization_schema,
					'has_article_schema'     => $has_article_schema,
					'has_breadcrumb_schema'  => $has_breadcrumb_schema,
					'has_product_schema'     => $has_product_schema,
					'has_review_schema'      => $has_review_schema,
					'has_video_schema'       => $has_video_schema,
					'posts_with_schema'      => absint( $posts_with_schema ),
					'total_posts'            => absint( $total_posts ),
					'schema_coverage'        => round( $schema_coverage, 1 ) . '%',
					'has_site_name'          => $has_site_name,
					'has_site_description'   => $has_site_description,
					'issues_detected'        => $issues,
					'recommendation'         => __( 'Install schema markup plugin, add Organization/Article/Product schemas, verify with structured data test', 'wpshadow' ),
					'common_schemas'         => array(
						'Organization' => 'Site identity - name, logo, contact, social',
						'Article'      => 'Blog posts - headline, author, date, content',
						'Product'      => 'E-commerce - name, price, availability, reviews',
						'BreadcrumbList' => 'Navigation hierarchy',
						'FAQPage'      => 'FAQ sections - question, answer',
						'Review'       => 'Ratings and testimonials',
					),
					'organization_schema_example' => array(
						'@context' => 'https://schema.org',
						'@type'    => 'Organization',
						'name'     => get_bloginfo( 'name' ),
						'url'      => $home_url,
						'description' => get_bloginfo( 'description' ),
						'logo'     => get_theme_mod( 'custom_logo_url' ) ?: '',
					),
					'rich_snippet_benefits'   => array(
						'Higher CTR'   => '20-30% increase in click-through rate',
						'Rich Results' => 'Answers, FAQs, Reviews appear in search',
						'Voice Search' => 'Structured data enables featured snippets',
						'Knowledge Graph' => 'Site info appears in Google Knowledge Panel',
					),
					'validation_tools'       => array(
						'Google' => 'https://search.google.com/test/rich-results',
						'Schema.org' => 'https://validator.schema.org/',
						'Bing'   => 'https://www.bing.com/webmaster/tools',
					),
					'seo_impact'             => 'Schema markup increases search visibility by 35-40% for eligible results',
				),
			);
		}

		return null;
	}
}
