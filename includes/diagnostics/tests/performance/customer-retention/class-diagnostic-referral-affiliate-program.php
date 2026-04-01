<?php
/**
 * Referral Affiliate Program Diagnostic
 *
 * Detects when sites aren't leveraging referral or affiliate marketing.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\CustomerRetention
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\CustomerRetention;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Referral Affiliate Program Diagnostic Class
 *
 * Checks if the site has referral or affiliate programs for growth.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Referral_Affiliate_Program extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'referral-affiliate-program';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'No Referral or Affiliate Program';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects when sites aren\'t using referral or affiliate programs';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'customer-retention';

	/**
	 * Run the diagnostic check
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array or null if no issues found.
	 */
	public static function check() {
		// Check for referral/affiliate plugins.
		$referral_plugins = array(
			'affiliate-wp/affiliate-wp.php'         => 'AffiliateWP',
			'easy-affiliate/easy-affiliate.php'     => 'Easy Affiliate',
			'affiliates/affiliates.php'             => 'Affiliates Manager',
			'referral-candy/referral-candy.php'     => 'ReferralCandy',
			'automatorwp/automatorwp.php'           => 'AutomatorWP (can manage referrals)',
		);

		$active_programs = array();
		foreach ( $referral_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_programs[] = $name;
			}
		}

		if ( ! empty( $active_programs ) ) {
			return null; // Referral/affiliate program active.
		}

		// Check for e-commerce or service business (needs referrals most).
		$business_plugins = array(
			'woocommerce/woocommerce.php'    => 'WooCommerce',
			'easy-digital-downloads/easy-digital-downloads.php' => 'Easy Digital Downloads',
			'lifterlms/lifterlms.php'        => 'LifterLMS (Courses)',
			'memberpress/memberpress.php'    => 'MemberPress (Memberships)',
		);

		$business_type = array();
		foreach ( $business_plugins as $plugin => $type ) {
			if ( is_plugin_active( $plugin ) ) {
				$business_type[] = $type;
			}
		}

		// If no business plugins, less critical.
		if ( empty( $business_type ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'You\'re missing out on your customers bringing you new customers. Referral programs turn happy customers into salespeople who work for free (or for rewards)', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 55,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/referral-programs?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'context'      => array(
				'business_type'      => $business_type,
				'has_program'        => false,
				'impact'             => __( 'Referred customers have 37% higher retention rates and a 16% higher lifetime value. They trust recommendations from friends 92% vs. 33% for ads.', 'wpshadow' ),
				'recommendation'     => array(
					__( 'Create a simple "refer a friend" program with incentives', 'wpshadow' ),
					__( 'Offer discounts or rewards for successful referrals', 'wpshadow' ),
					__( 'Make sharing easy with unique referral links', 'wpshadow' ),
					__( 'Track referrals and reward both referrer and referee', 'wpshadow' ),
					__( 'Consider an affiliate program for influencers/partners', 'wpshadow' ),
					__( 'Promote your referral program at checkout and post-purchase', 'wpshadow' ),
					__( 'Send referral reminders to satisfied customers', 'wpshadow' ),
				),
				'roi'                => __( 'Referral programs cost 5x less than paid ads and convert 3-5x better', 'wpshadow' ),
				'customer_value'     => __( 'Referred customers spend 200% more than average customers', 'wpshadow' ),
				'trust_factor'       => __( 'People are 4x more likely to buy when referred by a friend', 'wpshadow' ),
			),
		);
	}
}
