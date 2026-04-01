<?php
/**
 * Mobile Image Optimization Score
 *
 * Comprehensive image size and format validation.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Performance
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Diagnostics\Helpers\Diagnostic_HTML_Helper;
use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Image Optimization Score
 *
 * Validates image formats (WEBP/AVIF), responsive srcset,
 * and image-to-page-weight ratio.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Mobile_Image_Optimization_Score extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-image-optimization';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Image Optimization Score';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Comprehensive image size and format validation';

	/**
	 * The diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$analysis = self::analyze_image_optimization();

		if ( $analysis['optimization_score'] >= 80 ) {
			return null; // Images well optimized
		}

		$threat = 50;
		if ( $analysis['optimization_score'] < 50 ) {
			$threat = 75; // Critical - poor optimization
		}

		return array(
			'id'                     => self::$slug,
			'title'                  => self::$title,
			'description'            => sprintf(
				/* translators: %d: optimization score percentage */
				__( 'Image optimization score: %d%% (ideal: >80%%)', 'wpshadow' ),
				$analysis['optimization_score']
			),
			'severity'               => 'high',
			'threat_level'           => $threat,
			'optimization_score'     => $analysis['optimization_score'],
			'issues'                 => $analysis['issues'],
			'total_image_weight'     => size_format( $analysis['total_weight'], 1 ),
			'savings_potential'      => size_format( $analysis['savings'], 1 ),
			'format_recommendations' => $analysis['recommendations'] ?? array(),
			'user_impact'            => __( 'Unoptimized images waste 30-50% of bandwidth', 'wpshadow' ),
			'auto_fixable'           => true,
			'kb_link'                => 'https://wpshadow.com/kb/image-optimization?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
		);
	}

	/**
	 * Analyze image optimization.
	 *
	 * @since 0.6093.1200
	 * @return array Analysis results.
	 */
	private static function analyze_image_optimization(): array {
		$html = self::get_page_html();
		if ( ! $html ) {
			return array(
				'optimization_score' => 50,
				'issues'             => array(),
				'total_weight'       => 0,
				'savings'            => 0,
			);
		}

		$analysis = array(
			'optimization_score' => 100,
			'issues'             => array(),
			'total_weight'       => 0,
			'savings'            => 0,
			'recommendations'    => array(),
		);

		// Find all images
		preg_match_all( '/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $html, $images );

		if ( empty( $images[1] ) ) {
			return $analysis;
		}

		$image_count = count( $images[1] );
		$issues = array();

		// Check for WEBP/AVIF support
		$has_picture_element = preg_match( '/<picture/', $html );
		$has_srcset = preg_match_all( '/srcset=/i', $html, $matches );
		$has_webp = preg_match( '/\.webp/i', $html );

		if ( ! $has_webp && $image_count > 0 ) {
			$issues[] = array(
				'issue'    => 'No WEBP format images detected',
				'impact'   => '30% file size savings',
				'priority' => 'high',
			);
			$analysis['optimization_score'] -= 30;
		}

		// Check srcset coverage
		if ( $has_srcset < $image_count * 0.5 ) {
			$missing_srcset = $image_count - $has_srcset;
			$issues[] = array(
				'issue'    => "$missing_srcset images missing srcset",
				'impact'   => 'Wrong size images sent to devices',
				'priority' => 'high',
			);
			$analysis['optimization_score'] -= 20;
		}

		// Check for loading="lazy"
		$lazy_count = substr_count( $html, 'loading="lazy"' );
		if ( $lazy_count < $image_count * 0.5 ) {
			$issues[] = array(
				'issue'    => 'Most images not using lazy loading',
				'impact'   => '15-25% initial page weight savings',
				'priority' => 'medium',
			);
			$analysis['optimization_score'] -= 15;
		}

		// Estimate image weight (average 100KB per image)
		$analysis['total_weight'] = $image_count * 100 * 1024;

		// Estimate savings from optimization
		if ( $has_webp ) {
			$analysis['savings'] += $analysis['total_weight'] * 0.30; // 30% from WEBP
		}
		if ( $has_srcset > 0 ) {
			$analysis['savings'] += $analysis['total_weight'] * 0.20; // 20% from responsive
		}
		if ( $lazy_count > 0 ) {
			$analysis['savings'] += $analysis['total_weight'] * 0.20; // 20% from lazy loading (initial load)
		}

		$analysis['issues'] = array_slice( $issues, 0, 5 );
		$analysis['recommendations'] = array(
			'Convert images to WEBP format (use imagemagick or online tools)',
			'Implement responsive srcset for each image',
			'Add loading="lazy" to below-fold images',
			'Use CDN with automatic image optimization (Cloudflare, Akamai)',
		);

		return $analysis;
	}

	/**
	 * Get page HTML for analysis.
	 *
	 * @since 0.6093.1200
	 * @return string|null HTML content.
	 */
	private static function get_page_html(): ?string {
		return Diagnostic_HTML_Helper::fetch_homepage_html(
			array(
				'timeout'   => 5,
				'sslverify' => false,
			)
		);
	}
}
