<?php
/**
 * Mobile Filter/Sort Controls Treatment
 *
 * Validates that filter and sort controls (e.g., WooCommerce products) are
 * mobile-friendly with proper touch targets and mobile UI patterns.
 *
 * @package    WPShadow
 * @subpackage Treatments\Mobile
 * @since      1.602.1245
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Filter/Sort Controls Treatment Class
 *
 * Checks filter and sort controls for mobile usability including touch targets,
 * mobile patterns (bottom sheets, drawers), and accessibility.
 *
 * @since 1.602.1245
 */
class Treatment_Mobile_Filter_Sort_Controls extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-filter-sort-controls';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Filter/Sort Controls';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates filter and sort controls are mobile-friendly with proper touch targets and UI patterns';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'mobile';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.602.1245
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Filter_Sort_Controls' );
	}

	/**
	 * Check WooCommerce filter implementation.
	 *
	 * @since  1.602.1245
	 * @return array Issues found.
	 */
	private static function check_woocommerce_filters() {
		$issues = array();

		// Get shop page URL.
		$shop_id  = wc_get_page_id( 'shop' );
		$shop_url = $shop_id > 0 ? get_permalink( $shop_id ) : wc_get_page_permalink( 'shop' );

		if ( empty( $shop_url ) ) {
			return $issues;
		}

		// Capture shop page HTML.
		$html = self::capture_page_html( $shop_url );
		if ( empty( $html ) ) {
			return $issues;
		}

		// Check for filter widgets.
		$has_filters = preg_match( '/widget.*?(?:layered_nav|product_categories|product_tag_cloud|price_filter)/i', $html );

		if ( $has_filters ) {
			// Check if filters are in sidebar (bad for mobile).
			$has_sidebar = preg_match( '/<aside[^>]*class=["\'][^"\']*sidebar[^"\']*["\']/', $html ) ||
						   preg_match( '/<div[^>]*class=["\'][^"\']*sidebar[^"\']*["\']/', $html );

			if ( $has_sidebar ) {
				$issues[] = array(
					'issue_type'  => 'sidebar_filters',
					'severity'    => 'medium',
					'description' => 'Filters appear to be in sidebar (poor mobile UX - pushed below products)',
					'recommendation' => 'Use drawer/modal pattern for mobile filters',
				);
			}

			// Check for mobile filter button.
			$has_filter_button = preg_match( '/button[^>]*(?:filter|toggle.*filter)/i', $html );

			if ( ! $has_filter_button ) {
				$issues[] = array(
					'issue_type'  => 'no_filter_button',
					'severity'    => 'medium',
					'description' => 'No mobile filter button detected',
					'impact'      => 'Users may not find filters on mobile',
				);
			}
		} else {
			// No filters detected but products exist.
			$product_count = wp_count_posts( 'product' );
			if ( $product_count && $product_count->publish > 20 ) {
				$issues[] = array(
					'issue_type'  => 'no_filters',
					'severity'    => 'medium',
					'description' => sprintf( 'Shop has %d products but no filters detected', $product_count->publish ),
					'impact'      => 'Users cannot narrow down product selection',
				);
			}
		}

		// Check for sort dropdown.
		$has_orderby = preg_match( '/orderby/', $html );
		if ( $has_orderby ) {
			// Check if orderby is mobile-friendly.
			if ( preg_match( '/<select[^>]*name=["\']orderby["\'][^>]*>/', $html ) ) {
				// Standard dropdown - check if styled for mobile.
				$issues[] = array(
					'issue_type'  => 'default_sort_dropdown',
					'severity'    => 'low',
					'description' => 'Using default <select> dropdown for sort (consider custom mobile UI)',
					'impact'      => 'Native dropdowns can be clunky on mobile',
				);
			}
		}

		return $issues;
	}

	/**
	 * Capture page HTML.
	 *
	 * @since  1.602.1245
	 * @param  string $url Page URL.
	 * @return string HTML content.
	 */
	private static function capture_page_html( $url ) {
		$response = wp_remote_get(
			$url,
			array(
				'timeout'    => 10,
				'user-agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X)',
			)
		);

		if ( is_wp_error( $response ) ) {
			return '';
		}

		return wp_remote_retrieve_body( $response );
	}
}
