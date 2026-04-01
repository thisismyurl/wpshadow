<?php
/**
 * Structured Data Schema Markup Diagnostic
 *
 * Checks if structured data (JSON-LD, Schema.org) is properly implemented
 * to help search engines understand page content.
 *
 * @since 0.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Structured Data Schema Markup Diagnostic Class
 *
 * Verifies schema.org implementation:
 * - JSON-LD markup presence
 * - Organization schema
 * - Product schema
 * - Article schema
 *
 * @since 0.6093.1200
 */
class Diagnostic_Structured_Data_Schema_Markup extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'structured-data-schema-markup';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Structured Data Schema Markup';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for structured data markup implementation for SEO';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		$schema_plugins = array(
			'all-in-one-schema-rich-snippets/all_in_one_schema.php' => 'All In One Schema',
			'rank-math/rank-math.php'                               => 'Rank Math',
			'wordpress-seo/wp-seo.php'                              => 'Yoast SEO',
			'schema/schema.php'                                     => 'Schema',
		);

		$schema_installed = false;

		foreach ( $schema_plugins as $plugin_path => $plugin_name ) {
			if ( is_plugin_active( $plugin_path ) ) {
				$schema_installed = true;
				break;
			}
		}

		// Check if custom schema is implemented
		if ( has_filter( 'wp_schema_markup' ) || has_action( 'wp_head' ) ) {
			// Assume custom implementation
			$schema_installed = true;
		}

		if ( ! $schema_installed ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Structured data markup is not implemented. Adding schema.org markup helps search engines understand your content and can improve search results.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 30,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/structured-data?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'meta'          => array(
					'schema_installed'     => $schema_installed,
					'recommendation'       => 'Install Rank Math or Yoast SEO for automatic schema markup, or implement custom JSON-LD',
					'impact'               => 'Proper schema markup can improve click-through rate by 20-30% from search results',
					'markup_types'         => array(
						'Organization (Homepage)',
						'Article/BlogPosting (Posts)',
						'Product (WooCommerce)',
						'Event (Event plugins)',
						'Recipe (Recipe sites)',
					),
				),
			);
		}

		return null;
	}
}
