<?php
/**
 * Featured Images Assignment Failures Diagnostic
 *
 * Tests whether featured images import and assign correctly.
 *
 * @package    WPShadow
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
 * Featured Images Assignment Failures Diagnostic Class
 *
 * Tests whether featured images are correctly assigned to posts after import.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Featured_Images_Assignment_Failures extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'featured-images-assignment-failures';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Featured Images Assignment Failures';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether featured images import and assign correctly';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check posts without featured images.
		$posts_without_featured = get_posts( array(
			'post_type'      => array( 'post', 'page' ),
			'posts_per_page' => -1,
			'orderby'        => 'modified',
		) );

		if ( ! empty( $posts_without_featured ) ) {
			$missing_featured = 0;

			foreach ( $posts_without_featured as $post ) {
				if ( ! has_post_thumbnail( $post->ID ) ) {
					$missing_featured++;
				}
			}

			if ( $missing_featured > count( $posts_without_featured ) * 0.5 ) {
				$percentage = ( $missing_featured / count( $posts_without_featured ) ) * 100;
				$issues[] = sprintf(
					/* translators: %d: percentage of posts without featured images */
					__( '%d%% of posts are missing featured images', 'wpshadow' ),
					round( $percentage )
				);
			}
		}

		// Check for featured image metadata errors.
		$posts_with_featured = get_posts( array(
			'post_type'      => 'any',
			'posts_per_page' => 20,
			'orderby'        => 'modified',
			'order'          => 'DESC',
		) );

		if ( ! empty( $posts_with_featured ) ) {
			$bad_featured = 0;

			foreach ( $posts_with_featured as $post ) {
				$featured_id = get_post_thumbnail_id( $post->ID );

				if ( ! empty( $featured_id ) ) {
					// Check if attachment exists.
					$attachment = get_post( $featured_id );
					if ( empty( $attachment ) || $attachment->post_type !== 'attachment' ) {
						$bad_featured++;
					}
				}
			}

			if ( $bad_featured > 0 ) {
				$issues[] = sprintf(
					/* translators: %d: number of posts with broken featured image references */
					__( '%d posts have broken featured image references', 'wpshadow' ),
					$bad_featured
				);
			}
		}

		// Check for missing thumbnail support in theme.
		if ( ! current_theme_supports( 'post-thumbnails' ) ) {
			$issues[] = __( 'Current theme does not support featured images (post-thumbnails)', 'wpshadow' );
		}

		// Check if WP has default featured image.
		$default_featured = get_option( '_thumbnail_id' );
		if ( empty( $default_featured ) ) {
			$issues[] = __( 'No default featured image configured', 'wpshadow' );
		}

		// Check for featured image display in templates.
		if ( ! function_exists( 'get_the_post_thumbnail' ) ) {
			$issues[] = __( 'Featured image template functions not available', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/featured-images-assignment-failures?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
