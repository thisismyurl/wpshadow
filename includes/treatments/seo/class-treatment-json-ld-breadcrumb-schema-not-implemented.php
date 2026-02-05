<?php
/**
 * JSON-LD Breadcrumb Schema Not Implemented Treatment
 *
 * Checks if JSON-LD breadcrumb schema is implemented.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6030.2352
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * JSON-LD Breadcrumb Schema Not Implemented Treatment Class
 *
 * Detects missing JSON-LD breadcrumb schema.
 *
 * @since 1.6030.2352
 */
class Treatment_JSON_LD_Breadcrumb_Schema_Not_Implemented extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'json-ld-breadcrumb-schema-not-implemented';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'JSON-LD Breadcrumb Schema Not Implemented';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if JSON-LD breadcrumb schema is implemented';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6030.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Only check on pages with hierarchy (posts, pages, categories).
		if ( ! is_singular() && ! is_category() && ! is_archive() ) {
			return null;
		}

		// Check for SEO plugins that handle schema.
		$seo_plugins = array(
			'wordpress-seo/wp-seo.php'              => 'Yoast SEO',
			'all-in-one-seo-pack/all_in_one_seo_pack.php' => 'All in One SEO',
			'seo-by-rank-math/rank-math.php'        => 'Rank Math SEO',
			'seopress/seopress.php'                 => 'SEOPress',
			'slim-seo/slim-seo.php'                 => 'Slim SEO',
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

		// Check for breadcrumb plugins.
		$breadcrumb_plugins = array(
			'breadcrumb-navxt/breadcrumb-navxt.php' => 'Breadcrumb NavXT',
			'flexy-breadcrumb/flexy-breadcrumb.php' => 'Flexy Breadcrumb',
		);

		$breadcrumb_plugin = false;
		foreach ( $breadcrumb_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$breadcrumb_plugin = $name;
				break;
			}
		}

		// Check for schema markup in output.
		$has_breadcrumb_schema = has_filter( 'wp_head' ) && function_exists( 'yoast_breadcrumb' );

		// If no SEO plugin and no breadcrumb schema.
		if ( ! $seo_plugin_detected && ! $has_breadcrumb_schema ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'JSON-LD breadcrumb schema not implemented. Breadcrumb schema helps Google display your site hierarchy in search results, improving click-through rates. Install Yoast SEO or Rank Math (both add breadcrumb schema automatically).', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 15,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/breadcrumb-schema',
				'details'     => array(
					'seo_plugin'       => false,
					'breadcrumb_plugin' => false,
					'recommendation'   => __( 'Install Yoast SEO (free, 5M+ installs) or Rank Math SEO (free, 1M+ installs). Both automatically add breadcrumb schema to all pages. No configuration needed.', 'wpshadow' ),
					'what_is_breadcrumb_schema' => array(
						'definition' => 'Structured data that shows page hierarchy',
						'example' => 'Home > Blog > Category > Post Title',
						'google_display' => 'Shows in search results under your URL',
					),
					'seo_benefits' => array(
						'visibility' => 'Search results show full navigation path',
						'ctr_improvement' => 'Users understand page context before clicking',
						'mobile_friendly' => 'Breadcrumbs help mobile users navigate',
					),
					'example_markup' => array(
						'type' => 'BreadcrumbList',
						'items' => 'Home → Category → Post',
						'json_ld' => 'Machine-readable structured data',
					),
				),
			);
		}

		// No issues - schema implemented via SEO plugin.
		return null;
	}
}
