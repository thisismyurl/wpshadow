<?php
/**
 * Schema Basics Reviewed Diagnostic (Stub)
 *
 * TODO stub mapped to the seo gauge.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Schema_Basics_Reviewed Class
 *
 * TODO: Implement full test logic and remediation guidance.
 */
class Diagnostic_Schema_Basics extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'schema-basics';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Schema Basics';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether a schema markup plugin is active to output structured data that helps search engines understand and display the site\'s content in rich results.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * TODO Test Plan:
	 * - Detect schema output from theme or SEO plugins for organization, website, and article types.
	 *
	 * TODO Fix Plan:
	 * - Configure baseline structured data appropriate to the site model.
	 * - Use WordPress hooks, filters, settings, DB fixes, PHP config, or accessible server settings.
	 * - Do not modify WordPress core files.
	 * - Ensure performance/security/success impact and align with WPShadow commandments.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		$active_plugins = (array) get_option( 'active_plugins', array() );

		// Plugins that output schema markup automatically.
		$schema_plugins = array(
			'wordpress-seo/wp-seo.php'                              => 'Yoast SEO',
			'wordpress-seo-premium/wp-seo-premium.php'              => 'Yoast SEO Premium',
			'seo-by-rank-math/rank-math.php'                        => 'Rank Math',
			'seo-by-rank-math-pro/rank-math-pro.php'                => 'Rank Math Pro',
			'schema-and-structured-data-for-wp/index.php'           => 'Schema & Structured Data for WP',
			'wp-schema-pro/wp-schema-pro.php'                       => 'WP Schema Pro',
			'schema-pro/schema-pro.php'                             => 'Schema Pro',
			'the-seo-framework/the-seo-framework.php'               => 'The SEO Framework',
			'all-in-one-seo-pack/all_in_one_seo_pack.php'           => 'All in One SEO',
			'all-in-one-seo-pack-pro/all_in_one_seo_pack.php'       => 'All in One SEO Pro',
		);

		foreach ( $schema_plugins as $plugin_file => $plugin_name ) {
			if ( in_array( $plugin_file, $active_plugins, true ) ) {
				return null;
			}
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No plugin providing structured data (schema markup) is active. Schema markup helps search engines understand your content and enables rich results such as star ratings, breadcrumbs, and FAQ panels. Install an SEO plugin such as Yoast SEO or Rank Math, or a dedicated schema plugin such as Schema & Structured Data for WP.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 35,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/schema-basics',
			'details'      => array(
				'schema_plugin_detected' => false,
			),
		);
	}
}
