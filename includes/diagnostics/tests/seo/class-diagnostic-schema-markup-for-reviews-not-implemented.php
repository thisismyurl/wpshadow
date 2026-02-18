<?php
/**
 * Schema Markup For Reviews Not Implemented Diagnostic
 *
 * Checks if review schema is implemented.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Schema Markup For Reviews Not Implemented Diagnostic Class
 *
 * Detects missing review schema.
 *
 * @since 1.6030.2352
 */
class Diagnostic_Schema_Markup_For_Reviews_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'schema-markup-for-reviews-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Schema Markup For Reviews Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if review schema is implemented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for plugins that add Review schema markup.
		$review_schema_plugins = array(
			'woocommerce/woocommerce.php'                      => 'WooCommerce (product reviews)',
			'yoast-seo/wp-seo.php'                             => 'Yoast SEO',
			'wordpress-seo-premium/wp-seo-premium.php'         => 'Yoast SEO Premium',
			'schema-and-structured-data-for-wp/structured-data-for-wp.php' => 'Schema & Structured Data for WP',
			'all-in-one-seo-pack/all_in_one_seo_pack.php'      => 'All in One SEO',
			'seo-by-rank-math/rank-math.php'                   => 'Rank Math SEO',
			'wp-review/wp-review.php'                          => 'WP Review',
			'site-reviews/site-reviews.php'                    => 'Site Reviews',
		);

		$has_review_plugin = false;
		foreach ( $review_schema_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_review_plugin = true;
				break;
			}
		}

		// Only flag if site has reviews/ratings content.
		$has_reviews = false;

		// Check for WooCommerce products with reviews.
		if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			$product_count = wp_count_posts( 'product' );
			if ( isset( $product_count->publish ) && $product_count->publish > 0 ) {
				$has_reviews = true;
			}
		}

		// Check for comments on posts (could be reviews).
		$comment_count = wp_count_comments();
		if ( $comment_count->approved > 10 ) {
			$has_reviews = true;
		}

		// If site has review content but no schema plugin, flag it.
		if ( $has_reviews && ! $has_review_plugin ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Review schema markup not detected. Product/business reviews won\'t show star ratings in Google search results. Missing rich results opportunity.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/schema-markup-for-reviews-not-implemented',
				'details'     => array(
					'has_review_content'  => $has_reviews,
					'comment_count'       => (int) $comment_count->approved,
					'has_review_plugin'   => $has_review_plugin,
					'recommendation'       => __( 'Add Review schema to show star ratings in Google search results. Use Yoast SEO, Rank Math, or WP Review plugin. WooCommerce adds product review schema automatically.', 'wpshadow' ),
					'rich_results_benefit' => __( 'Reviews with star ratings get 35% higher click-through rates in search results. Google shows ratings for products, businesses, recipes, books, movies.', 'wpshadow' ),
					'review_schema_types' => array(
						__( 'Product Reviews: Star ratings for e-commerce products', 'wpshadow' ),
						__( 'Business Reviews: Ratings for local businesses', 'wpshadow' ),
						__( 'Recipe Reviews: Ratings for recipe content', 'wpshadow' ),
						__( 'Book/Movie Reviews: Ratings for entertainment content', 'wpshadow' ),
						__( 'Software Reviews: Ratings for SaaS/apps', 'wpshadow' ),
					),
					'recommended_plugins'  => array(
						'Yoast SEO (review schema built-in)',
						'Rank Math SEO (automatic review schema)',
						'WP Review (dedicated review plugin)',
						'Site Reviews (customer review plugin)',
						'WooCommerce (product review schema)',
					),
				),
			);
		}

		return null;
	}
}
