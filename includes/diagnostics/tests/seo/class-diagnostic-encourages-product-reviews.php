<?php
/**
 * Product Reviews Encouraged Diagnostic
 *
 * Tests whether the site actively encourages and collects product reviews to build trust
 * and inform purchase decisions. Reviews are critical social proof for e-commerce.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Encourages_Product_Reviews Class
 *
 * Diagnostic #7: Product Reviews Encouraged from Specialized & Emerging Success Habits.
 * Checks if the site actively collects and displays product reviews.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Encourages_Product_Reviews extends Diagnostic_Base {

	protected static $slug = 'encourages-product-reviews';
	protected static $title = 'Product Reviews Encouraged';
	protected static $description = 'Tests whether the site actively encourages product reviews to build trust';
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

		// Check WooCommerce reviews enabled.
		$reviews_enabled = get_option( 'woocommerce_enable_reviews', 'yes' );
		if ( 'yes' === $reviews_enabled ) {
			++$score;
			$score_details[] = __( '✓ Product reviews enabled', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ Product reviews disabled', 'wpshadow' );
			$recommendations[] = __( 'Enable WooCommerce product reviews in settings', 'wpshadow' );
		}

		// Check existing reviews.
		$review_count = get_comments(
			array(
				'post_type' => 'product',
				'status'    => 'approve',
				'count'     => true,
			)
		);

		if ( $review_count >= 10 ) {
			$score += 2;
			$score_details[] = sprintf(
				/* translators: %d: number of reviews */
				__( '✓ %d+ product reviews published', 'wpshadow' ),
				$review_count
			);
		} elseif ( $review_count > 0 ) {
			++$score;
			$score_details[]   = sprintf( __( '◐ %d product review(s) found', 'wpshadow' ), $review_count );
			$recommendations[] = __( 'Actively solicit reviews from customers post-purchase', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No product reviews found', 'wpshadow' );
			$recommendations[] = __( 'Send automated review request emails 7-14 days after delivery', 'wpshadow' );
		}

		// Check review request plugins.
		$review_plugins = array(
			'woocommerce-photo-reviews/woocommerce-photo-reviews.php',
			'customer-reviews-woocommerce/customer-reviews-woocommerce.php',
		);

		$has_review_plugin = false;
		foreach ( $review_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_review_plugin = true;
				++$score;
				$score_details[] = __( '✓ Review collection plugin active', 'wpshadow' );
				break;
			}
		}

		if ( ! $has_review_plugin ) {
			$score_details[]   = __( '✗ No automated review collection', 'wpshadow' );
			$recommendations[] = __( 'Install a review plugin to automate review requests via email', 'wpshadow' );
		}

		// Check review incentives.
		$incentive_content = get_posts(
			array(
				'post_type'      => 'any',
				'posts_per_page' => 3,
				'post_status'    => 'publish',
				's'              => 'review discount reward leave feedback',
			)
		);

		if ( ! empty( $incentive_content ) ) {
			++$score;
			$score_details[] = __( '✓ Review incentives mentioned', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No review incentive program', 'wpshadow' );
			$recommendations[] = __( 'Offer small incentives (5-10% off next order) for verified reviews', 'wpshadow' );
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
				__( 'Product reviews score: %d%%. 93%% of consumers read reviews before buying, and products with 50+ reviews convert 4.6x better than products without reviews. Star ratings in search results increase CTR by 35%%.', 'wpshadow' ),
				$score_percentage
			),
			'severity'         => $severity,
			'threat_level'     => $threat_level,
			'auto_fixable'     => false,
			'kb_link'          => 'https://wpshadow.com/kb/product-reviews?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'          => $score_details,
			'recommendations'  => $recommendations,
			'impact'           => __( 'Product reviews provide social proof, improve SEO, and give valuable feedback for product improvements.', 'wpshadow' ),
		);
	}
}
