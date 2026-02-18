<?php
/**
 * Media Caption Functionality Diagnostic
 *
 * Validates caption display support on images by
 * checking caption shortcode and attachment captions.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Tests
 * @since      1.6033.1625
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Media_Caption_Functionality Class
 *
 * Checks caption shortcode support and attachment captions.
 *
 * @since 1.6033.1625
 */
class Diagnostic_Media_Caption_Functionality extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-caption-functionality';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Media Caption Functionality';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates caption display and output functionality';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.1625
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		if ( ! shortcode_exists( 'caption' ) ) {
			$issues[] = __( 'Caption shortcode is not registered; image captions may not display', 'wpshadow' );
		}

		if ( ! has_filter( 'img_caption_shortcode' ) ) {
			$issues[] = __( 'No custom caption handling detected; verify caption output in the theme', 'wpshadow' );
		}

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

		$caption_count = 0;
		foreach ( $attachments as $attachment ) {
			$caption = wp_get_attachment_caption( $attachment->ID );
			if ( ! empty( $caption ) ) {
				$caption_count++;
			}
		}

		if ( 0 === $caption_count ) {
			$issues[] = __( 'No recent images have captions; consider adding captions for context and accessibility', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/media-caption-functionality',
			);
		}

		return null;
	}
}
