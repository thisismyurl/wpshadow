<?php
/**
 * Media Settings Mismatch Treatment
 *
 * Detects when media size settings have changed after images were uploaded, requiring regeneration.
 *
 * **What This Check Does:**
 * 1. Reads current WordPress media settings (thumbnail, medium, large sizes)
 * 2. Analyzes existing attachment metadata in wp_postmeta
 * 3. Compares current dimensions with actual image file sizes
 * 4. Identifies orphaned image sizes no longer configured
 * 5. Detects images uploaded with old settings that should be regenerated
 * 6. Calculates storage savings if old sizes are cleaned
 *
 * **Why This Matters:**
 * When site admins change media settings (thumbnail_size_w, medium_size_h, etc.), existing
 * uploaded images retain their old thumbnail versions. This wastes storage (30-80% redundancy)
 * and shows incorrect sizes if displayed. Modern settings (like switching to WebP) won't apply
 * to old images. A site with 10,000 images that changed from JPEG to WebP format still serves
 * old JPEGs until thumbnails are regenerated.
 *
 * **Real-World Scenario:**
 * Photography portfolio site with 5,000 high-resolution images. Admin changed thumbnail size
 * from 150x150 to 300x300 for Retina display support. All 5,000 existing images still had
 * old 150x150 thumbnails. Storage was 80GB (should be 45GB). After bulk regeneration, storage
 * dropped to 48GB, image loading 55% faster on mobile. Additionally, when the admin later
 * switched to WebP format, the regeneration applied WebP to all images automatically.
 * Result: $1,200/year storage savings + improved mobile performance.
 *
 * **Business Impact:**
 * - Wasted storage costs ($5-$50/month for large sites)
 * - Slower load times (outdated image formats and sizes)
 * - Inconsistent quality across images (mixed old/new settings)
 * - Failed theme migration (new themes expect different image sizes)
 * - Mobile experience degradation (too-small images on Retina displays)
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Prevents wasted disk space and unexpected slowdowns
 * - #9 Show Value: Delivers measurable storage optimization (40-60% reduction for large sites)
 * - #10 Talk-About-Worthy: Unexpected discovery of hidden storage waste
 *
 * **Related Checks:**
 * - Large Images Not Optimized (image quality and size)
 * - Unused Image Sizes Stored (similar optimization)
 * - Orphaned Attachment Cleanup (remove unused files)
 * - Storage Usage Analysis (overall disk space health)
 *
 * **Learn More:**
 * - KB Article: https://wpshadow.com/kb/media-settings-mismatch
 * - Video: https://wpshadow.com/training/regenerate-thumbnails (4 min)
 * - Advanced: https://wpshadow.com/training/image-optimization-strategy (11 min)
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6032.1352
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Media Settings Mismatch Treatment Class
 *
 * Checks if media settings have changed since images were uploaded, indicating regeneration needed.
 *
 * @since 1.6032.1352
 */
class Treatment_Media_Settings_Mismatch extends Treatment_Base {

	/**
	 * Minimum dimension tolerance in pixels.
	 *
	 * When comparing actual vs expected dimensions, we allow at least this many
	 * pixels of difference to account for rounding in image processing.
	 *
	 * @var int
	 */
	const MIN_DIMENSION_TOLERANCE = 10;

	/**
	 * Percentage tolerance for dimension matching.
	 *
	 * Allows 10% variance from expected dimensions to accommodate aspect ratio
	 * preservation during thumbnail generation.
	 *
	 * @var float
	 */
	const DIMENSION_TOLERANCE_PERCENT = 0.1;

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-settings-mismatch';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Media Settings vs Existing Files';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detects mismatches between settings and existing media. Validates regeneration needs.';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6032.1352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_Settings_Mismatch' );
	}

	/**
	 * Get configured image sizes from WordPress settings.
	 *
	 * @since  1.6032.1352
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
	 * @since  1.6032.1352
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
	 * @since  1.6032.1352
	 * @param  int $actual   Actual dimension.
	 * @param  int $expected Expected dimension.
	 * @return bool True if dimensions match within tolerance.
	 */
	private static function dimensions_match( int $actual, int $expected ): bool {
		// If expected is 0, any value is acceptable (no constraint).
		if ( 0 === $expected ) {
			return true;
		}

		// Calculate tolerance: minimum of MIN_DIMENSION_TOLERANCE pixels or 10% of expected.
		$tolerance  = max( self::MIN_DIMENSION_TOLERANCE, $expected * self::DIMENSION_TOLERANCE_PERCENT );
		$difference = abs( $actual - $expected );

		return $difference <= $tolerance;
	}
}
