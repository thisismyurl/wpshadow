<?php
/**
 * No Customer Loyalty or Retention Program Diagnostic
 *
 * Checks if customer loyalty/retention program is implemented.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Customer Loyalty/Retention Program Diagnostic
 *
 * Businesses with loyalty programs see 5-10% higher customer lifetime value.
 * Retention programs reduce churn by up to 25% and increase repeat purchases.
 *
 * @since 1.6035.0000
 */
class Diagnostic_No_Customer_Loyalty_Or_Retention_Program extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'no-customer-loyalty-retention-program';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Customer Loyalty or Retention Program Implemented';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if customer loyalty or retention program is implemented';

	/**
	 * Diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Run diagnostic check.
	 *
	 * @since  1.6035.0000
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		if ( ! self::has_loyalty_program() ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No customer loyalty or retention program detected. Acquiring new customers costs 5-25x more than retaining existing ones, yet you have no systematic retention strategy. Loyalty programs increase customer lifetime value by 5-10% and reduce churn by up to 25%. Implement: 1) Points/rewards system for purchases, 2) VIP tiers based on spending, 3) Birthday/anniversary offers, 4) Exclusive member benefits, 5) Referral rewards, 6) Win-back campaigns for inactive customers. Retention drives profitable growth.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/customer-loyalty-retention-program',
				'details'     => array(
					'issue'               => __( 'No loyalty or retention program detected', 'wpshadow' ),
					'recommendation'      => __( 'Implement customer loyalty program with points, rewards, and retention campaigns', 'wpshadow' ),
					'business_impact'     => __( 'Missing 5-10% additional customer lifetime value and 25% higher churn rate', 'wpshadow' ),
					'program_types'       => self::get_program_types(),
					'retention_tactics'   => self::get_retention_tactics(),
				),
			);
		}

		return null;
	}

	/**
	 * Check if loyalty/retention program exists.
	 *
	 * @since  1.6035.0000
	 * @return bool True if program detected, false otherwise.
	 */
	private static function has_loyalty_program() {
		// Check for loyalty-related content
		$loyalty_posts = self::count_posts_by_keywords(
			array( 'loyalty', 'rewards', 'points program', 'vip', 'member benefits', 'retention', 'customer club' )
		);

		if ( $loyalty_posts > 0 ) {
			return true;
		}

		// Check for loyalty plugins
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins = get_plugins();
		$loyalty_keywords = array(
			'loyalty',
			'reward',
			'points',
			'retention',
			'referral',
			'woocommerce points and rewards',
			'yith woocommerce points',
			'sumo reward points',
			'referralcandy',
			'swell',
		);

		foreach ( $all_plugins as $plugin_file => $plugin_data ) {
			$plugin_name = strtolower( $plugin_data['Name'] );
			foreach ( $loyalty_keywords as $keyword ) {
				if ( false !== strpos( $plugin_name, $keyword ) ) {
					// Check if plugin is active
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
	 * @since  1.6035.0000
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
	 * Get loyalty program types.
	 *
	 * @since  1.6035.0000
	 * @return array Program types with descriptions.
	 */
	private static function get_program_types() {
		return array(
			'points_based'   => __( 'Points for purchases (spend $1 = earn 1 point, redeem for rewards)', 'wpshadow' ),
			'tier_based'     => __( 'VIP tiers (bronze/silver/gold based on annual spending)', 'wpshadow' ),
			'referral'       => __( 'Refer-a-friend rewards (both parties get discount/points)', 'wpshadow' ),
			'subscription'   => __( 'Paid membership (annual fee for exclusive benefits)', 'wpshadow' ),
			'cashback'       => __( 'Percentage back on purchases (store credit)', 'wpshadow' ),
			'gamification'   => __( 'Badges, challenges, leaderboards to drive engagement', 'wpshadow' ),
		);
	}

	/**
	 * Get retention tactics.
	 *
	 * @since  1.6035.0000
	 * @return array Retention tactics with descriptions.
	 */
	private static function get_retention_tactics() {
		return array(
			'welcome_series'       => __( 'Onboard new customers with 3-5 email sequence', 'wpshadow' ),
			'birthday_rewards'     => __( 'Special offers on customer birthdays (40% higher redemption)', 'wpshadow' ),
			'anniversary'          => __( 'Celebrate customer anniversary with exclusive gift', 'wpshadow' ),
			'reactivation'         => __( 'Win-back campaigns for customers inactive 60+ days', 'wpshadow' ),
			'surprise_delight'     => __( 'Random acts of appreciation (unexpected free gift)', 'wpshadow' ),
			'exclusive_access'     => __( 'Early access to new products for loyal customers', 'wpshadow' ),
			'personalized_offers'  => __( 'Targeted discounts based on purchase history', 'wpshadow' ),
			'feedback_loops'       => __( 'Ask for feedback and act on it (close the loop)', 'wpshadow' ),
		);
	}
}
