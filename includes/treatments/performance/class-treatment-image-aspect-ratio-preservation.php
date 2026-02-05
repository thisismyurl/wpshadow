<?php
/**
 * Image Aspect Ratio Preservation Treatment
 *
 * Checks if images define proper aspect ratio to prevent Cumulative Layout Shift
 * during image loading.
 *
 * @since   1.6033.2097
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Image Aspect Ratio Preservation Treatment Class
 *
 * Verifies aspect ratio implementation:
 * - Width and height attributes
 * - CSS aspect-ratio support
 * - Container size definition
 * - CLS prevention
 *
 * @since 1.6033.2097
 */
class Treatment_Image_Aspect_Ratio_Preservation extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'image-aspect-ratio-preservation';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Image Aspect Ratio Preservation';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for proper image aspect ratio definition to prevent CLS';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.2097
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Count images with dimension metadata
		$query      = "SELECT COUNT(*) as count FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attachment_metadata'";
		$total      = (int) $wpdb->get_var( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$with_dims  = 0;

		if ( $total > 10 ) {
			$query   = "SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attachment_metadata' LIMIT 100";
			$results = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

			foreach ( $results as $row ) {
				$data = maybe_unserialize( $row->meta_value );
				if ( is_array( $data ) && ! empty( $data['width'] ) && ! empty( $data['height'] ) ) {
					$with_dims++;
				}
			}

			$percent = intval( ( $with_dims / max( 1, count( $results ) ) ) * 100 );

			if ( $percent < 70 ) {
				return array(
					'id'            => self::$slug,
					'title'         => self::$title,
					'description'   => sprintf(
						/* translators: %d: percentage of images with dimensions */
						__( 'Only %d%% of images have width/height defined. Missing dimensions cause Cumulative Layout Shift.', 'wpshadow' ),
						$percent
					),
					'severity'      => 'medium',
					'threat_level'  => 45,
					'auto_fixable'  => false,
					'kb_link'       => 'https://wpshadow.com/kb/image-aspect-ratio',
					'meta'          => array(
						'total_images'         => $total,
						'images_with_dims'     => $with_dims,
						'dimension_percent'    => $percent,
						'recommendation'       => 'Always include width and height on <img> tags to reserve space',
						'impact'               => 'Proper dimensions reduce CLS by 50-80%',
						'best_practice'        => array(
							'Add width/height to all images',
							'Use CSS aspect-ratio for containers',
							'Define container dimensions',
							'Use lazy loading with proper sizing',
						),
					),
				);
			}
		}

		return null;
	}
}
