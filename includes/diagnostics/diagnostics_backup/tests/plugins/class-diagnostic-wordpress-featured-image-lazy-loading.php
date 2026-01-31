<?php
/**
 * Wordpress Featured Image Lazy Loading Diagnostic
 *
 * Wordpress Featured Image Lazy Loading issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1288.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wordpress Featured Image Lazy Loading Diagnostic Class
 *
 * @since 1.1288.0000
 */
class Diagnostic_WordpressFeaturedImageLazyLoading extends Diagnostic_Base {

	protected static $slug = 'wordpress-featured-image-lazy-loading';
	protected static $title = 'Wordpress Featured Image Lazy Loading';
	protected static $description = 'Wordpress Featured Image Lazy Loading issue detected';
	protected static $family = 'functionality';

	public static function check() {
		$issues = array();

		// Check 1: Lazy loading enabled
		$lazy = get_option( 'featured_image_lazy_loading_enabled', 0 );
		if ( ! $lazy ) {
			$issues[] = 'Featured image lazy loading not enabled';
		}

		// Check 2: Loading attribute
		$loading_attr = get_option( 'featured_image_loading_attribute_set', 0 );
		if ( ! $loading_attr ) {
			$issues[] = 'Loading attribute not set for featured images';
		}

		// Check 3: Placeholder
		$placeholder = get_option( 'featured_image_placeholder_enabled', 0 );
		if ( ! $placeholder ) {
			$issues[] = 'Placeholder images not configured';
		}

		// Check 4: Responsive images
		$responsive = get_option( 'featured_image_responsive_enabled', 0 );
		if ( ! $responsive ) {
			$issues[] = 'Responsive featured images not enabled';
		}

		// Check 5: LQIP (Low Quality Image Placeholder)
		$lqip = get_option( 'featured_image_lqip_enabled', 0 );
		if ( ! $lqip ) {
			$issues[] = 'Low quality image placeholder not enabled';
		}

		// Check 6: CSS for lazy loading
		$css = get_option( 'featured_image_lazy_load_css_optimized', 0 );
		if ( ! $css ) {
			$issues[] = 'Lazy loading CSS not optimized';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 30;
			$threat_multiplier = 6;
			$max_threat = 60;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d lazy loading issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wordpress-featured-image-lazy-loading',
			);
		}

		return null;
	}
}
