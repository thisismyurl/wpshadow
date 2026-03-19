<?php
/**
 * Review and Rating System Diagnostic
 *
 * Checks if review and rating systems are implemented.
 *
 * @package WPShadow\Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Review and Rating System
 *
 * Detects whether the site has review/rating capabilities for products or services.
 */
class Diagnostic_Review_And_Rating_System extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'review-and-rating-system';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Review and Rating System';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for review and rating functionality';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'customer-feedback';

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Finding array if issues detected, null otherwise
	 */
	public static function check() {
		$issues  = array();
		$stats   = array();
		$plugins = array(
			'woocommerce/woocommerce.php'                      => 'WooCommerce',
			'wp-simple-rating/wp-simple-rating.php'            => 'WP Simple Rating',
			'rich-reviews/rich-reviews.php'                    => 'Rich Reviews',
			'total-product-review/total-product-review.php'    => 'Total Product Review',
			'advanced-reviews/advanced-reviews.php'            => 'Advanced Reviews',
		);

		$active = array();
		foreach ( $plugins as $file => $name ) {
			if ( is_plugin_active( $file ) ) {
				$active[] = $name;
			}
		}

		$stats['active_review_tools']    = count( $active );
		$stats['review_plugins_found']   = $active;
		$stats['woocommerce_active']     = is_plugin_active( 'woocommerce/woocommerce.php' );

		// Check for review/rating pages or content
		$review_pages = self::find_pages_by_keywords( array( 'reviews', 'rating', 'testimonials' ) );
		$stats['review_pages_found']     = count( $review_pages );

		if ( empty( $active ) && empty( $review_pages ) ) {
			$issues[] = __( 'No review or rating system detected', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'A review and rating system builds customer trust and provides social proof. Customers rely on reviews when making purchasing decisions. Implementing a review system can increase conversion rates and customer loyalty.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 60,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/reviews-ratings',
				'context'       => array(
					'stats'  => $stats,
					'issues' => $issues,
				),
			);
		}

		return null;
	}

	/**
	 * Find pages by keywords
	 *
	 * @param array $keywords Keywords to search for
	 * @return array List of matching pages
	 */
	private static function find_pages_by_keywords( array $keywords ): array {
		$matches = array();
		foreach ( $keywords as $keyword ) {
			$results = get_posts(
				array(
					's'              => $keyword,
					'post_type'      => array( 'page', 'post' ),
					'post_status'    => 'publish',
					'posts_per_page' => 5,
				)
			);
			foreach ( $results as $post ) {
				$matches[ $post->ID ] = get_the_title( $post );
			}
		}
		return array_values( $matches );
	}
}
