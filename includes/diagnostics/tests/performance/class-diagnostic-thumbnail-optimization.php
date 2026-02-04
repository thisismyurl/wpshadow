<?php
/**
 * Thumbnail Optimization Diagnostic
 *
 * Analyzes WordPress thumbnail sizes and optimization.
 *
 * @since   1.6033.2125
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Thumbnail Optimization Diagnostic
 *
 * Evaluates thumbnail generation and identifies unnecessary sizes.
 *
 * @since 1.6033.2125
 */
class Diagnostic_Thumbnail_Optimization extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'thumbnail-optimization';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Thumbnail Optimization';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes WordPress thumbnail sizes and optimization';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.2125
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Get all registered image sizes
		$image_sizes = wp_get_registered_image_subsizes();
		$size_count  = count( $image_sizes );

		// Get intermediate image sizes
		$intermediate_sizes = get_intermediate_image_sizes();

		// Count total attachments
		global $wpdb;
		$total_images = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} 
			WHERE post_type = 'attachment' 
			AND post_mime_type LIKE 'image/%'"
		);

		// Calculate storage estimate
		$avg_thumbnails_per_image = $size_count;
		$avg_thumbnail_size       = 30; // KB estimate
		$estimated_storage_mb     = absint( $total_images ) * $avg_thumbnails_per_image * $avg_thumbnail_size / 1024;

		// Check for excessive thumbnail sizes
		if ( $size_count > 10 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of thumbnail sizes */
					__( '%d thumbnail sizes registered. Each uploaded image generates all sizes, consuming storage and processing time.', 'wpshadow' ),
					$size_count
				),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/thumbnail-optimization',
				'meta'         => array(
					'thumbnail_sizes'      => $size_count,
					'total_images'         => absint( $total_images ),
					'estimated_storage_mb' => round( $estimated_storage_mb, 2 ),
					'registered_sizes'     => array_keys( $image_sizes ),
					'recommendation'       => 'Audit and disable unused thumbnail sizes',
					'impact_estimate'      => sprintf( 'Reducing to 5-6 sizes could save %d MB', round( $estimated_storage_mb * 0.40 ) ),
					'how_to_disable'       => 'Use Disable Site Icon Size or similar plugin',
				),
			);
		}

		// Check if theme adds custom sizes
		$default_sizes = array( 'thumbnail', 'medium', 'medium_large', 'large' );
		$custom_sizes  = array_diff( $intermediate_sizes, $default_sizes );

		if ( count( $custom_sizes ) > 5 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of custom sizes */
					__( '%d custom thumbnail sizes detected. Review if all are necessary to reduce storage usage.', 'wpshadow' ),
					count( $custom_sizes )
				),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/thumbnail-optimization',
				'meta'         => array(
					'custom_sizes'     => $custom_sizes,
					'custom_size_count' => count( $custom_sizes ),
					'recommendation'   => 'Audit custom sizes added by theme/plugins',
					'storage_impact'   => round( $estimated_storage_mb * ( count( $custom_sizes ) / $size_count ), 2 ) . ' MB',
				),
			);
		}

		return null;
	}
}
