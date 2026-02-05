<?php
/**
 * Thumbnail Loading Speed Treatment
 *
 * Measures thumbnail load performance and detects missing or slow thumbnails.
 *
 * @package    WPShadow
 * @subpackage Treatments\Media
 * @since      1.6030.2148
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Thumbnail_Loading_Speed Class
 *
 * Validates thumbnail availability and performance. Missing thumbnails cause
 * on-demand generation, which slows down the media library and front-end.
 *
 * @since 1.6030.2148
 */
class Treatment_Thumbnail_Loading_Speed extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'thumbnail-loading-speed';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'Thumbnail Loading Speed';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Measures thumbnail load performance and detects missing thumbnails';

	/**
	 * The family this treatment belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * Validates:
	 * - Missing thumbnail metadata
	 * - Missing files on disk
	 * - Time to resolve thumbnails
	 * - Thumbnail regeneration needs
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues        = array();
		$missing_files = 0;
		$missing_meta  = 0;

		global $wpdb;

		// Sample recent image attachments.
		$images = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT p.ID, pm.meta_value as file_path
				FROM {$wpdb->posts} p
				INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_wp_attached_file'
				WHERE p.post_type = %s
				AND p.post_mime_type LIKE %s
				ORDER BY p.post_date DESC
				LIMIT 25",
				'attachment',
				'image/%'
			)
		);

		if ( empty( $images ) ) {
			return null;
		}

		$upload_dir = wp_upload_dir();
		$start_time = microtime( true );
		
		foreach ( $images as $image ) {
			$metadata = wp_get_attachment_metadata( $image->ID );
			
			if ( empty( $metadata ) || empty( $metadata['sizes'] ) ) {
				$missing_meta++;
				continue;
			}

			// Verify that common thumbnails exist on disk.
			$sizes_to_check = array( 'thumbnail', 'medium', 'medium_large' );
			foreach ( $sizes_to_check as $size ) {
				if ( empty( $metadata['sizes'][ $size ]['file'] ) ) {
					continue;
				}

				$file_path = $upload_dir['basedir'] . '/' . dirname( $image->file_path ) . '/' . $metadata['sizes'][ $size ]['file'];
				if ( ! file_exists( $file_path ) ) {
					$missing_files++;
					break;
				}
			}
		}

		$thumb_time = microtime( true ) - $start_time;

		// Resolving thumbnails should be quick.
		if ( 0.4 < $thumb_time ) {
			$issues[] = sprintf(
				/* translators: %s: time in milliseconds */
				__( 'Thumbnail resolution took %sms for 25 images - slow file/metadata access', 'wpshadow' ),
				number_format_i18n( $thumb_time * 1000, 2 )
			);
		}

		if ( 0 < $missing_meta ) {
			$issues[] = sprintf(
				/* translators: %d: number of images */
				_n(
					'%d image is missing thumbnail metadata',
					'%d images are missing thumbnail metadata',
					$missing_meta,
					'wpshadow'
				),
				$missing_meta
			);
		}

		if ( 0 < $missing_files ) {
			$issues[] = sprintf(
				/* translators: %d: number of images */
				_n(
					'%d image has missing thumbnail files on disk',
					'%d images have missing thumbnail files on disk',
					$missing_files,
					'wpshadow'
				),
				$missing_files
			);
		}

		// Check for image_downsize filter (affects thumbnail retrieval).
		if ( has_filter( 'image_downsize' ) ) {
			$issues[] = __( 'image_downsize filter is active - may affect thumbnail generation performance', 'wpshadow' );
		}

		// Check for disabled image editor support.
		if ( ! wp_image_editor_supports( array( 'mime_type' => 'image/jpeg' ) ) ) {
			$issues[] = __( 'Image editor does not support JPEG - thumbnails may fail to generate', 'wpshadow' );
		}

		// Check for large thumbnail sizes.
		$thumbnail_size = get_option( 'thumbnail_size_w', 150 ) * get_option( 'thumbnail_size_h', 150 );
		if ( ( 400 * 400 ) < $thumbnail_size ) {
			$issues[] = __( 'Thumbnail size is very large - increases load time and disk usage', 'wpshadow' );
		}

		// Check for missing intermediate sizes option.
		$intermediate_sizes = get_intermediate_image_sizes();
		if ( empty( $intermediate_sizes ) ) {
			$issues[] = __( 'No intermediate image sizes registered - thumbnails may not be generated', 'wpshadow' );
		}

		// Check for object caching.
		if ( ! wp_using_ext_object_cache() ) {
			$issues[] = __( 'No persistent object cache detected - thumbnail metadata lookups may be slower', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues */
					_n(
						'%d issue detected with thumbnail loading',
						'%d issues detected with thumbnail loading',
						count( $issues ),
						'wpshadow'
					),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/thumbnail-loading-speed',
				'details'      => array(
					'issues'        => $issues,
					'missing_meta'  => $missing_meta,
					'missing_files' => $missing_files,
					'time_ms'       => round( $thumb_time * 1000, 2 ),
				),
			);
		}

		return null;
	}
}
