<?php
/**
 * No Paid Advertising Strategy or Tracking Diagnostic
 *
 * Checks if paid advertising (ads) has a documented strategy and ROI tracking.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\BusinessPerformance
 * @since      1.6035.2100
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Paid Advertising Strategy Diagnostic
 *
 * Detects when paid advertising lacks a documented strategy or ROI tracking.
 * Without strategy, you're burning budget. Companies with ad strategy see
 * 2-3x better ROI than those running ads ad-hoc. Tracking is essential.
 *
 * @since 1.6035.2100
 */
class Diagnostic_No_Paid_Advertising_Strategy_Or_Tracking extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-paid-advertising-strategy-tracking';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Paid Advertising Strategy & Tracking Documented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if paid advertising has documented strategy and ROI tracking';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Run the diagnostic check
	 *
	 * @since  1.6035.2100
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$has_ads        = self::check_active_ads();
		$has_tracking   = self::check_ad_tracking();
		$has_strategy   = self::check_ad_strategy();

		if ( $has_ads && ( ! $has_tracking || ! $has_strategy ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Paid advertising is active but missing strategy or tracking. You\'re likely burning budget without knowing ROI. Companies with ad strategy see 2-3x better ROI. Create strategy: 1) Define target audience, 2) Choose platforms (Google, Facebook, LinkedIn), 3) Set budget/bid strategy, 4) Create ad variations, 5) Track conversions obsessively. Use UTM parameters on every ad link.', 'wpshadow' ),
				'severity'    => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/paid-advertising-strategy',
				'details'     => array(
					'ads_active'         => $has_ads,
					'tracking_configured' => $has_tracking,
					'strategy_documented' => $has_strategy,
					'platforms'          => self::get_advertising_platforms(),
					'metrics'            => self::get_ad_metrics(),
					'recommendation'     => __( 'Document your ad strategy and set up UTM/pixel tracking on all ads', 'wpshadow' ),
				),
			);
		}

		return null;
	}

	/**
	 * Check if ads are active
	 *
	 * @since  1.6035.2100
	 * @return bool True if ads detected
	 */
	private static function check_active_ads(): bool {
		// Check for Google Ads, Facebook Pixel, conversion tracking
		$response = wp_remote_get( home_url( '/' ) );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$body = wp_remote_retrieve_body( $response );

		// Check for ad pixels
		if ( preg_match( '/google.*ads|facebook.*pixel|conversion|gtag/i', $body ) ) {
			return true;
		}

		// Check for ad management plugins
		$plugins = get_plugins();

		$ad_keywords = array( 'ads', 'advertising', 'google ads', 'facebook', 'conversion' );

		foreach ( $plugins as $plugin_file => $plugin_data ) {
			$plugin_name = strtolower( $plugin_data['Name'] );
			foreach ( $ad_keywords as $keyword ) {
				if ( strpos( $plugin_name, $keyword ) !== false ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Check if ad tracking is configured
	 *
	 * @since  1.6035.2100
	 * @return bool True if tracking configured
	 */
	private static function check_ad_tracking(): bool {
		$response = wp_remote_get( home_url( '/' ) );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$body = wp_remote_retrieve_body( $response );

		// Check for Google Analytics
		if ( preg_match( '/gtag|ga-|google.*analytics/i', $body ) ) {
			return true;
		}

		// Check for Facebook Pixel
		if ( preg_match( '/facebook.*pixel|fbq/i', $body ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if ad strategy is documented
	 *
	 * @since  1.6035.2100
	 * @return bool True if strategy evident
	 */
	private static function check_ad_strategy(): bool {
		// Check for pages/posts mentioning ads or advertising strategy
		$strategy_posts = get_posts( array(
			'numberposts' => 5,
			's'           => 'advertising strategy OR ad strategy OR paid ads',
		) );

		return ! empty( $strategy_posts );
	}

	/**
	 * Get advertising platforms with descriptions
	 *
	 * @since  1.6035.2100
	 * @return array Array of platforms
	 */
	private static function get_advertising_platforms(): array {
		return array(
			array(
				'platform'  => 'Google Ads (Search)',
				'best_for'  => 'Intent-based (people searching for your solution)',
				'cost'      => 'Pay per click (CPC)',
				'timeframe' => 'Fast results (days/weeks)',
			),
			array(
				'platform'  => 'Facebook/Instagram Ads',
				'best_for'  => 'Awareness & retargeting (reach your audience)',
				'cost'      => 'CPM or CPC',
				'timeframe' => 'Slower ramp (weeks/months)',
			),
			array(
				'platform'  => 'LinkedIn Ads',
				'best_for'  => 'B2B (target by job title, company)',
				'cost'      => 'Higher CPC, better quality',
				'timeframe' => 'Weeks/months',
			),
			array(
				'platform'  => 'YouTube Ads',
				'best_for'  => 'Brand awareness & video promotion',
				'cost'      => 'CPV (cost per view)',
				'timeframe' => 'Weeks/months',
			),
		);
	}

	/**
	 * Get key ad metrics to track
	 *
	 * @since  1.6035.2100
	 * @return array Array of metrics
	 */
	private static function get_ad_metrics(): array {
		return array(
			'Impressions'      => 'Number of times ad shown',
			'Clicks'           => 'Number of people who clicked',
			'Click-Through Rate (CTR)' => 'Clicks / Impressions (target: 2-5%)',
			'Cost Per Click'   => 'Ad spend / Clicks (lower is better)',
			'Conversions'      => 'Number of people who completed goal',
			'Conversion Rate'  => 'Conversions / Clicks (target: 1-3%)',
			'Cost Per Acquisition (CPA)' => 'Ad spend / Conversions (key metric)',
			'Return on Ad Spend (ROAS)' => 'Revenue / Ad spend (target: 3:1+)',
		);
	}
}
