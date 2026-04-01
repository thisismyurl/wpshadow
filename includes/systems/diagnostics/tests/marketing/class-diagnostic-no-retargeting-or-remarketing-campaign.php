<?php
/**
 * No Retargeting or Remarketing Campaign Diagnostic
 *
 * Checks if retargeting campaigns are in place.
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
 * Retargeting Campaign Diagnostic
 *
 * Most visitors (95%) leave without buying. Retargeting brings them back.
 * It's the most cost-effective way to convert people already interested.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Retargeting_Or_Remarketing_Campaign extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'no-retargeting-remarketing-campaign';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Retargeting/Remarketing Campaign';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if retargeting campaigns are in place';

	/**
	 * Diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Run diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		if ( ! self::has_retargeting() ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No retargeting campaign detected. 95% of visitors leave without buying. But 26% visit again. Retargeting shows them ads on other sites, bringing them back. Cheapest traffic you\'ll ever buy. Setup: 1) Install tracking pixel (Google Ads, Facebook, LinkedIn), 2) Create audiences (all visitors, cart abandoners, by page visited), 3) Create ads (remind them, address objections), 4) Set frequency (show max 3x/day, 30 days), 5) Optimize (test different messages), 6) Track ROAS (every dollar spent should return $5+). The rule: People who already visited cost 30% less to convert than cold traffic.', 'wpshadow' ),
				'severity'    => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/retargeting-remarketing-campaign?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'     => array(
					'issue'               => __( 'No retargeting campaign detected', 'wpshadow' ),
					'recommendation'      => __( 'Implement retargeting campaigns across Google and social platforms', 'wpshadow' ),
					'business_impact'     => __( 'Missing 26% of interested visitors who return (30% cheaper conversion)', 'wpshadow' ),
					'retargeting_types'   => self::get_retargeting_types(),
					'audience_segments'   => self::get_audience_segments(),
				),
			);
		}

		return null;
	}

	/**
	 * Check if retargeting exists.
	 *
	 * @since 0.6093.1200
	 * @return bool True if retargeting detected, false otherwise.
	 */
	private static function has_retargeting() {
		// Check for retargeting-related content
		$retargeting_posts = self::count_posts_by_keywords(
			array(
				'retargeting',
				'remarketing',
				'pixel',
				'audience',
				'cart abandonment',
			)
		);

		if ( $retargeting_posts > 0 ) {
			return true;
		}

		// Check for retargeting plugins
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins = get_plugins();
		$retargeting_keywords = array(
			'retarget',
			'remarket',
			'pixel',
			'facebook pixel',
			'google ads',
		);

		foreach ( $all_plugins as $plugin_file => $plugin_data ) {
			$plugin_name = strtolower( $plugin_data['Name'] );
			foreach ( $retargeting_keywords as $keyword ) {
				if ( false !== strpos( $plugin_name, $keyword ) ) {
					if ( is_plugin_active( $plugin_file ) ) {
						return true;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Count posts containing specific keywords.
	 *
	 * @since 0.6093.1200
	 * @param  array $keywords Keywords to search for.
	 * @return int Number of matching posts.
	 */
	private static function count_posts_by_keywords( $keywords ) {
		$total = 0;

		foreach ( $keywords as $keyword ) {
			$posts = get_posts(
				array(
					's'              => $keyword,
					'posts_per_page' => 1,
					'post_type'      => array( 'post', 'page' ),
					'post_status'    => 'publish',
					'fields'         => 'ids',
				)
			);

			if ( ! empty( $posts ) ) {
				++$total;
			}
		}

		return $total;
	}

	/**
	 * Get retargeting types.
	 *
	 * @since 0.6093.1200
	 * @return array Retargeting types with descriptions.
	 */
	private static function get_retargeting_types() {
		return array(
			'google'   => array(
				'name'       => __( 'Google Ads (Search + Display)', 'wpshadow' ),
				'cost'       => __( '$0.50-2.00 per click (low cost)', 'wpshadow' ),
				'reach'      => __( '2 billion website visitors monthly', 'wpshadow' ),
				'best_for'   => __( 'Cart abandoners, high-intent visitors', 'wpshadow' ),
			),
			'facebook' => array(
				'name'       => __( 'Facebook/Instagram Ads', 'wpshadow' ),
				'cost'       => __( '$0.50-2.00 per click (low cost)', 'wpshadow' ),
				'reach'      => __( '3 billion monthly active users', 'wpshadow' ),
				'best_for'   => __( 'Brand awareness, lower-intent retargeting', 'wpshadow' ),
			),
			'linkedin' => array(
				'name'       => __( 'LinkedIn Ads (B2B)', 'wpshadow' ),
				'cost'       => __( '$3-10 per click (higher cost)', 'wpshadow' ),
				'reach'      => __( '900 million professionals', 'wpshadow' ),
				'best_for'   => __( 'B2B, software, professional services', 'wpshadow' ),
			),
			'email'    => array(
				'name'       => __( 'Email Retargeting', 'wpshadow' ),
				'cost'       => __( 'Free (own audience)', 'wpshadow' ),
				'reach'      => __( 'Email list (your subscribers)', 'wpshadow' ),
				'best_for'   => __( 'Highest ROI (owned audience)', 'wpshadow' ),
			),
		);
	}

	/**
	 * Get audience segments for retargeting.
	 *
	 * @since 0.6093.1200
	 * @return array Audience segments to retarget.
	 */
	private static function get_audience_segments() {
		return array(
			'all_visitors'     => __( 'All Site Visitors: Visited any page', 'wpshadow' ),
			'cart_abandoners'  => __( 'Cart Abandoners: Added to cart but didn\'t buy', 'wpshadow' ),
			'product_viewers'  => __( 'Product Viewers: Viewed specific product/page', 'wpshadow' ),
			'near_buyers'      => __( 'Near Buyers: Got to checkout but abandoned', 'wpshadow' ),
			'past_buyers'      => __( 'Past Buyers: Previous customers (upsell/cross-sell)', 'wpshadow' ),
			'engaged'          => __( 'Engaged Visitors: Spent 3+ minutes on site', 'wpshadow' ),
			'by_time'          => __( 'By Time: Visited 7+ days ago (more receptive)', 'wpshadow' ),
			'lapsed'           => __( 'Lapsed Customers: Haven\'t bought in 6 months', 'wpshadow' ),
		);
	}
}
