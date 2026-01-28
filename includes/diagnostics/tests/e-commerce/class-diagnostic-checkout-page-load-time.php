<?php
/**
 * Checkout Page Load Time Diagnostic
 *
 * Measures WooCommerce checkout page performance. Slow checkout pages
 * directly reduce conversion rates and revenue.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Checkout_Page_Load_Time Class
 *
 * Measures the performance of WooCommerce checkout page which directly impacts revenue.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Checkout_Page_Load_Time extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'checkout-page-load-time';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'WooCommerce Checkout Page Load Time';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Measures checkout page performance and revenue impact';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'e-commerce';

	/**
	 * Checkout load time threshold - good (milliseconds)
	 *
	 * @var int
	 */
	const CHECKOUT_GOOD = 1500;

	/**
	 * Checkout load time threshold - acceptable (milliseconds)
	 *
	 * @var int
	 */
	const CHECKOUT_ACCEPTABLE = 3000;

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if WooCommerce installed and checkout slow.
	 */
	public static function check() {
		// Check if WooCommerce is active
		if ( ! class_exists( 'WooCommerce' ) && ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			// WooCommerce not active - not applicable
			return null;
		}

		// Get checkout page URL
		$checkout_url = get_option( 'woocommerce_checkout_page_id' );
		if ( ! $checkout_url ) {
			// No checkout page configured
			return null;
		}

		$checkout_page_url = get_permalink( $checkout_url );

		if ( ! $checkout_page_url ) {
			return null;
		}

		// Measure checkout page load time
		$load_time = self::measure_page_load_time( $checkout_page_url );

		if ( $load_time <= self::CHECKOUT_GOOD ) {
			// Good performance
			return null;
		}

		if ( $load_time <= self::CHECKOUT_ACCEPTABLE ) {
			// Needs improvement
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					/* translators: %d: load time in ms, %d: conversion loss percentage */
					__( 'Checkout loads in %dms. At this speed you\'re losing ~%d%% of conversions.', 'wpshadow' ),
					(int) $load_time,
					self::estimate_conversion_loss( $load_time )
				),
				'severity'      => 'medium',
				'threat_level'  => 55,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/checkout-page-load-time',
				'family'        => self::$family,
				'meta'          => array(
					'checkout_load_time_ms'   => (int) $load_time,
					'threshold_good'          => self::CHECKOUT_GOOD,
					'threshold_acceptable'    => self::CHECKOUT_ACCEPTABLE,
					'estimated_conversion_loss' => self::estimate_conversion_loss( $load_time ) . '%',
					'revenue_at_risk'         => sprintf(
						/* translators: %s: currency amount estimate */
						__( 'Estimated %s per month lost to slow checkout', 'wpshadow' ),
						'$100-500'
					),
					'optimization_recommendations' => array(
						__( 'Disable unused checkout field plugins' ),
						__( 'Lazy load non-critical checkout elements' ),
						__( 'Optimize payment gateway integration' ),
						__( 'Reduce number of order review requests' ),
						__( 'Enable checkout page caching' ),
					),
				),
				'details'       => array(
					'issue'              => sprintf(
						/* translators: %d: load time */
						__( 'Checkout page loads in %dms.', 'wpshadow' ),
						(int) $load_time
					),
					'revenue_impact'     => sprintf(
						/* translators: %d: percentage, %s: example revenue */
						__( 'Every 1 second delay reduces conversions by ~7%%. You\'re likely losing %s/month.', 'wpshadow' ),
						'$500-2000'
					),
					'common_culprits'    => array(
						__( 'Slow payment gateway integration' ) => __( 'Stripe, PayPal, Square initialization delays' ),
						__( 'Too many checkout field plugins' ) => __( 'Extra fields, multi-step checkout' ),
						__( 'Checkout optimization plugins' ) => __( 'Ironic: some optimization plugins slow checkout' ),
						__( 'Heavy analytics tracking' ) => __( 'Google Analytics, Facebook Pixel on checkout' ),
						__( 'Database queries for tax/shipping' ) => __( 'Unoptimized lookup queries' ),
					),
					'action_plan'        => array(
						'Immediate (1 hour)' => array(
							__( 'Disable non-essential checkout plugins for A/B test' ),
							__( 'Measure impact of each' ),
							__( 'Keep only essentials' ),
						),
						'Short term (1 day)' => array(
							__( 'Enable WooCommerce checkout page caching' ),
							__( 'Reduce checkout fields to essential only' ),
							__( 'Optimize payment gateway code' ),
						),
						'Medium term (1 week)' => array(
							__( 'Implement Express Checkout (Apple Pay, Google Pay)' ),
							__( 'Test guest checkout vs account' ),
							__( 'Review and optimize database queries' ),
						),
					),
				),
			);
		}

		// Poor performance (critical)
		return array(
			'id'            => self::$slug,
			'title'         => self::$title,
			'description'   => sprintf(
				/* translators: %d: load time ms, %d: conversion loss % */
				__( 'CRITICAL: Checkout loads in %dms. You\'re losing ~%d%% of transactions.', 'wpshadow' ),
				(int) $load_time,
				self::estimate_conversion_loss( $load_time )
			),
			'severity'      => 'high',
			'threat_level'  => 80,
			'auto_fixable'  => false,
			'kb_link'       => 'https://wpshadow.com/kb/checkout-page-load-time',
			'family'        => self::$family,
			'meta'          => array(
				'checkout_load_time_ms'   => (int) $load_time,
				'threshold_good'          => self::CHECKOUT_GOOD,
				'threshold_acceptable'    => self::CHECKOUT_ACCEPTABLE,
				'estimated_conversion_loss' => self::estimate_conversion_loss( $load_time ) . '%',
				'monthly_revenue_loss'    => sprintf(
					/* translators: %s: example revenue amount */
					__( 'Likely losing %s/month to slow checkout', 'wpshadow' ),
					'$1000-5000'
				),
				'urgent_actions'          => array(
					__( 'IMMEDIATELY disable all non-essential checkout plugins' ),
					__( 'Benchmark checkout before/after' ),
					__( 'This is direct revenue loss - FIX NOW' ),
				),
			),
			'details'       => array(
				'issue'         => sprintf(
					/* translators: %d: seconds */
					__( 'Checkout takes %d seconds to load.', 'wpshadow' ),
					(int) ( $load_time / 1000 )
				),
				'revenue_crisis' => sprintf(
					/* translators: %s: percentage/month estimate */
					__( 'At %s loss of conversions, you\'re losing thousands in revenue each month.', 'wpshadow' ),
					'10-15%'
				),
				'emergency_fixes' => array(
					__( '1. Temporarily disable all non-essential plugins on checkout' ),
					__( '2. Measure and confirm speed improvement' ),
					__( '3. Re-enable plugins one by one to find culprit' ),
					__( '4. Either optimize or remove problematic plugins' ),
					__( '5. Test on mobile (likely even slower)' ),
				),
			),
		);
	}

	/**
	 * Measure page load time for given URL.
	 *
	 * @since  1.2601.2148
	 * @param  string $url Page URL to measure.
	 * @return int Load time in milliseconds.
	 */
	private static function measure_page_load_time( $url ) {
		$start_time = microtime( true );

		$response = wp_remote_get(
			$url,
			array(
				'timeout'   => 10,
				'sslverify' => true,
				'blocking'  => true,
			)
		);

		$end_time = microtime( true );

		if ( is_wp_error( $response ) ) {
			return 5000; // Assume slow if error
		}

		// Calculate load time in milliseconds
		return (int) ( ( $end_time - $start_time ) * 1000 );
	}

	/**
	 * Estimate conversion loss percentage based on load time.
	 *
	 * @since  1.2601.2148
	 * @param  int $load_time Load time in milliseconds.
	 * @return int Estimated conversion loss percentage.
	 */
	private static function estimate_conversion_loss( $load_time ) {
		// Research shows 1s = 7% conversion loss
		$seconds = $load_time / 1000;
		$loss    = (int) ( $seconds * 7 );

		return min( $loss, 99 ); // Cap at 99%
	}
}
