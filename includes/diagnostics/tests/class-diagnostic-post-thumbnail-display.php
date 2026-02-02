<?php
/**
 * Post Thumbnail Display Diagnostic
 *
 * Checks if post thumbnails are properly configured and displayed.
 *
 * @since   1.26033.0901
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Post_Thumbnail_Display Class
 *
 * Validates post thumbnail configuration and availability.
 *
 * @since 1.26033.0901
 */
class Diagnostic_Post_Thumbnail_Display extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'post-thumbnail-display';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Post Thumbnail Display';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies post thumbnails are properly configured and displayed';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'posts';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.0901
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if featured image support is enabled
		$post_thumbnail_support = current_theme_supports( 'post-thumbnails' );

		if ( ! $post_thumbnail_support ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Featured image (post thumbnail) support is not enabled for your theme. This means posts cannot display featured images even if assigned.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/post-thumbnail-display',
			);
		}

		global $wpdb;

		// Count posts with missing thumbnails but published
		$posts_without_thumbnails = $wpdb->get_var(
			"SELECT COUNT(p.ID) FROM {$wpdb->posts} p
			LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_thumbnail_id'
			WHERE p.post_status = 'publish'
			AND p.post_type IN ('post', 'page')
			AND pm.meta_id IS NULL"
		);

		if ( intval( $posts_without_thumbnails ) > 50 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of posts without thumbnails */
					__( 'Found %d published posts without featured images. Consider adding thumbnails for better visual appeal and SEO.', 'wpshadow' ),
					intval( $posts_without_thumbnails )
				),
				'severity'     => 'low',
				'threat_level' => 10,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/post-thumbnail-display',
			);
		}

		return null; // Post thumbnail display is configured
	}
}
