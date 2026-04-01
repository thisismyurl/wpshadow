<?php
/**
 * Image Alt Text Generic Diagnostic
 *
 * Checks if images have meaningful, descriptive alt text.
 *
 * @since 0.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Image Alt Text Diagnostic Class
 *
 * Validates that images have descriptive alt text for screen readers.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Image_Alt_Text_Generic extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'image-alt-text-generic';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Image Alt Text is Generic or Missing';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if images have meaningful, descriptive alt text';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues       = array();
		$total_images = 0;
		$bad_alt      = 0;

		// Check recent posts for images.
		$posts = get_posts(
			array(
				'numberposts' => 30,
				'post_status' => 'publish',
				'post_type'   => 'any',
			)
		);

		$generic_terms = array( 'image', 'photo', 'picture', 'graphic', 'img', 'icon', 'logo' );

		foreach ( $posts as $post ) {
			$content = $post->post_content;

			// Find all images.
			if ( preg_match_all( '/<img[^>]*>/i', $content, $img_matches ) ) {
				foreach ( $img_matches[0] as $img_tag ) {
					$total_images++;

					// Check if alt attribute exists.
					if ( ! preg_match( '/alt=["\']([^"\']*)["\']/', $img_tag, $alt_match ) ) {
						$bad_alt++;
						continue;
					}

					$alt_text = trim( $alt_match[1] );

					// Empty alt is fine for decorative images.
					if ( empty( $alt_text ) ) {
						continue;
					}

					// Check for filename pattern.
					if ( preg_match( '/\.(jpg|jpeg|png|gif|webp|svg)$/i', $alt_text ) ||
						 preg_match( '/^(img|image|photo|pic)[-_]?[0-9]+$/i', $alt_text ) ) {
						$bad_alt++;
						continue;
					}

					// Check for generic terms only.
					$words = preg_split( '/\s+/', strtolower( $alt_text ) );
					if ( count( $words ) === 1 && in_array( $words[0], $generic_terms, true ) ) {
						$bad_alt++;
						continue;
					}

					// Check if alt is too short (less than 10 chars, excluding decorative).
					if ( strlen( $alt_text ) < 10 && ! in_array( strtolower( $alt_text ), array( 'logo', 'icon' ), true ) ) {
						$bad_alt++;
					}
				}
			}
		}

		if ( $bad_alt > 5 ) {
			$issues[] = sprintf(
				/* translators: 1: number of images with bad alt, 2: total images */
				__( 'Found %1$d images (out of %2$d) with generic or missing alt text', 'wpshadow' ),
				$bad_alt,
				$total_images
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Your images have generic alt text like "image" or "IMG_1234"—this is like showing someone a book with all the words blurred. Screen reader users (about 2% of web users who are blind or have low vision) hear this alt text read aloud. When it just says "image" or a filename, they get zero information about what the image shows. Good alt text describes the content: instead of "IMG_1234.jpg", use "Golden retriever playing fetch in park."', 'wpshadow' ) . ' ' . implode( ' ', $issues ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/image-alt-text?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
