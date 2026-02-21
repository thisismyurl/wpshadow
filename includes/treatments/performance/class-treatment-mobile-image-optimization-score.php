<?php
/**
 * Mobile Image Optimization Score
 *
 * Comprehensive image size and format validation.
 *
 * @package    WPShadow
 * @subpackage Treatments\Performance
 * @since      1.602.1600
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Treatments\Helpers\Treatment_HTML_Helper;
use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Image Optimization Score
 *
 * Validates image formats (WEBP/AVIF), responsive srcset,
 * and image-to-page-weight ratio.
 *
 * @since 1.602.1600
 */
class Treatment_Mobile_Image_Optimization_Score extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-image-optimization';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Image Optimization Score';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Comprehensive image size and format validation';

	/**
	 * The treatment family.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.602.1600
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Image_Optimization_Score' );
	}

	/**
	 * Analyze image optimization.
	 *
	 * @since  1.602.1600
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
	 * @since  1.602.1600
	 * @return string|null HTML content.
	 */
	private static function get_page_html(): ?string {
		return Treatment_HTML_Helper::fetch_homepage_html(
			array(
				'timeout'   => 5,
				'sslverify' => false,
			)
		);
	}
}
