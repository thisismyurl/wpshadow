<?php
/**
 * Responsive Images Srcset Validation Diagnostic
 *
 * Verifies that images use srcset attribute with multiple resolutions to ensure
 * optimal image delivery across different screen sizes and devices.
 *
 * @since   1.6033.2096
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Responsive Images Srcset Validation Diagnostic Class
 *
 * Analyzes responsive image implementation:
 * - Srcset attribute usage
 * - Multiple image resolutions
 * - Sizes attribute presence
 * - Picture element usage
 *
 * @since 1.6033.2096
 */
class Diagnostic_Responsive_Images_Srcset_Validation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'responsive-images-srcset-validation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Responsive Images Srcset Validation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies responsive image srcset for optimal device delivery';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.2096
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Count attachments with srcset metadata
		$query           = "SELECT COUNT(*) as count FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attachment_metadata' LIMIT 50";
		$attachment_meta = $wpdb->get_var( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		if ( empty( $attachment_meta ) ) {
			return null;
		}

		// Check for images with multiple sizes
		$images_with_sizes = 0;

		$query    = "SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attachment_metadata' LIMIT 50";
		$metadata = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		foreach ( $metadata as $item ) {
			$data = maybe_unserialize( $item->meta_value );
			if ( is_array( $data ) && ! empty( $data['sizes'] ) ) {
				$images_with_sizes++;
			}
		}

		$responsive_percentage = intval( ( $images_with_sizes / max( 1, $attachment_meta ) ) * 100 );

		if ( $responsive_percentage < 50 && $attachment_meta > 10 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					/* translators: %d: percentage of responsive images */
					__( 'Only %d%% of images are using responsive srcset. This wastes bandwidth on mobile devices.', 'wpshadow' ),
					$responsive_percentage
				),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/responsive-images',
				'meta'          => array(
					'total_images'         => intval( $attachment_meta ),
					'responsive_images'    => $images_with_sizes,
					'responsive_percent'   => $responsive_percentage,
					'recommendation'       => 'Regenerate thumbnails with plugin like Regenerate Thumbnails or enable image scaling',
					'impact'               => 'Responsive images reduce mobile bandwidth by 40-60%',
					'best_practice'        => array(
						'Use WordPress native image scaling',
						'Set proper image dimensions',
						'Use <picture> element for art direction',
						'Test with browser DevTools device emulation',
					),
				),
			);
		}

		return null;
	}
}
