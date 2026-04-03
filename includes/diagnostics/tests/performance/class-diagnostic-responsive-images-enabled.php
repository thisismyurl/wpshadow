<?php
/**
 * Responsive Images Enabled Diagnostic
 *
 * Verifies that WordPress is generating srcset and sizes attributes for
 * uploaded images, enabling browsers to request appropriately-sized assets.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Responsive Images Enabled Diagnostic Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Responsive_Images_Enabled extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'responsive-images-enabled';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Responsive Images Enabled';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether WordPress responsive image srcset generation is functioning so browsers can load appropriately sized images for each device and viewport.';

	/**
	 * Gauge family/category for dashboard placement.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

/**
 * Confidence level of this diagnostic.
 *
 * @var string
 */
protected static $confidence = 'standard';

	/**
	 * Run the diagnostic check.
	 *
	 * Fetches a recent uploaded image attachment and tests whether
	 * wp_get_attachment_image() produces srcset/sizes attributes.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array when srcset is missing, null when healthy.
	 */
	public static function check() {
		global $wpdb;

		// Check if srcset generation has been suppressed via the wp_calculate_image_srcset filter.
		// Some performance plugins or themes return empty arrays to disable srcset.
		// We detect this by calling wp_get_attachment_image on a real attachment.
		$attachment_id = (int) $wpdb->get_var(
			"SELECT ID
			 FROM {$wpdb->posts}
			 WHERE post_type = 'attachment'
			   AND post_mime_type LIKE 'image/%'
			   AND post_status = 'inherit'
			 LIMIT 1"
		);

		if ( $attachment_id <= 0 ) {
			return null; // No images uploaded yet — cannot test.
		}

		$img_html = wp_get_attachment_image( $attachment_id, 'large' );

		if ( false !== strpos( $img_html, 'srcset=' ) ) {
			return null; // srcset is being generated.
		}

		// No srcset in output — check meta to confirm multiple sizes exist.
		$meta = wp_get_attachment_metadata( $attachment_id );
		if ( empty( $meta['sizes'] ) || count( $meta['sizes'] ) < 2 ) {
			return null; // Only one size available — srcset not applicable.
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Responsive image srcset attributes are not being output for images on this site. Without srcset, browsers cannot select the appropriately sized image for the viewport, causing mobile devices to download oversized images unnecessarily. Check whether a theme, plugin, or filter is suppressing wp_calculate_image_srcset and remove the restriction.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 35,
			'kb_link'      => 'https://wpshadow.com/kb/responsive-images?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'tested_attachment_id' => $attachment_id,
				'srcset_present'       => false,
			),
		);
	}
}
