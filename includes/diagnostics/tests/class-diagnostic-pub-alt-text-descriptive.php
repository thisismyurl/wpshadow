<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;


class Diagnostic_Pub_Alt_Text_Descriptive extends Diagnostic_Base {
	protected static $slug = 'pub-alt-text-descriptive';

	protected static $title = 'Pub Alt Text Descriptive';

	protected static $description = 'Automatically initialized lean diagnostic for Pub Alt Text Descriptive. Optimized for minimal overhead while surfacing high-value signals.';

	protected static $family = 'general';

	protected static $family_label = 'General';

	/**
	 * Get diagnostic ID
	 */
	public static function get_id(): string {
		return 'pub-alt-text-descriptive';
	}

	/**
	 * Get diagnostic name
	 */
	public static function get_name(): string {
		return __( 'Alt Text is Descriptive', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 */
	public static function get_description(): string {
		return __( 'Alt text more than just filename?', 'wpshadow' );
	}

	/**
	 * Get diagnostic category
	 */
	public static function get_category(): string {
		return 'content_publishing';
	}

	/**
	 * Get threat level
	 *
	 * @return int 0-100 severity level
	 */
	public static function get_threat_level(): int {
		return 25;
	}

	/**
	 * Run diagnostic test
	 *
	 * @return array Diagnostic results
	 */
	public static function run(): array {
		$result = self::check();

		if ( null === $result ) {
			return array(
				'status'  => 'pass',
				'message' => __( 'Alt text is descriptive on published content', 'wpshadow' ),
				'data'    => array(),
			);
		}

		return array(
			'status'  => 'warning',
			'message' => $result['description'],
			'data'    => $result,
		);
	}

	/**
	 * Get KB article URL
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/pub-alt-text-descriptive';
	}

	/**
	 * Get training video URL
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/category-content-publishing';
	}

	/**
	 * Check if alt text is descriptive on published content.
	 *
	 * Analyzes recent published posts to detect images with non-descriptive alt text
	 * such as filenames, generic terms, or very short text.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issues detected, null otherwise.
	 */
	public static function check(): ?array {
		// Get recent published posts.
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => 20,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		if ( empty( $posts ) ) {
			return null;
		}

		$total_images             = 0;
		$non_descriptive_count    = 0;
		$problematic_alt_examples = array();

		foreach ( $posts as $post ) {
			$content = $post->post_content;

			// Parse HTML for images with alt text.
			preg_match_all( '/<img[^>]+alt=["\']([^"\']*)["\'][^>]*>/i', $content, $matches );

			if ( empty( $matches[1] ) ) {
				continue;
			}

			foreach ( $matches[1] as $alt_text ) {
				++$total_images;

				if ( self::is_non_descriptive_alt( $alt_text ) ) {
					++$non_descriptive_count;

					// Store first 5 examples for reporting.
					if ( count( $problematic_alt_examples ) < 5 ) {
						$problematic_alt_examples[] = array(
							'post_id'    => $post->ID,
							'post_title' => $post->post_title,
							'alt_text'   => $alt_text,
						);
					}
				}
			}
		}

		// Need at least some images to analyze.
		if ( 0 === $total_images ) {
			return null;
		}

		$descriptive_percentage = ( ( $total_images - $non_descriptive_count ) / $total_images ) * 100;

		/**
		 * Filters the minimum percentage of images that must have descriptive alt text.
		 *
		 * @since 1.2601.2148
		 *
		 * @param int $threshold Minimum percentage (0-100). Default 80.
		 */
		$threshold = apply_filters( 'wpshadow_descriptive_alt_text_threshold', 80 );

		// Flag if less than threshold of images have descriptive alt text.
		if ( $descriptive_percentage < $threshold ) {
			return array(
				'id'            => self::$slug,
				'title'         => __( 'Non-Descriptive Alt Text Found', 'wpshadow' ),
				'description'   => sprintf(
					/* translators: 1: percentage of images with descriptive alt, 2: total images analyzed */
					__( 'Only %1$.0f%% of images (%2$d total) have descriptive alt text. Alt text should describe the image content, not just be a filename or generic term like "image.jpg" or "photo".', 'wpshadow' ),
					$descriptive_percentage,
					$total_images
				),
				'severity'      => 'low',
				'threat_level'  => 25,
				'category'      => 'content_publishing',
				'kb_link'       => self::get_kb_article(),
				'training_link' => self::get_training_video(),
				'auto_fixable'  => false,
				'examples'      => $problematic_alt_examples,
			);
		}

		return null;
	}

	/**
	 * Check if alt text is non-descriptive.
	 *
	 * Detects common patterns of non-descriptive alt text:
	 * - Filenames (contains .jpg, .png, etc. or looks like a filename)
	 * - Generic terms (image, photo, picture, img, etc.)
	 * - Very short text (less than 3 characters)
	 * - Numeric-only or code-like patterns (IMG_001, DSC0001, etc.)
	 *
	 * @since  1.2601.2148
	 * @param  string $alt_text The alt text to analyze.
	 * @return bool True if non-descriptive, false otherwise.
	 */
	private static function is_non_descriptive_alt( string $alt_text ): bool {
		$alt_text = trim( $alt_text );

		// Empty alt is handled by coverage diagnostic.
		if ( empty( $alt_text ) ) {
			return false;
		}

		// Too short to be descriptive (less than 3 characters).
		if ( strlen( $alt_text ) < 3 ) {
			return true;
		}

		// Contains file extension - likely a filename.
		if ( preg_match( '/\.(jpe?g|png|gif|bmp|webp|svg|ico)$/i', $alt_text ) ) {
			return true;
		}

		// Looks like a filename pattern (underscores, hyphens, numbers).
		if ( preg_match( '/^[a-z0-9_\-]+$/i', $alt_text ) && preg_match( '/[_\-]/', $alt_text ) ) {
			return true;
		}

		// Common camera/device naming patterns.
		if ( preg_match( '/^(IMG|DSC|DCIM|P|PANO|WP)[_\-]?\d+$/i', $alt_text ) ) {
			return true;
		}

		// Generic terms (case-insensitive, whole word match).
		$generic_terms = array(
			'image',
			'photo',
			'picture',
			'img',
			'pic',
			'screenshot',
			'untitled',
			'default',
			'placeholder',
		);

		/**
		 * Filters the list of generic terms considered non-descriptive for alt text.
		 *
		 * Allows developers to customize which terms are flagged as non-descriptive
		 * alt text. Useful for adding language-specific terms or industry-specific jargon.
		 *
		 * @since 1.2601.2148
		 *
		 * @param array $generic_terms Array of lowercase strings to check against.
		 */
		$generic_terms = apply_filters( 'wpshadow_generic_alt_text_terms', $generic_terms );

		$alt_lower = strtolower( $alt_text );
		foreach ( $generic_terms as $term ) {
			if ( $alt_lower === $term ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Pub Alt Text Descriptive
	 * Slug: pub-alt-text-descriptive
	 *
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Checks if image alt text is descriptive rather than filenames or generic terms
	 *
	 * @since  1.2601.2148
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_pub_alt_text_descriptive(): array {
		$result = self::check();

		if ( null === $result ) {
			return array(
				'passed'  => true,
				'message' => __( 'Published posts have descriptive alt text (or no posts found)', 'wpshadow' ),
			);
		}

		$message = $result['description'] ?? __( 'Non-descriptive alt text detected on published content', 'wpshadow' );

		return array(
			'passed'  => false,
			'message' => $message,
		);
	}
}
