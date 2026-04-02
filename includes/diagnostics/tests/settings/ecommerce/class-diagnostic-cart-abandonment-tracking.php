<?php
/**
 * Cart Abandonment Tracking Diagnostic
 *
 * Checks if abandoned carts are being monitored.
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
 * Cart Abandonment Tracking Diagnostic Class
 *
 * Verifies that abandoned shopping carts are being tracked and
 * that recovery mechanisms are in place.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Cart_Abandonment_Tracking extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'cart-abandonment-tracking';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Cart Abandonment Tracking';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if abandoned carts are being monitored';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'ecommerce';

	/**
	 * Run the cart abandonment tracking diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if cart abandonment issues detected, null otherwise.
	 */
	public static function check() {
		$issues    = array();
		$warnings  = array();
		$stats     = array();

		// Check for WooCommerce.
		if ( ! function_exists( 'wc' ) || ! class_exists( 'WooCommerce' ) ) {
			$warnings[] = __( 'WooCommerce not active - skipping cart abandonment check', 'wpshadow' );
			return null;
		}

		// Check for cart abandonment recovery plugin.
		$recovery_plugins = array(
			'abandoned-cart-lite-for-woocommerce/abandoned-cart-lite.php',
			'woocommerce-abandoned-cart/woocommerce-abandoned-cart.php',
			'woolentor-addons/woolentor-addons.php',
			'cartflows/cartflows.php',
		);

		$has_recovery_plugin = false;
		$active_plugin = null;

		foreach ( $recovery_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_recovery_plugin = true;
				$active_plugin = $plugin;
				break;
			}
		}

		$stats['recovery_plugin'] = $active_plugin ?: 'None';

		if ( ! $has_recovery_plugin ) {
			$warnings[] = __( 'No cart abandonment recovery plugin - lost revenue opportunity', 'wpshadow' );
		}

		// Check for cart tracking enabled.
		$cart_tracking = get_option( 'woocommerce_enable_cart_abandonment_tracking' );
		$stats['cart_tracking_enabled'] = boolval( $cart_tracking );

		if ( ! $cart_tracking ) {
			$warnings[] = __( 'Cart abandonment tracking not enabled', 'wpshadow' );
		}

		// Check for abandoned cart detection threshold.
		$abandonment_threshold = get_option( 'woocommerce_abandonment_threshold_minutes', 60 );
		$stats['abandonment_threshold_minutes'] = intval( $abandonment_threshold );

		if ( $abandonment_threshold > 120 ) {
			$warnings[] = sprintf(
				/* translators: %d: minutes */
				__( 'Abandonment threshold is %d minutes - may be too long', 'wpshadow' ),
				$abandonment_threshold
			);
		}

		// Check for abandoned cart emails.
		$recovery_emails = get_option( 'woocommerce_abandonment_emails_enabled' );
		$stats['recovery_emails_enabled'] = boolval( $recovery_emails );

		if ( ! $recovery_emails ) {
			$warnings[] = __( 'Cart abandonment recovery emails not enabled', 'wpshadow' );
		}

		// Check email timing.
		$email_delay = get_option( 'woocommerce_abandonment_first_email_delay_minutes' );
		$stats['first_email_delay_minutes'] = $email_delay ? intval( $email_delay ) : 'Not set';

		if ( ! $email_delay ) {
			$warnings[] = __( 'First email delay not configured', 'wpshadow' );
		}

		// Check number of follow-up emails.
		$email_sequence = get_option( 'woocommerce_abandonment_email_sequence' );
		$email_count = ! empty( $email_sequence ) ? count( unserialize( $email_sequence ) ) : 0;
		$stats['recovery_email_count'] = $email_count;

		if ( $email_count === 0 ) {
			$warnings[] = __( 'No recovery email sequence configured', 'wpshadow' );
		} elseif ( $email_count === 1 ) {
			$warnings[] = __( 'Only one recovery email - consider multiple follow-ups', 'wpshadow' );
		}

		// Check for discount code in recovery emails.
		$recovery_discount = get_option( 'woocommerce_abandonment_recovery_discount_code' );
		$stats['recovery_discount'] = ! empty( $recovery_discount ) ? $recovery_discount : 'None';

		if ( ! $recovery_discount ) {
			$warnings[] = __( 'No discount code in recovery emails - reduces conversion', 'wpshadow' );
		}

		// Get abandoned carts count.
		$abandoned_carts = get_option( 'woocommerce_abandoned_carts_count' );
		$stats['abandoned_carts_count'] = ! empty( $abandoned_carts ) ? intval( $abandoned_carts ) : 0;

		// Calculate abandonment rate.
		$sessions = get_option( 'woocommerce_total_sessions' );
		if ( ! empty( $sessions ) && $sessions > 0 ) {
			$abandonment_rate = ( intval( $abandoned_carts ) / intval( $sessions ) ) * 100;
			$stats['abandonment_rate_percent'] = round( $abandonment_rate, 2 );

			// Typical abandonment rate 60-90%.
			if ( $abandonment_rate > 95 ) {
				$warnings[] = sprintf(
					/* translators: %d: percentage */
					__( 'Very high cart abandonment rate (%d%%) - investigate causes', 'wpshadow' ),
					intval( $abandonment_rate )
				);
			}
		}

		// Check for recovered revenue.
		$recovered_revenue = get_option( 'woocommerce_abandonment_recovered_revenue' );
		$stats['recovered_revenue'] = ! empty( $recovered_revenue ) ? floatval( $recovered_revenue ) : 0;

		// Check for abandoned cart analytics.
		$analytics_tracking = get_option( 'woocommerce_track_abandonment_analytics' );
		$stats['analytics_tracking'] = boolval( $analytics_tracking );

		if ( ! $analytics_tracking ) {
			$warnings[] = __( 'Cart abandonment analytics not enabled - can\'t optimize', 'wpshadow' );
		}

		// Check for SMS notifications (if enabled).
		$sms_enabled = get_option( 'woocommerce_abandonment_sms_enabled' );
		$stats['sms_notifications'] = boolval( $sms_enabled );

		// Check for push notifications.
		$push_enabled = get_option( 'woocommerce_abandonment_push_notifications_enabled' );
		$stats['push_notifications'] = boolval( $push_enabled );

		if ( ! $push_enabled ) {
			$warnings[] = __( 'Push notifications not enabled for cart recovery', 'wpshadow' );
		}

		// Check for cart recovery ROI.
		$recovery_roi = get_option( 'woocommerce_abandonment_recovery_roi' );
		$stats['recovery_roi_percent'] = ! empty( $recovery_roi ) ? intval( $recovery_roi ) : 'Not tracked';

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Cart abandonment tracking has critical issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/cart-abandonment-tracking',
				'context'      => array(
					'stats'    => $stats,
					'issues'   => $issues,
					'warnings' => $warnings,
				),
			);
		}

		// If only warnings.
		if ( ! empty( $warnings ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Cart abandonment tracking has recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'medium',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/cart-abandonment-tracking',
				'context'      => array(
					'stats'    => $stats,
					'warnings' => $warnings,
				),
			);
		}

		return null; // Cart abandonment tracking is active.
	}
}
