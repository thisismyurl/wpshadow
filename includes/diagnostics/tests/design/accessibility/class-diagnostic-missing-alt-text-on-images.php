<?php
/**
 * Missing Alt Text on Images Diagnostic
 *
 * Counts images without alt attributes for accessibility and SEO.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26028.1905
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Missing Alt Text on Images Class
 *
 * Tests for images missing alt attributes.
 *
 * @since 1.26028.1905
 */
class Diagnostic_Missing_Alt_Text_On_Images extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'missing-alt-text-on-images';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Missing Alt Text on Images';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Counts images without alt attributes for accessibility and SEO';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26028.1905
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$alt_text_stats = self::scan_images_for_alt_text();
		
		if ( $alt_text_stats['missing_alt_count'] > 0 ) {
			$percentage = $alt_text_stats['total_images'] > 0
				? round( ( $alt_text_stats['missing_alt_count'] / $alt_text_stats['total_images'] ) * 100 )
				: 0;

			$severity = 'low';
			if ( $percentage > 50 ) {
				$severity = 'high';
			} elseif ( $percentage > 30 ) {
				$severity = 'medium';
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: number of images without alt text, 2: total images, 3: percentage */
					__( '%1$d of %2$d images (%3$d%%) missing alt text (accessibility violation, lost SEO)', 'wpshadow' ),
					$alt_text_stats['missing_alt_count'],
					$alt_text_stats['total_images'],
					$percentage
				),
				'severity'     => $severity,
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/missing-alt-text-on-images',
				'meta'         => array(
					'total_images'          => $alt_text_stats['total_images'],
					'missing_alt_count'     => $alt_text_stats['missing_alt_count'],
					'percentage_missing'    => $percentage,
					'media_library_missing' => $alt_text_stats['media_library_missing'],
				),
			);
		}

		return null;
	}

	/**
	 * Scan images for missing alt text.
	 *
	 * @since  1.26028.1905
	 * @return array Statistics about alt text usage.
	 */
	private static function scan_images_for_alt_text() {
		global $wpdb;

		$stats = array(
			'total_images'          => 0,
			'missing_alt_count'     => 0,
			'media_library_missing' => 0,
		);

		// Check media library first.
		$attachments = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_mime_type' => 'image',
				'posts_per_page' => -1,
				'fields'         => 'ids',
			)
		);

		foreach ( $attachments as $attachment_id ) {
			++$stats['total_images'];
			
			$alt_text = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
			if ( empty( $alt_text ) ) {
				++$stats['missing_alt_count'];
				++$stats['media_library_missing'];
			}
		}

		// Also scan content for inline images (that may not be in media library).
		$posts = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT post_content
				FROM {$wpdb->posts}
				WHERE post_status = %s
				AND post_type IN ('post', 'page')
				LIMIT 500",
				'publish'
			)
		);

		foreach ( $posts as $post ) {
			// Find all img tags.
			preg_match_all( '/<img[^>]+>/i', $post->post_content, $matches );
			
			if ( ! empty( $matches[0] ) ) {
				foreach ( $matches[0] as $img_tag ) {
					// Check if img tag has alt attribute.
					if ( ! preg_match( '/alt=["\']([^"\']*)["\']/', $img_tag ) ) {
						// Missing alt attribute entirely.
						++$stats['missing_alt_count'];
					} elseif ( preg_match( '/alt=["\']["\']/i', $img_tag ) ) {
						// Empty alt attribute (which is OK for decorative images).
						// Don't count this as missing.
					}
				}
			}
		}

		return $stats;
	}
}
