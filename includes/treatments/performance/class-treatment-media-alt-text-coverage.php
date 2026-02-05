<?php
/**
 * Media Alt Text Coverage Treatment
 *
 * Measures percentage of images with alt text to
 * detect accessibility issues.
 *
 * @package    WPShadow
 * @subpackage Treatments\Tests
 * @since      1.6033.1615
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Media_Alt_Text_Coverage Class
 *
 * Checks image attachments for missing alt text.
 *
 * @since 1.6033.1615
 */
class Treatment_Media_Alt_Text_Coverage extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-alt-text-coverage';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Alt Text Coverage';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Measures percentage of images with alt text';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.1615
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$attachments = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_mime_type' => 'image',
				'posts_per_page' => 50,
				'post_status'    => 'inherit',
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		if ( empty( $attachments ) ) {
			return null;
		}

		$with_alt = 0;
		foreach ( $attachments as $attachment ) {
			$alt = get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true );
			if ( ! empty( $alt ) ) {
				$with_alt++;
			}
		}

		$total = count( $attachments );
		$coverage = ( $with_alt / max( 1, $total ) ) * 100;

		if ( $coverage < 80 ) {
			$issues[] = sprintf(
				/* translators: 1: percentage, 2: total images */
				__( 'Only %1$s%% of recent images have alt text (%2$s images checked); improve accessibility by adding alt text', 'wpshadow' ),
				number_format( $coverage, 2 ),
				number_format_i18n( $total )
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/media-alt-text-coverage',
			);
		}

		return null;
	}
}
