<?php
/**
 * Media Image Schema Markup Treatment
 *
 * Tests whether structured data for images is configured
 * via SEO plugins or schema filters.
 *
 * @package    WPShadow
 * @subpackage Treatments\Tests
 * @since      1.6033.1625
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Media_Image_Schema_Markup Class
 *
 * Detects schema markup integration for images.
 *
 * @since 1.6033.1625
 */
class Treatment_Media_Image_Schema_Markup extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-image-schema-markup';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Image Schema Markup';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if structured data is added for images';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.1625
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
