<?php
/**
 * AJAX: Regenerate Thumbnails
 *
 * @since 1.6093.1200
 * @package WPShadow\Admin
 */

declare(strict_types=1);

namespace WPShadow\Admin;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Core\Activity_Logger;
use WPShadow\Core\Error_Handler;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Regenerate Thumbnails Handler
 */
class AJAX_Regenerate_Thumbnails extends AJAX_Handler_Base {
	/**
	 * Handle the AJAX request.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function handle() {
		self::verify_request( 'wpshadow_regenerate_thumbnails', 'manage_options' );

		$method       = self::get_post_param( 'regenerate_method', 'text', 'all', true );
		$image_sizes  = self::get_post_param( 'image_sizes', 'array', array() );
		$delete_old   = rest_sanitize_boolean( self::get_post_param( 'delete_old', 'bool', false ) );
		$only_featured = rest_sanitize_boolean( self::get_post_param( 'only_featured', 'bool', false ) );
		$start_id     = self::get_post_param( 'start_id', 'int', 0 );
		$end_id       = self::get_post_param( 'end_id', 'int', 0 );
		$batch_offset = self::get_post_param( 'batch_offset', 'int', 0 );

		// Validate method
		if ( ! in_array( $method, array( 'all', 'missing', 'range' ), true ) ) {
			self::send_error( __( 'Invalid regeneration method', 'wpshadow' ) );
			return;
		}

		if ( empty( $image_sizes ) ) {
			self::send_error( __( 'Please select at least one image size', 'wpshadow' ) );
			return;
		}

		try {
			// Get attachments to process
			$attachments = self::get_attachments( $method, $start_id, $end_id, $only_featured, $batch_offset );

			if ( empty( $attachments ) ) {
				self::send_success(
					array(
						'message'   => __( 'All thumbnails have been regenerated', 'wpshadow' ),
						'completed' => true,
						'processed' => $batch_offset,
					)
				);
				return;
			}

			// Process batch (limit to 10 images per request)
			$batch_size = 10;
			$batch      = array_slice( $attachments, 0, $batch_size );
			$results    = array(
				'processed'    => 0,
				'errors'       => 0,
				'error_images' => array(),
			);

			foreach ( $batch as $attachment_id ) {
				$result = self::regenerate_attachment_thumbnails(
					$attachment_id,
					$image_sizes,
					$delete_old
				);

				if ( $result['success'] ) {
					$results['processed']++;
				} else {
					$results['errors']++;
					$results['error_images'][] = array(
						'id'    => $attachment_id,
						'title' => get_the_title( $attachment_id ),
						'error' => $result['error'],
					);
				}
			}

			// Calculate progress
			$new_offset     = $batch_offset + $results['processed'];
			$total_images   = self::get_total_images_count( $method, $start_id, $end_id, $only_featured );
			$is_complete    = $new_offset >= $total_images;
			$percent_complete = $total_images > 0 ? ( $new_offset / $total_images ) * 100 : 100;

			// Log activity if complete
			if ( $is_complete ) {
				Activity_Logger::log(
					'thumbnails_regenerated',
					array(
						'method'        => $method,
						'total_images'  => $new_offset,
						'image_sizes'   => count( $image_sizes ),
						'errors'        => $results['errors'],
					)
				);
			}

			self::send_success(
				array(
					'message'          => sprintf(
						/* translators: %d: number of images processed */
						__( 'Processed %d images', 'wpshadow' ),
						$results['processed']
					),
					'completed'        => $is_complete,
					'processed'        => $new_offset,
					'total'            => $total_images,
					'percent'          => round( $percent_complete, 1 ),
					'errors'           => $results['errors'],
					'error_images'     => $results['error_images'],
					'batch_offset'     => $new_offset,
				)
			);

		} catch ( \Exception $e ) {
			Error_Handler::log_error( $e->getMessage(), $e );
			self::send_error( $e->getMessage() );
		}
	}

	/**
	 * Get attachments to process.
	 *
	 * @since 1.6093.1200
	 * @param  string $method        Method (all/missing/range).
	 * @param  int    $start_id      Start ID (for range).
	 * @param  int    $end_id        End ID (for range).
	 * @param  bool   $only_featured Featured images only.
	 * @param  int    $offset        Batch offset.
	 * @return array Attachment IDs.
	 */
	private static function get_attachments( $method, $start_id, $end_id, $only_featured, $offset ) {
		$args = array(
			'post_type'      => 'attachment',
			'post_mime_type' => 'image',
			'post_status'    => 'inherit',
			'posts_per_page' => 100, // Get next batch
			'offset'         => $offset,
			'fields'         => 'ids',
			'orderby'        => 'ID',
			'order'          => 'ASC',
		);

		// Range method
		if ( 'range' === $method && $start_id > 0 && $end_id > 0 ) {
			$args['post__in'] = range( $start_id, $end_id );
			unset( $args['offset'] );
		}

		// Only featured images
		if ( $only_featured ) {
			$args['meta_key']   = '_thumbnail_id';
			$args['meta_query'] = array(
				array(
					'key'     => '_thumbnail_id',
					'compare' => 'EXISTS',
				),
			);
		}

		$query = new \WP_Query( $args );
		return $query->posts;
	}

	/**
	 * Get total images count.
	 *
	 * @since 1.6093.1200
	 * @param  string $method        Method (all/missing/range).
	 * @param  int    $start_id      Start ID (for range).
	 * @param  int    $end_id        End ID (for range).
	 * @param  bool   $only_featured Featured images only.
	 * @return int Total count.
	 */
	private static function get_total_images_count( $method, $start_id, $end_id, $only_featured ) {
		$args = array(
			'post_type'      => 'attachment',
			'post_mime_type' => 'image',
			'post_status'    => 'inherit',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		);

		if ( 'range' === $method && $start_id > 0 && $end_id > 0 ) {
			return $end_id - $start_id + 1;
		}

		if ( $only_featured ) {
			$args['meta_key'] = '_thumbnail_id';
			$args['meta_query'] = array(
				array(
					'key'     => '_thumbnail_id',
					'compare' => 'EXISTS',
				),
			);
		}

		$query = new \WP_Query( $args );
		return $query->found_posts;
	}

	/**
	 * Regenerate thumbnails for attachment.
	 *
	 * @since 1.6093.1200
	 * @param  int   $attachment_id Attachment ID.
	 * @param  array $image_sizes   Image sizes to regenerate.
	 * @param  bool  $delete_old    Delete old thumbnails.
	 * @return array Result.
	 */
	private static function regenerate_attachment_thumbnails( $attachment_id, $image_sizes, $delete_old ) {
		$file_path = get_attached_file( $attachment_id );

		if ( ! file_exists( $file_path ) ) {
			return array(
				'success' => false,
				'error'   => __( 'File not found', 'wpshadow' ),
			);
		}

		// Get current metadata
		$metadata = wp_get_attachment_metadata( $attachment_id );

		// Delete old thumbnails if requested
		if ( $delete_old && ! empty( $metadata['sizes'] ) ) {
			foreach ( $metadata['sizes'] as $size => $size_data ) {
				$thumb_file = path_join( dirname( $file_path ), $size_data['file'] );
				if ( file_exists( $thumb_file ) ) {
					// phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink
					@unlink( $thumb_file );
				}
			}
			$metadata['sizes'] = array();
		}

		// Load image editor
		require_once ABSPATH . 'wp-admin/includes/image.php';

		// Regenerate thumbnails for specified sizes
		foreach ( $image_sizes as $size_name ) {
			$size_data = wp_get_registered_image_subsizes()[ $size_name ] ?? null;
			
			if ( ! $size_data ) {
				continue;
			}

			$editor = wp_get_image_editor( $file_path );
			if ( is_wp_error( $editor ) ) {
				continue;
			}

			// Resize image
			$editor->resize( $size_data['width'], $size_data['height'], $size_data['crop'] );

			// Save resized image
			$resized = $editor->save(
				wp_get_image_editor()->generate_filename( $size_name, dirname( $file_path ) . '/' )
			);

			if ( ! is_wp_error( $resized ) && ! empty( $resized['file'] ) ) {
				$metadata['sizes'][ $size_name ] = array(
					'file'      => basename( $resized['file'] ),
					'width'     => $resized['width'],
					'height'    => $resized['height'],
					'mime-type' => $resized['mime-type'],
				);
			}
		}

		// Update metadata
		wp_update_attachment_metadata( $attachment_id, $metadata );

		return array( 'success' => true );
	}
}

// Register AJAX action
\add_action( 'wp_ajax_wpshadow_regenerate_thumbnails', array( '\WPShadow\\Admin\\AJAX_Regenerate_Thumbnails', 'handle' ) );
