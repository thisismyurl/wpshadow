<?php
/**
 * Missing Responsive Image Srcset Diagnostic
 *
 * Detects images missing srcset attribute for multiple resolutions,
 * causing oversized mobile image delivery and poor performance.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Performance
 * @since      1.6028.2130
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Missing Responsive Image Srcset Diagnostic Class
 *
 * Checks for images lacking srcset attributes, which causes mobile devices
 * to download oversized images and waste bandwidth.
 *
 * @since 1.6028.2130
 */
class Diagnostic_Missing_Responsive_Srcset extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'missing-responsive-srcset';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Missing Responsive Image Srcset';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects images missing srcset attribute for responsive delivery';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6028.2130
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check cache first.
		$cache_key = 'wpshadow_missing_responsive_srcset_check';
		$cached    = get_transient( $cache_key );
		if ( false !== $cached ) {
			return $cached;
		}

		// Analyze images on sample pages.
		$image_analysis = self::analyze_images_on_pages();

		$total_images      = $image_analysis['total'];
		$images_with_srcset = $image_analysis['with_srcset'];
		$images_without    = $image_analysis['without_srcset'];

		$srcset_percentage = $total_images > 0 ? ( $images_with_srcset / $total_images ) * 100 : 100;

		// Determine if there's an issue.
		if ( $srcset_percentage >= 70 ) {
			$result = null; // Good srcset coverage.
		} else {
			$severity     = $srcset_percentage < 50 ? 'medium' : 'low';
			$threat_level = $srcset_percentage < 50 ? 45 : 30;

			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: srcset percentage */
					__( 'Only %.1f%% of images have srcset, causing oversized mobile downloads', 'wpshadow' ),
					$srcset_percentage
				),
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/performance-responsive-images',
				'family'       => self::$family,
				'meta'         => array(
					'total_images'      => $total_images,
					'with_srcset'       => $images_with_srcset,
					'without_srcset'    => $images_without,
					'srcset_percentage' => round( $srcset_percentage, 2 ),
					'thresholds'        => array(
						'good'       => 70,
						'acceptable' => 50,
						'poor'       => 30,
					),
				),
				'details'      => array(
					'missing_srcset_examples' => array_slice( $image_analysis['examples'], 0, 10 ),
				),
				'recommendations' => array(
					__( 'Use wp_get_attachment_image() to automatically generate srcset', 'wpshadow' ),
					__( 'Enable WordPress responsive images feature (on by default 4.4+)', 'wpshadow' ),
					__( 'Regenerate thumbnails to create multiple image sizes', 'wpshadow' ),
					__( 'Avoid hardcoding image URLs without srcset attributes', 'wpshadow' ),
					__( 'Consider using a responsive images plugin for better control', 'wpshadow' ),
				),
			);
		}

		// Cache for 12 hours.
		set_transient( $cache_key, $result, 12 * HOUR_IN_SECONDS );

		return $result;
	}

	/**
	 * Analyze images on sample pages.
	 *
	 * @since  1.6028.2130
	 * @return array Analysis results.
	 */
	private static function analyze_images_on_pages() {
		$total        = 0;
		$with_srcset  = 0;
		$without_srcset = 0;
		$examples     = array();

		// Analyze homepage.
		$home_analysis = self::analyze_images_on_page( home_url( '/' ) );
		$total        += $home_analysis['total'];
		$with_srcset  += $home_analysis['with_srcset'];
		$without_srcset += $home_analysis['without_srcset'];
		$examples      = array_merge( $examples, $home_analysis['examples'] );

		// Analyze recent posts.
		$posts = get_posts(
			array(
				'numberposts' => 3,
				'post_type'   => 'post',
				'post_status' => 'publish',
			)
		);

		foreach ( $posts as $post ) {
			$post_analysis = self::analyze_images_on_page( get_permalink( $post->ID ) );
			$total        += $post_analysis['total'];
			$with_srcset  += $post_analysis['with_srcset'];
			$without_srcset += $post_analysis['without_srcset'];
			$examples      = array_merge( $examples, $post_analysis['examples'] );
		}

		return array(
			'total'         => $total,
			'with_srcset'   => $with_srcset,
			'without_srcset' => $without_srcset,
			'examples'      => $examples,
		);
	}

	/**
	 * Analyze images on a specific page.
	 *
	 * @since  1.6028.2130
	 * @param  string $url URL to analyze.
	 * @return array Analysis results.
	 */
	private static function analyze_images_on_page( $url ) {
		$response = wp_remote_get( $url );

		if ( is_wp_error( $response ) ) {
			return array(
				'total'         => 0,
				'with_srcset'   => 0,
				'without_srcset' => 0,
				'examples'      => array(),
			);
		}

		$html = wp_remote_retrieve_body( $response );

		// Extract img tags.
		preg_match_all( '/<img[^>]+>/i', $html, $matches );

		$total         = 0;
		$with_srcset   = 0;
		$without_srcset = 0;
		$examples      = array();

		foreach ( $matches[0] as $img_tag ) {
			++$total;

			if ( stripos( $img_tag, 'srcset=' ) !== false ) {
				++$with_srcset;
			} else {
				++$without_srcset;
				// Extract src for example.
				if ( preg_match( '/src=["\']([^"\']+)["\']/i', $img_tag, $src_match ) ) {
					$examples[] = array(
						'src' => $src_match[1],
						'url' => $url,
					);
				}
			}
		}

		return array(
			'total'         => $total,
			'with_srcset'   => $with_srcset,
			'without_srcset' => $without_srcset,
			'examples'      => $examples,
		);
	}
}
