<?php
/**
 * No Customer Referral Program Diagnostic
 *
 * Checks if customer referral program exists.
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
 * Customer Referral Program Diagnostic
 *
 * Referred customers have 25% higher lifetime value and 16% lower
 * churn than other customers. They also convert 4x faster.
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_Customer_Referral_Program extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'no-customer-referral-program';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Customer Referral Program';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if customer referral program exists';

	/**
	 * Diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Run diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		if ( ! self::has_referral_program() ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No customer referral program detected. Your happiest customers want to refer you, but you haven\'t made it easy. Referred customers have 25% higher lifetime value and 16% lower churn (they\'re better customers). Plus they convert 4x faster. Program: 1) Make it easy (referral link or form), 2) Reward BOTH (referrer and referred), 3) Track properly (know who referred whom), 4) Promote heavily (remind customers regularly), 5) Make reward valuable ($25-100 for SaaS), 6) Require conversion (referred person must become customer), 7) Automate delivery. Best referral programs are mutual benefit: everyone wins.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/customer-referral-program',
				'details'     => array(
					'issue'               => __( 'No customer referral program detected', 'wpshadow' ),
					'recommendation'      => __( 'Implement referral program with incentives for both parties', 'wpshadow' ),
					'business_impact'     => __( 'Missing 25% higher LTV, 16% lower churn, 4x faster conversion', 'wpshadow' ),
					'program_components'  => self::get_program_components(),
					'reward_structures'   => self::get_reward_structures(),
				),
			);
		}

		return null;
	}

	/**
	 * Check if referral program exists.
	 *
	 * @since 1.6093.1200
	 * @return bool True if program detected, false otherwise.
	 */
	private static function has_referral_program() {
		// Check for referral-related content
		$referral_posts = self::count_posts_by_keywords(
			array(
				'referral',
				'refer a friend',
				'affiliate',
				'rewards program',
				'incentive program',
			)
		);

		if ( $referral_posts > 0 ) {
			return true;
		}

		// Check for referral plugins
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins = get_plugins();
		$referral_keywords = array(
			'referral',
			'refer',
			'affiliate',
			'reward',
		);

		foreach ( $all_plugins as $plugin_file => $plugin_data ) {
			$plugin_name = strtolower( $plugin_data['Name'] );
			foreach ( $referral_keywords as $keyword ) {
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
	 * @since 1.6093.1200
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
	 * Get program components.
	 *
	 * @since 1.6093.1200
	 * @return array Program components with descriptions.
	 */
	private static function get_program_components() {
		return array(
			'referral_link'    => __( 'Unique referral link (easy sharing, trackable)', 'wpshadow' ),
			'dashboard'        => __( 'Referrer dashboard (see referrals, track status, pending rewards)', 'wpshadow' ),
			'tracking'         => __( 'Automatic tracking (know who referred whom, conversion status)', 'wpshadow' ),
			'notification'     => __( 'Real-time notifications (referred became customer, earned reward)', 'wpshadow' ),
			'promotion'        => __( 'Promotion push (email, in-app, dashboard promoting program)', 'wpshadow' ),
			'reward_delivery'  => __( 'Automatic reward delivery (immediately or after first payment)', 'wpshadow' ),
			'transparency'     => __( 'Program transparency (explain rules clearly, no surprises)', 'wpshadow' ),
		);
	}

	/**
	 * Get reward structure examples.
	 *
	 * @since 1.6093.1200
	 * @return array Reward structure options.
	 */
	private static function get_reward_structures() {
		return array(
			'both_cash'       => array(
				'name'        => __( 'Both Receive Cash Reward', 'wpshadow' ),
				'example'     => __( 'Referrer: $50 | Referred: $50 credit', 'wpshadow' ),
				'best_for'    => __( 'High LTV products (SaaS, services)', 'wpshadow' ),
			),
			'tiered'          => array(
				'name'        => __( 'Tiered Rewards (More Referrals = More Money)', 'wpshadow' ),
				'example'     => __( '1-5 referrals: $50 | 6-10: $75 | 11+: $100', 'wpshadow' ),
				'best_for'    => __( 'Incentivizing high-volume referrers', 'wpshadow' ),
			),
			'both_discount'   => array(
				'name'        => __( 'Both Receive Discount', 'wpshadow' ),
				'example'     => __( 'Referrer: 20% off | Referred: 20% off first month', 'wpshadow' ),
				'best_for'    => __( 'Focusing on customer acquisition', 'wpshadow' ),
			),
			'referrer_cash'   => array(
				'name'        => __( 'Only Referrer Rewarded (Less Common)', 'wpshadow' ),
				'example'     => __( 'Referrer: $50 per successful referral', 'wpshadow' ),
				'best_for'    => __( 'Affiliate-style programs', 'wpshadow' ),
			),
		);
	}
}
