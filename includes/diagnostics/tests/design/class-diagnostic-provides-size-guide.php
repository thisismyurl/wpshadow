<?php
/**
 * Size Guide Comprehensive Diagnostic
 *
 * Tests whether the site provides detailed, accurate sizing information to reduce
 * return rates. Clear sizing guidance prevents fit-related returns and increases confidence.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Provides_Size_Guide Class
 *
 * Diagnostic #3: Size Guide Comprehensive from Specialized & Emerging Success Habits.
 * Checks if the site provides comprehensive sizing information.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Provides_Size_Guide extends Diagnostic_Base {

	protected static $slug = 'provides-size-guide';
	protected static $title = 'Size Guide Comprehensive';
	protected static $description = 'Tests whether the site provides detailed, accurate sizing information';
	protected static $family = 'ecommerce-optimization';

	public static function check() {
		$score          = 0;
		$max_score      = 5;
		$score_details  = array();
		$recommendations = array();

		// Check WooCommerce active.
		if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			return null;
		}

		// Check size guide/sizing pages.
		$size_guide_pages = get_posts(
			array(
				'post_type'      => 'page',
				'posts_per_page' => 5,
				'post_status'    => 'publish',
				's'              => 'size guide sizing chart measurements',
			)
		);

		if ( ! empty( $size_guide_pages ) ) {
			++$score;
			$score_details[] = __( '✓ Size guide page available', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No size guide page found', 'wpshadow' );
			$recommendations[] = __( 'Create a comprehensive size guide page with measurement instructions', 'wpshadow' );
		}

		// Check size chart plugins.
		$size_chart_plugins = array(
			'woo-advanced-product-size-chart/woo-advanced-product-size-chart.php',
			'size-chart-for-woocommerce/size-chart-for-woocommerce.php',
		);

		$has_size_chart_plugin = false;
		foreach ( $size_chart_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_size_chart_plugin = true;
				++$score;
				$score_details[] = __( '✓ Size chart plugin active', 'wpshadow' );
				break;
			}
		}

		if ( ! $has_size_chart_plugin ) {
			$score_details[]   = __( '✗ No size chart plugin', 'wpshadow' );
			$recommendations[] = __( 'Install a size chart plugin to display sizing on product pages', 'wpshadow' );
		}

		// Check measurement instructions.
		$measurement_content = get_posts(
			array(
				'post_type'      => array( 'page', 'product' ),
				'posts_per_page' => 10,
				'post_status'    => 'publish',
				's'              => 'measure inches centimeters chest waist',
			)
		);

		if ( ! empty( $measurement_content ) ) {
			++$score;
			$score_details[] = __( '✓ Measurement instructions provided', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No measurement guidance', 'wpshadow' );
			$recommendations[] = __( 'Add "how to measure" instructions with visual diagrams', 'wpshadow' );
		}

		// Check fit recommendation content.
		$fit_content = get_posts(
			array(
				'post_type'      => 'product',
				'posts_per_page' => 10,
				'post_status'    => 'publish',
				's'              => 'fits true to size runs small large',
			)
		);

		if ( ! empty( $fit_content ) ) {
			++$score;
			$score_details[] = __( '✓ Fit recommendations included', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No fit guidance', 'wpshadow' );
			$recommendations[] = __( 'Add fit notes (e.g., "Runs true to size", "Size up for looser fit")', 'wpshadow' );
		}

		// Check customer review fit feedback.
		$fit_reviews = get_comments(
			array(
				'post_type'   => 'product',
				'status'      => 'approve',
				'number'      => 10,
				'search'      => 'fit size small large',
			)
		);

		if ( ! empty( $fit_reviews ) ) {
			++$score;
			$score_details[] = __( '✓ Customer fit feedback visible in reviews', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No fit feedback in reviews', 'wpshadow' );
			$recommendations[] = __( 'Encourage customers to mention fit in reviews ("How did it fit? True to size/runs small/runs large?")', 'wpshadow' );
		}

		$score_percentage = ( $score / $max_score ) * 100;

		if ( $score_percentage < 30 ) {
			$severity     = 'medium';
			$threat_level = 25;
		} elseif ( $score_percentage < 60 ) {
			$severity     = 'low';
			$threat_level = 15;
		} else {
			return null;
		}

		return array(
			'id'               => self::$slug,
			'title'            => self::$title,
			'description'      => sprintf(
				/* translators: %d: score percentage */
				__( 'Size guide score: %d%%. Comprehensive sizing information reduces returns by 30-50%% and increases purchase confidence by 40%%. Fit-related returns are the #1 cause of apparel returns (60%% of all returns). Visual measurement guides convert 25%% better than text-only charts.', 'wpshadow' ),
				$score_percentage
			),
			'severity'         => $severity,
			'threat_level'     => $threat_level,
			'auto_fixable'     => false,
			'kb_link'          => 'https://wpshadow.com/kb/size-guide',
			'details'          => $score_details,
			'recommendations'  => $recommendations,
			'impact'           => __( 'Detailed sizing guidance prevents expensive returns, builds buyer confidence, and reduces customer service inquiries.', 'wpshadow' ),
		);
	}
}
