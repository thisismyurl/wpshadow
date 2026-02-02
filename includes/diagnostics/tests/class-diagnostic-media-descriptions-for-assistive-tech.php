<?php
/**
 * Media Descriptions for Assistive Tech Diagnostic
 *
 * Tests comprehensive descriptions for screen readers.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26033.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Media Descriptions for Assistive Tech Diagnostic Class
 *
 * Verifies that media library elements have comprehensive descriptions
 * including ARIA labels, titles, and descriptions for screen readers.
 *
 * @since 1.26033.0000
 */
class Diagnostic_Media_Descriptions_For_Assistive_Tech extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-descriptions-for-assistive-tech';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Media Descriptions for Assistive Tech';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests comprehensive descriptions for screen readers';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if alt text coverage is adequate.
		$sample_images = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_mime_type' => 'image',
				'posts_per_page' => 50,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		if ( ! empty( $sample_images ) ) {
			$missing_alt   = 0;
			$total_checked = 0;

			foreach ( $sample_images as $image ) {
				$alt_text = get_post_meta( $image->ID, '_wp_attachment_image_alt', true );
				$total_checked++;

				if ( empty( $alt_text ) ) {
					$missing_alt++;
				}
			}

			if ( $total_checked > 0 ) {
				$percentage = ( $missing_alt / $total_checked ) * 100;
				if ( $percentage > 30 ) {
					$issues[] = sprintf(
						/* translators: %d: percentage of images without alt text */
						__( '%d%% of images are missing alt text descriptions', 'wpshadow' ),
						round( $percentage )
					);
				}
			}
		}

		// Check if media library has ARIA labels.
		if ( ! function_exists( 'wp_enqueue_media' ) ) {
			$issues[] = __( 'Media library functionality is not available', 'wpshadow' );
		}

		// Check if media-views script is registered (contains ARIA).
		if ( ! wp_script_is( 'media-views', 'registered' ) ) {
			$issues[] = __( 'Media views script is not registered', 'wpshadow' );
		}

		// Check for media view strings filter (translatable ARIA labels).
		$has_strings_filter = has_filter( 'media_view_strings' );
		if ( ! $has_strings_filter ) {
			$issues[] = __( 'No media view strings filter detected for ARIA label customization', 'wpshadow' );
		}

		// Check if attachment metadata includes captions/descriptions.
		if ( ! empty( $sample_images ) ) {
			$missing_caption = 0;
			foreach ( $sample_images as $image ) {
				$caption = wp_get_attachment_caption( $image->ID );
				if ( empty( $caption ) ) {
					$missing_caption++;
				}
			}

			$caption_percentage = ( $missing_caption / count( $sample_images ) ) * 100;
			if ( $caption_percentage > 70 ) {
				$issues[] = sprintf(
					/* translators: %d: percentage of images without captions */
					__( '%d%% of images lack captions for additional context', 'wpshadow' ),
					round( $caption_percentage )
				);
			}
		}

		// Check if theme supports HTML5 for proper semantic markup.
		$theme_support = get_theme_support( 'html5' );
		if ( empty( $theme_support ) ) {
			$issues[] = __( 'Theme does not declare HTML5 support for semantic markup', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/media-descriptions-for-assistive-tech',
			);
		}

		return null;
	}
}
