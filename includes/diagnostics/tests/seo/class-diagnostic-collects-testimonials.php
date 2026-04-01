<?php
/**
 * Diagnostic: Customer Testimonials Collected
 *
 * Tests if site actively collects and displays customer testimonials.
 * Testimonials build trust and provide powerful social proof.
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
 * Customer Testimonials Collected Diagnostic Class
 *
 * Checks if site has systems to collect, manage, and display
 * customer testimonials and reviews.
 *
 * Detection methods:
 * - Testimonial plugins
 * - Review collection systems
 * - Product reviews (if ecommerce)
 * - Third-party review integrations
 *
 * @since 0.6093.1200
 */
class Diagnostic_Collects_Testimonials extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'collects-testimonials';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Customer Testimonials Collected';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if site actively collects and displays customer testimonials';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'user-engagement';

	/**
	 * Run the diagnostic check.
	 *
	 * Scoring system (5 points):
	 * - 1 point: Testimonial plugin installed
	 * - 1 point: Testimonial custom post type exists
	 * - 1 point: Product reviews enabled (if WooCommerce)
	 * - 1 point: Third-party reviews integrated (Trustpilot, etc.)
	 * - 1 point: Recent testimonials added
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$score     = 0;
		$max_score = 5;
		$details   = array();

		// Check for testimonial plugins.
		$testimonial_plugins = array(
			'strong-testimonials/strong-testimonials.php' => 'Strong Testimonials',
			'testimonial-slider-and-showcase/testimonial-slider-and-showcase.php' => 'Testimonial Slider',
			'testimonials-widget/testimonials-widget.php' => 'Testimonials Widget',
			'easy-testimonials/easy-testimonials.php'    => 'Easy Testimonials',
		);

		foreach ( $testimonial_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$score++;
				$details['testimonial_plugin'] = $name;
				break;
			}
		}

		// Check for testimonial custom post types.
		$testimonial_cpts = array( 'testimonial', 'testimonials', 'review', 'reviews' );
		$cpt_exists = false;

		foreach ( $testimonial_cpts as $cpt ) {
			if ( post_type_exists( $cpt ) ) {
				$cpt_exists = true;

				// Check for recent testimonials.
				$recent_testimonials = get_posts(
					array(
						'post_type'      => $cpt,
						'posts_per_page' => 1,
						'post_status'    => 'publish',
						'date_query'     => array(
							array(
								'after' => '90 days ago',
							),
						),
					)
				);

				if ( ! empty( $recent_testimonials ) ) {
					$score++;
					$details['recent_testimonials'] = true;
				}
				break;
			}
		}

		if ( $cpt_exists ) {
			$score++;
			$details['testimonial_cpt'] = true;
		}

		// Check WooCommerce reviews.
		if ( class_exists( 'WooCommerce' ) ) {
			$reviews_enabled = get_option( 'woocommerce_enable_reviews', 'yes' );

			if ( 'yes' === $reviews_enabled ) {
				$score++;
				$details['woocommerce_reviews'] = true;

				// Check for recent reviews.
				$recent_reviews = get_comments(
					array(
						'post_type'  => 'product',
						'status'     => 'approve',
						'number'     => 1,
						'date_query' => array(
							array(
								'after' => '30 days ago',
							),
						),
					)
				);

				if ( ! empty( $recent_reviews ) ) {
					$details['recent_product_reviews'] = true;
				}
			}
		}

		// Check for third-party review integrations.
		$review_integrations = array(
			'trustpilot-reviews/trustpilot.php'          => 'Trustpilot',
			'reviews.io/reviews-io.php'                  => 'Reviews.io',
			'yotpo-social-reviews/yotpo.php'             => 'Yotpo',
			'google-reviews-widget/google-reviews-widget.php' => 'Google Reviews',
		);

		foreach ( $review_integrations as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$score++;
				$details['review_integration'] = $name;
				break;
			}
		}

		// Calculate percentage score.
		$percentage = ( $score / $max_score ) * 100;

		// Pass if score is 60% or higher.
		if ( $percentage >= 60 ) {
			return null;
		}

		// Build finding.
		$severity     = $percentage < 30 ? 'medium' : 'low';
		$threat_level = (int) ( 50 - $percentage );

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: percentage score */
				__( 'Testimonial collection score: %d%%. Customer testimonials are powerful social proof that build trust.', 'wpshadow' ),
				(int) $percentage
			),
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/customer-testimonials?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => $details,
			'why_matters'  => self::get_why_matters(),
		);
	}

	/**
	 * Get the "Why This Matters" educational content.
	 *
	 * @since 0.6093.1200
	 * @return string Explanation of why this diagnostic matters.
	 */
	private static function get_why_matters() {
		return __(
			'Testimonials are social proof that your solution works. They answer objections, build credibility, and reduce purchase anxiety. A single testimonial that speaks to a specific pain point can be more persuasive than pages of marketing copy. Studies show 92% of consumers read reviews before buying. Without testimonials, you\'re asking visitors to trust you blindly. Collect them systematically and display them prominently.',
			'wpshadow'
		);
	}
}
