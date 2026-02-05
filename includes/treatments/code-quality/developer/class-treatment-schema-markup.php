<?php
/**
 * Schema Markup Treatment
 *
 * Checks if theme includes proper Schema.org structured data markup.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6035.1300
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Schema Markup Treatment Class
 *
 * Verifies that the theme includes proper Schema.org structured data
 * for better SEO and rich snippets.
 *
 * @since 1.6035.1300
 */
class Treatment_Schema_Markup extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'schema-markup';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Schema.org Structured Data';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if theme includes proper Schema.org structured data markup';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'developer';

	/**
	 * Run the schema markup treatment check.
	 *
	 * @since  1.6035.1300
	 * @return array|null Finding array if schema issues detected, null otherwise.
	 */
	public static function check() {
		$theme     = wp_get_theme();
		$theme_dir = $theme->get_stylesheet_directory();
		$issues    = array();
		$warnings  = array();
		$found_schemas = array();

		// Common schema types to check for.
		$schema_types = array(
			'Organization',
			'WebSite',
			'WebPage',
			'Article',
			'BlogPosting',
			'Person',
			'BreadcrumbList',
		);

		// Check header.php for schema.
		$header_file = $theme_dir . '/header.php';
		if ( file_exists( $header_file ) ) {
			$header_content = file_get_contents( $header_file );
			
			// Check for itemscope/itemtype (Microdata).
			if ( strpos( $header_content, 'itemscope' ) !== false ) {
				$found_schemas[] = 'Microdata';
			}
			
			// Check for JSON-LD script tags.
			if ( strpos( $header_content, 'application/ld+json' ) !== false ) {
				$found_schemas[] = 'JSON-LD';
			}
		}

		// Check common template files for schema.
		$template_files = array(
			$theme_dir . '/single.php',
			$theme_dir . '/content.php',
			$theme_dir . '/template-parts/content.php',
			$theme_dir . '/inc/template-tags.php',
		);

		foreach ( $template_files as $file ) {
			if ( file_exists( $file ) ) {
				$content = file_get_contents( $file );
				
				// Check for schema.org types.
				foreach ( $schema_types as $type ) {
					if ( strpos( $content, 'schema.org/' . $type ) !== false ||
						 strpos( $content, '"@type": "' . $type . '"' ) !== false ) {
						$found_schemas[] = $type;
					}
				}
				
				// Check for itemscope/itemtype.
				if ( strpos( $content, 'itemscope' ) !== false && 
					 ! in_array( 'Microdata', $found_schemas, true ) ) {
					$found_schemas[] = 'Microdata';
				}
			}
		}

		// Check functions.php for schema implementations.
		$functions_php = $theme_dir . '/functions.php';
		if ( file_exists( $functions_php ) ) {
			$functions_content = file_get_contents( $functions_php );
			
			// Check for schema-related functions.
			if ( strpos( $functions_content, 'schema' ) !== false ||
				 strpos( $functions_content, 'structured_data' ) !== false ||
				 strpos( $functions_content, 'json_ld' ) !== false ) {
				$found_schemas[] = 'Custom Implementation';
			}
		}

		// Remove duplicates.
		$found_schemas = array_unique( $found_schemas );

		// Check if any schema was found.
		if ( empty( $found_schemas ) ) {
			$issues[] = __( 'No Schema.org structured data detected', 'wpshadow' );
		} else {
			// Check for essential schema types.
			$has_article_schema = false;
			$has_organization_schema = false;
			
			foreach ( $found_schemas as $schema ) {
				if ( in_array( $schema, array( 'Article', 'BlogPosting' ), true ) ) {
					$has_article_schema = true;
				}
				if ( $schema === 'Organization' || $schema === 'WebSite' ) {
					$has_organization_schema = true;
				}
			}
			
			if ( ! $has_article_schema ) {
				$warnings[] = __( 'Missing Article/BlogPosting schema for blog posts', 'wpshadow' );
			}
			
			if ( ! $has_organization_schema ) {
				$warnings[] = __( 'Missing Organization/WebSite schema for site identity', 'wpshadow' );
			}
		}

		// Check for breadcrumb schema.
		$breadcrumb_found = false;
		foreach ( glob( $theme_dir . '/*.php' ) as $file ) {
			$content = file_get_contents( $file );
			if ( strpos( $content, 'BreadcrumbList' ) !== false ||
				 strpos( $content, 'breadcrumb' ) !== false ) {
				$breadcrumb_found = true;
				break;
			}
		}

		if ( ! $breadcrumb_found ) {
			$warnings[] = __( 'Missing BreadcrumbList schema - improves navigation', 'wpshadow' );
		}

		// Check if using SEO plugin that handles schema.
		$seo_plugins = array(
			'wordpress-seo/wp-seo.php',        // Yoast SEO.
			'all-in-one-seo-pack/all_in_one_seo_pack.php',
			'seo-by-rank-math/rank-math.php',
		);

		$has_seo_plugin = false;
		foreach ( $seo_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_seo_plugin = true;
				break;
			}
		}

		// If critical issues found and no SEO plugin.
		if ( ! empty( $issues ) && ! $has_seo_plugin ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Theme lacks Schema.org structured data: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/schema-markup',
				'context'      => array(
					'theme_name'      => $theme->get( 'Name' ),
					'found_schemas'   => $found_schemas,
					'has_seo_plugin'  => $has_seo_plugin,
					'issues'          => $issues,
					'warnings'        => $warnings,
				),
			);
		}

		// If only warnings.
		if ( ! empty( $warnings ) && ! $has_seo_plugin ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Schema markup has recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/schema-markup',
				'context'      => array(
					'theme_name'      => $theme->get( 'Name' ),
					'found_schemas'   => $found_schemas,
					'has_seo_plugin'  => $has_seo_plugin,
					'warnings'        => $warnings,
				),
			);
		}

		return null; // Schema markup is properly implemented or handled by SEO plugin.
	}
}
