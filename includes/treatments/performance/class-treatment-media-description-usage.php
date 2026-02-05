<?php
/**
 * Media Description Usage Treatment
 *
 * Checks whether media descriptions are populated and
 * usable for attachment pages and metadata.
 *
 * @package    WPShadow
 * @subpackage Treatments\Tests
 * @since      1.6033.1625
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Media_Description_Usage Class
 *
 * Validates media description usage for attachments.
 *
 * @since 1.6033.1625
 */
class Treatment_Media_Description_Usage extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-description-usage';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Media Description Usage';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether media descriptions are used properly';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.1625
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		$attachments = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_mime_type' => 'image',
				'posts_per_page' => 10,
				'post_status'    => 'inherit',
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		$description_count = 0;
		foreach ( $attachments as $attachment ) {
			if ( ! empty( $attachment->post_content ) ) {
				$description_count++;
			}
		}

		if ( 0 === $description_count && ! empty( $attachments ) ) {
			$issues[] = __( 'No recent media items include descriptions; consider adding descriptions for better SEO and accessibility', 'wpshadow' );
		}

		$attachment_template = locate_template( array( 'attachment.php', 'image.php' ) );
		if ( empty( $attachment_template ) ) {
			$issues[] = __( 'No attachment template detected; media descriptions may not be displayed on attachment pages', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/media-description-usage',
			);
		}

		return null;
	}
}
