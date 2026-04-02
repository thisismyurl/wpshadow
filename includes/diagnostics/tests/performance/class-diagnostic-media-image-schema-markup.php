<?php
/**
 * Media Image Schema Markup Diagnostic
 *
 * Tests whether structured data for images is configured
 * via SEO plugins or schema filters.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Tests
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Media_Image_Schema_Markup Class
 *
 * Detects schema markup integration for images.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Media_Image_Schema_Markup extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-image-schema-markup';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Image Schema Markup';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if structured data is added for images';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$schema_plugins = array(
			'wordpress-seo/wp-seo.php'      => 'Yoast SEO',
			'rank-math/rank-math.php'       => 'Rank Math',
			'autodescription/autodescription.php' => 'The SEO Framework',
			'all-in-one-seo-pack/all_in_one_seo_pack.php' => 'All in One SEO',
			'seopress/seopress.php'         => 'SEOPress',
		);

		$schema_active = false;
		foreach ( $schema_plugins as $plugin_path => $plugin_name ) {
			if ( is_plugin_active( $plugin_path ) ) {
				$schema_active = true;
				break;
			}
		}

		$schema_filters = array(
			'wpseo_schema_graph',
			'rank_math/json_ld',
			'wp_sitemap_posts_query_args',
		);

		$schema_filter_found = false;
		foreach ( $schema_filters as $filter ) {
			if ( has_filter( $filter ) ) {
				$schema_filter_found = true;
				break;
			}
		}

		if ( ! $schema_active && ! $schema_filter_found ) {
			$issues[] = __( 'No schema integration detected; consider enabling structured data for images', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/media-image-schema-markup',
			);
		}

		return null;
	}
}
