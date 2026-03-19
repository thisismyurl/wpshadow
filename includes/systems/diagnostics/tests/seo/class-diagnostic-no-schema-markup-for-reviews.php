<?php
/**
 * No Schema Markup for Reviews Diagnostic
 *
 * Detects when product/business reviews lack schema markup,
 * missing search result enhancements and trust signals.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\SEO
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Schema Markup for Reviews
 *
 * Checks whether reviews include structured data markup
 * for search result enhancements and rich snippets.
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_Schema_Markup_For_Reviews extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-schema-markup-reviews';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Schema Markup for Reviews';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether review schema markup is implemented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Whether this diagnostic is auto-fixable
	 *
	 * @var bool
	 */
	protected static $auto_fixable = false;

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for review plugins
		$has_review_plugin = is_plugin_active( 'woocommerce-product-reviews-pro/woocommerce-product-reviews-pro.php' ) ||
			is_plugin_active( 'trustpilot-review-badge/trustpilot.php' ) ||
			is_plugin_active( 'google-reviews-wp/google-reviews-wp.php' );

		// Check for review schema plugin
		$has_review_schema = is_plugin_active( 'yoast-seo/wp-seo.php' ) ||
			is_plugin_active( 'rank-math-seo/rank-math.php' );

		// Check if site has reviews
		$has_woocommerce = class_exists( 'WooCommerce' );
		$review_data = get_option( 'wpshadow_review_schema_enabled' );

		// If has reviews but no schema markup
		if ( ( $has_woocommerce || $has_review_plugin ) && ! $has_review_schema && ! $review_data ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'Your reviews aren\'t using schema markup, so Google can\'t display them in search results. When Google shows star ratings in search results (called "rich snippets"), it dramatically increases click-through rate—sometimes by 30% or more because people see the ratings before clicking. Schema markup tells Google: "This is a 5-star review from John on Jan 15." Without it, Google can\'t verify or display review data.',
					'wpshadow'
				),
				'severity'      => 'medium',
				'threat_level'  => 45,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Search Result CTR',
					'potential_gain' => '+30% click-through rate',
					'roi_explanation' => 'Review schema enables rich snippets in search results, showing star ratings before users click, dramatically improving CTR.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/schema-markup-reviews',
			);
		}

		return null;
	}
}
