<?php
/**
 * Image Lazy Loading Coverage Analysis Diagnostic
 *
 * Calculates percentage of below-fold images using lazy loading.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26029.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Image Lazy Loading Coverage Analysis Class
 *
 * Tests lazy loading.
 *
 * @since 1.26029.0000
 */
class Diagnostic_Image_Lazy_Loading_Coverage_Analysis extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'image-lazy-loading-coverage-analysis';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Image Lazy Loading Coverage Analysis';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Calculates percentage of below-fold images using lazy loading';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26029.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$lazy_check = self::check_lazy_loading();
		
		if ( $lazy_check['has_issues'] ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $lazy_check['issues'] ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/image-lazy-loading-coverage-analysis',
				'meta'         => array(
					'total_images'        => $lazy_check['total_images'],
					'lazy_loaded_images'  => $lazy_check['lazy_loaded_images'],
					'lazy_loading_percent' => $lazy_check['lazy_loading_percent'],
					'recommendations'     => $lazy_check['recommendations'],
				),
			);
		}

		return null;
	}

	/**
	 * Check lazy loading coverage.
	 *
	 * @since  1.26029.0000
	 * @return array Check results.
	 */
	private static function check_lazy_loading() {
		$check = array(
			'has_issues'           => false,
			'issues'               => array(),
			'total_images'         => 0,
			'lazy_loaded_images'   => 0,
			'lazy_loading_percent' => 0,
			'recommendations'      => array(),
		);

		// Check homepage for lazy loading.
		$homepage_id = (int) get_option( 'page_on_front' );
		
		if ( $homepage_id > 0 ) {
			$content = get_post_field( 'post_content', $homepage_id );
		} else {
			// Get latest posts content.
			$recent_posts = get_posts( array(
				'posts_per_page' => 5,
				'post_status'    => 'publish',
			) );

			$content = '';
			foreach ( $recent_posts as $post ) {
				$content .= $post->post_content;
			}
		}

		if ( ! empty( $content ) ) {
			// Find all img tags.
			preg_match_all( '/<img[^>]+>/i', $content, $matches );

			if ( ! empty( $matches[0] ) ) {
				$check['total_images'] = count( $matches[0] );

				foreach ( $matches[0] as $img_tag ) {
					// Check for loading="lazy" attribute.
					if ( false !== strpos( $img_tag, 'loading="lazy"' ) || false !== strpos( $img_tag, "loading='lazy'" ) ) {
						$check['lazy_loaded_images']++;
					}

					// Check for data-lazy or similar attributes from lazy loading plugins.
					if ( false !== strpos( $img_tag, 'data-lazy' ) || 
					     false !== strpos( $img_tag, 'data-src' ) ||
					     false !== strpos( $img_tag, 'lazyload' ) ) {
						$check['lazy_loaded_images']++;
					}
				}

				// Calculate percentage.
				if ( $check['total_images'] > 0 ) {
					$check['lazy_loading_percent'] = round( ( $check['lazy_loaded_images'] / $check['total_images'] ) * 100, 1 );
				}
			}
		}

		// Detect issues.
		if ( $check['total_images'] > 5 && $check['lazy_loading_percent'] < 50 ) {
			$check['has_issues'] = true;
			$check['issues'][] = sprintf(
				/* translators: 1: number of images, 2: lazy loading percentage */
				__( 'Only %2$s%% of images (%1$d total) use lazy loading', 'wpshadow' ),
				$check['total_images'],
				number_format( $check['lazy_loading_percent'], 1 )
			);
			$check['recommendations'][] = __( 'Add loading="lazy" to below-fold images', 'wpshadow' );
		}

		if ( $check['total_images'] > 10 && $check['lazy_loaded_images'] === 0 ) {
			$check['has_issues'] = true;
			$check['issues'][] = sprintf(
				/* translators: %d: number of images */
				__( '%d images found with no lazy loading (increases initial page weight)', 'wpshadow' ),
				$check['total_images']
			);
			$check['recommendations'][] = __( 'Implement native lazy loading or use a plugin like Lazy Load by WP Rocket', 'wpshadow' );
		}

		return $check;
	}
}
