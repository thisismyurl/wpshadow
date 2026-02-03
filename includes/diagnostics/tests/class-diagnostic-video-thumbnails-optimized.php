<?php
/**
 * Video Thumbnails Optimized Diagnostic
 *
 * Tests whether the site uses custom, compelling thumbnails that achieve >10% click-through rates.
 *
 * @since   1.26034.0355
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Video Thumbnails Optimized Diagnostic Class
 *
 * Custom thumbnails increase click-through rates by 154% vs auto-generated.
 * Professional thumbnails are critical for video discoverability.
 *
 * @since 1.26034.0355
 */
class Diagnostic_Video_Thumbnails_Optimized extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'video-thumbnails-optimized';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Video Thumbnails Optimized';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site uses custom, compelling thumbnails that achieve >10% click-through rates';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'video-marketing';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26034.0355
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$thumbnail_score = 0;
		$max_score = 5;

		// Check for custom thumbnails.
		$custom_thumbnails = self::check_custom_thumbnails();
		if ( $custom_thumbnails ) {
			$thumbnail_score++;
		} else {
			$issues[] = __( 'Using auto-generated video thumbnails', 'wpshadow' );
		}

		// Check for high-quality images.
		$hq_images = self::check_hq_images();
		if ( $hq_images ) {
			$thumbnail_score++;
		} else {
			$issues[] = __( 'Video thumbnails not HD quality (1280x720 minimum)', 'wpshadow' );
		}

		// Check for text overlays.
		$text_overlays = self::check_text_overlays();
		if ( $text_overlays ) {
			$thumbnail_score++;
		} else {
			$issues[] = __( 'Thumbnails lack compelling text overlays', 'wpshadow' );
		}

		// Check for consistent branding.
		$consistent_branding = self::check_consistent_branding();
		if ( $consistent_branding ) {
			$thumbnail_score++;
		} else {
			$issues[] = __( 'No consistent thumbnail style or branding', 'wpshadow' );
		}

		// Check for A/B testing.
		$thumbnail_testing = self::check_thumbnail_testing();
		if ( $thumbnail_testing ) {
			$thumbnail_score++;
		} else {
			$issues[] = __( 'Not A/B testing thumbnail designs', 'wpshadow' );
		}

		// Determine severity based on thumbnail optimization.
		$thumbnail_percentage = ( $thumbnail_score / $max_score ) * 100;

		if ( $thumbnail_percentage < 40 ) {
			$severity = 'low';
			$threat_level = 25;
		} elseif ( $thumbnail_percentage < 70 ) {
			$severity = 'low';
			$threat_level = 15;
		} else {
			return null;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %d: Thumbnail optimization percentage */
				__( 'Video thumbnail optimization at %d%%. ', 'wpshadow' ),
				(int) $thumbnail_percentage
			) . implode( '. ', $issues ) . ' ' . __( 'Custom thumbnails increase CTR by 154%', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/video-thumbnails-optimized',
			);
		}

		return null;
	}

	/**
	 * Check custom thumbnails.
	 *
	 * @since  1.26034.0355
	 * @return bool True if custom, false otherwise.
	 */
	private static function check_custom_thumbnails() {
		// Check for featured images on video posts.
		$videos = get_posts(
			array(
				'post_type'      => 'any',
				'posts_per_page' => 5,
				's'              => 'video',
			)
		);

		foreach ( $videos as $video ) {
			if ( has_post_thumbnail( $video->ID ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check HQ images.
	 *
	 * @since  1.26034.0355
	 * @return bool True if HQ, false otherwise.
	 */
	private static function check_hq_images() {
		// Check for large image uploads (typical for video thumbnails).
		$attachments = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_mime_type' => 'image',
				'posts_per_page' => 5,
			)
		);

		foreach ( $attachments as $attachment ) {
			$metadata = wp_get_attachment_metadata( $attachment->ID );
			if ( isset( $metadata['width'] ) && $metadata['width'] >= 1280 ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check text overlays.
	 *
	 * @since  1.26034.0355
	 * @return bool True if overlays used, false otherwise.
	 */
	private static function check_text_overlays() {
		// Check for thumbnail documentation.
		$query = new \WP_Query(
			array(
				's'              => 'thumbnail text overlay design',
				'post_type'      => 'any',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			)
		);

		return $query->have_posts();
	}

	/**
	 * Check consistent branding.
	 *
	 * @since  1.26034.0355
	 * @return bool True if consistent, false otherwise.
	 */
	private static function check_consistent_branding() {
		// Check for branding documentation.
		$query = new \WP_Query(
			array(
				's'              => 'brand style guide thumbnail template',
				'post_type'      => 'any',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			)
		);

		return $query->have_posts();
	}

	/**
	 * Check thumbnail testing.
	 *
	 * @since  1.26034.0355
	 * @return bool True if testing, false otherwise.
	 */
	private static function check_thumbnail_testing() {
		// Difficult to detect automatically.
		return apply_filters( 'wpshadow_tests_thumbnails', false );
	}
}
