<?php
/**
 * Image Alt Text Process Diagnostic
 *
 * Checks the media library for image attachments that have no alt text
 * stored in the _wp_attachment_image_alt post-meta field. Missing alt
 * text on non-decorative images violates WCAG 1.1.1.
 *
 * @package ThisIsMyURL\Shadow
 * @subpackage Diagnostics
 * @since 0.6095
 */

declare(strict_types=1);

namespace ThisIsMyURL\Shadow\Diagnostics;

use ThisIsMyURL\Shadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Image_Alt_Process Class
 *
 * @since 0.6095
 */
class Diagnostic_Image_Alt_Process extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'image-alt-process';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Image Alt Text Process';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks the media library for uploaded images that have no alt text set. Images without alt text are invisible to screen readers and fail WCAG 1.1.1.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Confidence level of this diagnostic.
	 *
	 * @var string
	 */
	protected static $confidence = 'standard';

	/**
	 * Run the diagnostic check.
	 *
	 * Fetches up to 500 image attachments from the media library and counts
	 * those with an empty or missing alt text meta value. Reports up to 10
	 * examples so the finding stays actionable.
	 *
	 * @since  0.6095
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		$images = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'post_mime_type' => array( 'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/avif' ),
				'posts_per_page' => 500,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'fields'         => 'ids',
			)
		);

		if ( empty( $images ) ) {
			return null;
		}

		$missing  = array();
		$examples = array();

		foreach ( $images as $image_id ) {
			$alt = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
			if ( '' !== trim( (string) $alt ) ) {
				continue;
			}

			$missing[] = $image_id;

			if ( count( $examples ) < 10 ) {
				$att        = get_post( $image_id );
				$examples[] = array(
					'attachment_id' => $image_id,
					'filename'      => basename( (string) $att->guid ),
					'title'         => $att->post_title,
					'edit_url'      => get_edit_post_link( $image_id, 'raw' ),
				);
			}
		}

		if ( empty( $missing ) ) {
			return null;
		}

		$count = count( $missing );
		$total = count( $images );

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: missing count, 2: total images */
				__( '%1$d of %2$d images in the media library have no alt text. Screen readers will announce these images by filename, providing no meaningful content to visually-impaired users.', 'thisismyurl-shadow' ),
				$count,
				$total
			),
			'severity'     => $count > 20 ? 'high' : 'medium',
			'threat_level' => $count > 20 ? 60 : 40,
			'details'      => array(
				'missing_count' => $count,
				'total_images'  => $total,
				'examples'      => $examples,
				'fix'           => __( 'Go to Media &rsaquo; Library, open each flagged image, and add a concise alt text that describes what the image shows. For purely decorative images, enter a single space to mark them as presentational.', 'thisismyurl-shadow' ),
			),
		);
	}
}
