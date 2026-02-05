<?php
/**
 * Media Thumbnail Loading Speed Treatment
 *
 * Measures thumbnail retrieval performance and detects
 * lazy loading configuration issues.
 *
 * @package    WPShadow
 * @subpackage Treatments\Tests
 * @since      1.6033.1605
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Media_Thumbnail_Loading_Speed Class
 *
 * Checks thumbnail generation and loading performance for
 * recent image attachments and lazy loading configuration.
 *
 * @since 1.6033.1605
 */
class Treatment_Media_Thumbnail_Loading_Speed extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-thumbnail-loading-speed';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Thumbnail Loading Speed';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Measures thumbnail load time and lazy loading configuration';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.1605
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		$lazy_loading = wp_lazy_loading_enabled( 'img', 'wp_get_attachment_image' );
		if ( ! $lazy_loading ) {
			$issues[] = __( 'Lazy loading is disabled for images; thumbnails may slow down media library loading', 'wpshadow' );
		}

		$recent_attachments = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_mime_type' => 'image',
				'posts_per_page' => 5,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		if ( empty( $recent_attachments ) ) {
			return null;
		}

		$start_time = microtime( true );
		$missing_thumbnails = 0;
		foreach ( $recent_attachments as $attachment ) {
			$metadata = wp_get_attachment_metadata( $attachment->ID );
			if ( empty( $metadata['sizes'] ) ) {
				$missing_thumbnails++;
				continue;
			}

			$thumb = wp_get_attachment_image_src( $attachment->ID, 'thumbnail' );
			if ( empty( $thumb ) ) {
				$missing_thumbnails++;
			}
		}
		$elapsed = microtime( true ) - $start_time;

		if ( $elapsed > 1.5 ) {
			$issues[] = sprintf(
				/* translators: %s: duration in seconds */
				__( 'Thumbnail retrieval took %s seconds for recent uploads; consider optimizing image sizes or server resources', 'wpshadow' ),
				number_format( $elapsed, 2 )
			);
		}

		if ( $missing_thumbnails > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of images */
				_n(
					'%d recent image is missing a thumbnail; regenerate thumbnails to speed up loading',
					'%d recent images are missing thumbnails; regenerate thumbnails to speed up loading',
					$missing_thumbnails,
					'wpshadow'
				),
				$missing_thumbnails
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/media-thumbnail-loading-speed',
			);
		}

		return null;
	}
}
