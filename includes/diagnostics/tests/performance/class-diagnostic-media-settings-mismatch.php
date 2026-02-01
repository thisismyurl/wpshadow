<?php
/**
 * Media Settings Mismatch Diagnostic
 *
 * Detects mismatches between configured media settings and existing media files.
 * Validates if media files need regeneration due to changed settings.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26032.1352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Media Settings Mismatch Diagnostic Class
 *
 * Checks if media settings have changed since images were uploaded,
 * indicating a need for thumbnail regeneration.
 *
 * @since 1.26032.1352
 */
class Diagnostic_Media_Settings_Mismatch extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-settings-mismatch';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Media Settings vs Existing Files';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects mismatches between settings and existing media. Validates regeneration needs.';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26032.1352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check transient cache first.
		$cached = get_transient( 'wpshadow_media_settings_check' );
		if ( false !== $cached ) {
			return $cached;
		}

		// Get current configured image sizes.
		$configured_sizes = self::get_configured_sizes();

		// Sample recent attachments to check for mismatches.
		$sample_size      = 50;
		$mismatched_count = 0;
		$total_checked    = 0;
		$missing_sizes    = array();

		$attachments = self::get_recent_image_attachments( $sample_size );

		if ( empty( $attachments ) ) {
			// No images to check, return null.
			$result = null;
			set_transient( 'wpshadow_media_settings_check', $result, HOUR_IN_SECONDS );
			return $result;
		}

		foreach ( $attachments as $attachment ) {
			++$total_checked;
			$metadata = wp_get_attachment_metadata( $attachment->ID );

			if ( ! $metadata || ! isset( $metadata['sizes'] ) ) {
				// No metadata or sizes, could indicate an issue.
				++$mismatched_count;
				continue;
			}

			// Check if configured sizes exist in metadata.
			foreach ( $configured_sizes as $size_name => $size_data ) {
				if ( ! isset( $metadata['sizes'][ $size_name ] ) ) {
					// This size is missing.
					if ( ! isset( $missing_sizes[ $size_name ] ) ) {
						$missing_sizes[ $size_name ] = 0;
					}
					++$missing_sizes[ $size_name ];
					++$mismatched_count;
				} else {
					// Size exists, check if dimensions match configuration.
					$actual_width  = (int) $metadata['sizes'][ $size_name ]['width'];
					$actual_height = (int) $metadata['sizes'][ $size_name ]['height'];
					$expected_w    = (int) $size_data['width'];
					$expected_h    = (int) $size_data['height'];

					// Allow some tolerance for aspect ratio preservation.
					$width_matches  = self::dimensions_match( $actual_width, $expected_w );
					$height_matches = self::dimensions_match( $actual_height, $expected_h );

					if ( ! $width_matches && ! $height_matches ) {
						// Dimensions don't match expected size.
						if ( ! isset( $missing_sizes[ $size_name ] ) ) {
							$missing_sizes[ $size_name ] = 0;
						}
						++$missing_sizes[ $size_name ];
						++$mismatched_count;
					}
				}
			}
		}

		// Calculate mismatch percentage.
		$expected_checks     = $total_checked * count( $configured_sizes );
		$mismatch_percentage = $expected_checks > 0 ? ( $mismatched_count / $expected_checks ) * 100 : 0;

		// Only flag if significant mismatch (> 25% of checks failed).
		if ( $mismatch_percentage > 25 ) {
			$result = array(
				'id'              => self::$slug,
				'title'           => self::$title,
				'description'     => sprintf(
					/* translators: 1: percentage of mismatches, 2: number of images checked */
					__( 'Media settings mismatch detected: %1$.1f%% of image size checks failed across %2$d recent images. Configured image sizes don\'t match the actual generated thumbnails, indicating images may need regeneration.', 'wpshadow' ),
					$mismatch_percentage,
					$total_checked
				),
				'severity'        => $mismatch_percentage > 50 ? 'medium' : 'low',
				'threat_level'    => 55,
				'auto_fixable'    => false,
				'kb_link'         => 'https://wpshadow.com/kb/media-settings-mismatch',
				'meta'            => array(
					'mismatch_percentage' => round( $mismatch_percentage, 1 ),
					'images_checked'      => $total_checked,
					'mismatched_count'    => $mismatched_count,
					'missing_sizes'       => $missing_sizes,
					'configured_sizes'    => array_keys( $configured_sizes ),
				),
				'details'         => array(
					__( 'Current configured image sizes:', 'wpshadow' ) => array_keys( $configured_sizes ),
					__( 'Images sampled:', 'wpshadow' ) => $total_checked,
					__( 'Mismatch rate:', 'wpshadow' )  => round( $mismatch_percentage, 1 ) . '%',
				),
				'recommendations' => array(
					__( 'Regenerate thumbnails using a plugin like Regenerate Thumbnails', 'wpshadow' ),
					__( 'Review your media settings (Settings > Media) to ensure they match your theme requirements', 'wpshadow' ),
					__( 'Consider using a plugin to manage custom image sizes', 'wpshadow' ),
				),
			);

			// No significant issues found.
			set_transient( 'wpshadow_media_settings_check', $result, HOUR_IN_SECONDS );
			return $result;
		}

		// No significant issues found.
		$result = null;
		set_transient( 'wpshadow_media_settings_check', $result, HOUR_IN_SECONDS );
		return $result;
	}

	/**
	 * Get configured image sizes from WordPress settings.
	 *
	 * @since  1.26032.1352
	 * @return array Array of configured sizes with width and height.
	 */
	private static function get_configured_sizes(): array {
		$sizes = array(
			'thumbnail' => array(
				'width'  => (int) get_option( 'thumbnail_size_w', 150 ),
				'height' => (int) get_option( 'thumbnail_size_h', 150 ),
				'crop'   => (bool) get_option( 'thumbnail_crop', 1 ),
			),
			'medium'    => array(
				'width'  => (int) get_option( 'medium_size_w', 300 ),
				'height' => (int) get_option( 'medium_size_h', 300 ),
				'crop'   => false,
			),
			'large'     => array(
				'width'  => (int) get_option( 'large_size_w', 1024 ),
				'height' => (int) get_option( 'large_size_h', 1024 ),
				'crop'   => false,
			),
		);

		// Filter out sizes with zero dimensions.
		$sizes = array_filter(
			$sizes,
			function ( $size ) {
				return $size['width'] > 0 || $size['height'] > 0;
			}
		);

		return $sizes;
	}

	/**
	 * Get recent image attachments for sampling.
	 *
	 * @since  1.26032.1352
	 * @param  int $limit Number of attachments to retrieve.
	 * @return array Array of attachment objects.
	 */
	private static function get_recent_image_attachments( int $limit = 50 ): array {
		global $wpdb;

		// Query is already prepared with placeholders.
		$query = $wpdb->prepare(
			"SELECT ID FROM {$wpdb->posts}
			WHERE post_type = 'attachment'
			AND post_mime_type LIKE %s
			ORDER BY post_date DESC
			LIMIT %d",
			'image/%',
			$limit
		);

		$results = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		return is_array( $results ) ? $results : array();
	}

	/**
	 * Check if two dimensions match within acceptable tolerance.
	 *
	 * Allows for slight variations due to aspect ratio preservation.
	 *
	 * @since  1.26032.1352
	 * @param  int $actual   Actual dimension.
	 * @param  int $expected Expected dimension.
	 * @return bool True if dimensions match within tolerance.
	 */
	private static function dimensions_match( int $actual, int $expected ): bool {
		// If expected is 0, any value is acceptable (no constraint).
		if ( 0 === $expected ) {
			return true;
		}

		// Allow 10% tolerance for aspect ratio preservation.
		$tolerance  = max( 10, $expected * 0.1 );
		$difference = abs( $actual - $expected );

		return $difference <= $tolerance;
	}
}
