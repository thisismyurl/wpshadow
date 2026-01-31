<?php
/**
 * Diagnostic: Structured Data (Schema.org) Validation
 *
 * Detects if site includes proper Schema.org structured data.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Structured_Data_Schema
 *
 * Checks for Schema.org structured data markup (JSON-LD) which improves
 * search engine understanding and enables rich search results.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Structured_Data_Schema extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'structured-data-schema';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Structured Data (Schema.org) Validation';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Detect if site includes proper Schema.org structured data';

	/**
	 * Run the diagnostic check.
	 *
	 * Looks for JSON-LD structured data in page output.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if no schema found, null if present.
	 */
	public static function check() {
		// Check for schema plugins
		$schema_plugins = array(
			'wordpress-seo/wp-seo.php' => 'Yoast SEO',
			'all-in-one-seo-pack/all_in_one_seo_pack.php' => 'All in One SEO',
			'schema-and-structured-data-for-wp/structured-data-for-wp.php' => 'Schema & Structured Data',
			'wp-seopress/seopress.php' => 'SEOPress',
			'rank-math/rank-math.php' => 'Rank Math',
		);

		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$has_schema_plugin = false;
		$schema_plugin_name = '';

		foreach ( $schema_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$has_schema_plugin = true;
				$schema_plugin_name = $plugin_name;
				break;
			}
		}

		// Check homepage for JSON-LD structured data
		ob_start();
		wp_head();
		$head_content = ob_get_clean();

		// Look for JSON-LD script tags
		$has_json_ld = false !== strpos( $head_content, 'application/ld+json' );

		// Look for schema.org references
		$has_schema_org = false !== strpos( $head_content, 'schema.org' );

		$has_structured_data = $has_json_ld || $has_schema_org;

		if ( $has_structured_data || $has_schema_plugin ) {
			// Structured data is present
			return null;
		}

		// No structured data detected
		$description = __( 'No Schema.org structured data detected. Structured data (markup using JSON-LD format) helps search engines understand your content and enables rich search results like star ratings, breadcrumbs, FAQs, and more. This improves click-through rates and is increasingly important for SEO. Popular plugins like Yoast SEO, Rank Math, or Schema & Structured Data can add proper markup automatically.', 'wpshadow' );

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => $description,
			'severity'    => 'low',
			'threat_level' => 25,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/seo-structured-data-schema',
			'meta'        => array(
				'has_json_ld' => $has_json_ld,
				'has_schema_org' => $has_schema_org,
				'has_schema_plugin' => $has_schema_plugin,
				'schema_plugin' => $schema_plugin_name,
			),
		);
	}
}
