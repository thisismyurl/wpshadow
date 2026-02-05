<?php
/**
 * Image Schema Markup Treatment
 *
 * Tests if proper schema markup is added to images.
 * Validates structured data for SEO purposes.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.7029.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Image Schema Markup Treatment Class
 *
 * Checks if images have proper schema.org structured data markup
 * for improved SEO and rich search results.
 *
 * @since 1.7029.1200
 */
class Treatment_Image_Schema_Markup extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'image-schema-markup';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Image Schema Markup';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if proper schema markup is added to images';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * Tests if image schema markup (schema.org/ImageObject) is being
	 * added to images on the frontend.
	 *
	 * @since  1.7029.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		// Check if a recent post with an image exists.
		$args = array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => 5,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'meta_query'     => array(
				array(
					'key'     => '_thumbnail_id',
					'compare' => 'EXISTS',
				),
			),
		);

		$posts = get_posts( $args );

		if ( empty( $posts ) ) {
			return null; // No posts with images to test.
		}

		// Test a sample post.
		$test_post  = $posts[0];
		$post_url   = get_permalink( $test_post->ID );
		$post_title = get_the_title( $test_post->ID );

		// Make HTTP request to check for schema markup.
		$response = wp_remote_get(
			$post_url,
			array(
				'timeout'    => 10,
				'user-agent' => 'WPShadow/1.0 (Schema Markup Treatment)',
			)
		);

		if ( is_wp_error( $response ) ) {
			return null; // Cannot test due to connection error.
		}

		$html = wp_remote_retrieve_body( $response );

		if ( empty( $html ) ) {
			return null;
		}

		// Check for schema.org ImageObject markup.
		$has_image_schema = false;
		$schema_type      = '';

		// Check for JSON-LD schema.
		if ( preg_match( '/<script[^>]*type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/is', $html, $matches ) ) {
			foreach ( $matches as $match ) {
				if ( false !== strpos( $match, 'ImageObject' ) ) {
					$has_image_schema = true;
					$schema_type      = 'JSON-LD';
					break;
				}
			}
		}

		// Check for microdata schema.
		if ( ! $has_image_schema && false !== strpos( $html, 'itemtype="http://schema.org/ImageObject"' ) ) {
			$has_image_schema = true;
			$schema_type      = 'Microdata';
		}

		// Check for RDFa schema.
		if ( ! $has_image_schema && false !== strpos( $html, 'typeof="ImageObject"' ) ) {
			$has_image_schema = true;
			$schema_type      = 'RDFa';
		}

		// Check for common SEO plugins that handle schema.
		$seo_plugins = array(
			'wordpress-seo/wp-seo.php'                => 'Yoast SEO',
			'all-in-one-seo-pack/all_in_one_seo_pack.php' => 'All in One SEO',
			'seo-by-rank-math/rank-math.php'          => 'Rank Math',
			'autodescription/autodescription.php'     => 'The SEO Framework',
		);

		$active_seo_plugin = '';
		foreach ( $seo_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_seo_plugin = $name;
				break;
			}
		}

		if ( ! $has_image_schema ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Images are missing schema.org markup, which helps search engines understand image content', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/image-schema-markup',
				'details'      => array(
					'tested_post'       => array(
						'id'    => $test_post->ID,
						'title' => $post_title,
						'url'   => $post_url,
					),
					'schema_found'      => false,
					'active_seo_plugin' => $active_seo_plugin ? $active_seo_plugin : 'None detected',
					'seo_impact'        => __( 'Schema markup helps search engines display rich results with image thumbnails in search', 'wpshadow' ),
					'recommendation'    => empty( $active_seo_plugin )
						? __( 'Install an SEO plugin like Yoast SEO, Rank Math, or The SEO Framework to add schema markup automatically', 'wpshadow' )
						: __( 'Configure your SEO plugin to enable schema markup for images', 'wpshadow' ),
					'schema_types'      => array(
						'JSON-LD'   => __( 'Recommended: Most modern and supported by Google', 'wpshadow' ),
						'Microdata' => __( 'Inline HTML attributes', 'wpshadow' ),
						'RDFa'      => __( 'Attribute-based markup', 'wpshadow' ),
					),
				),
			);
		}

		return null;
	}
}
