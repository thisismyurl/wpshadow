<?php
/**
 * Coupon System Health Diagnostic
 *
 * Checks if discount codes are applying correctly.
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
 * Coupon System Health Diagnostic Class
 *
 * Verifies that the coupon/discount system is functioning correctly
 * and that coupons are applying properly to orders.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Coupon_System_Health extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'coupon-system-health';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Coupon System Health';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if discount codes are applying correctly';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'ecommerce';

	/**
	 * Run the coupon system health diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if coupon issues detected, null otherwise.
	 */
	public static function check() {
		$issues    = array();
		$warnings  = array();
		$stats     = array();

		// Check for WooCommerce.
		if ( ! function_exists( 'wc' ) || ! class_exists( 'WooCommerce' ) ) {
			$warnings[] = __( 'WooCommerce not active - skipping coupon check', 'wpshadow' );
			return null;
		}

		// Get active coupons.
		$coupons = get_posts( array(
			'post_type'      => 'shop_coupon',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
		) );

		$stats['total_coupons'] = count( $coupons );

		if ( empty( $coupons ) ) {
			$warnings[] = __( 'No coupons found', 'wpshadow' );
		} else {
			// Check for expired coupons still active.
			$expired_coupons = 0;
			$about_to_expire = 0;
			$expired_not_removed = 0;

			foreach ( $coupons as $coupon ) {
				$coupon_obj = new \WC_Coupon( $coupon->ID );
				$expiry_date = $coupon_obj->get_date_expires();

				if ( $expiry_date ) {
					$expiry_time = $expiry_date->getTimestamp();
					$now = time();

					if ( $expiry_time < $now ) {
						$expired_coupons++;
					} elseif ( $expiry_time < $now + ( 7 * 24 * 3600 ) ) { // 7 days.
						$about_to_expire++;
					}
				}
			}

			$stats['expired_coupons'] = $expired_coupons;
			$stats['about_to_expire'] = $about_to_expire;

			if ( $expired_coupons > 0 ) {
				$warnings[] = sprintf(
					/* translators: %d: count */
					__( '%d expired coupons still active - archive or delete them', 'wpshadow' ),
					$expired_coupons
				);
			}

			if ( $about_to_expire > 0 ) {
				$warnings[] = sprintf(
					/* translators: %d: count */
					__( '%d coupons expiring within 7 days', 'wpshadow' ),
					$about_to_expire
				);
			}
		}

		// Check coupon usage tracking.
		$usage_tracking = get_option( 'woocommerce_coupon_usage_tracking' );
		$stats['usage_tracking'] = boolval( $usage_tracking );

		if ( ! $usage_tracking ) {
			$warnings[] = __( 'Coupon usage tracking not enabled', 'wpshadow' );
		}

		// Check for usage limits on high-value coupons.
		$high_value_unlimited = 0;

		foreach ( $coupons as $coupon ) {
			$coupon_obj = new \WC_Coupon( $coupon->ID );
			$discount_type = $coupon_obj->get_discount_type();
			$coupon_amount = $coupon_obj->get_amount();
			$usage_limit = $coupon_obj->get_usage_limit();

			// If fixed discount >50 or percentage >30% with no usage limit.
			if ( ( $discount_type === 'fixed' && $coupon_amount > 50 ) ||
				 ( $discount_type === 'percent' && $coupon_amount > 30 ) ) {
				if ( empty( $usage_limit ) ) {
					$high_value_unlimited++;
				}
			}
		}

		$stats['high_value_unlimited_coupons'] = $high_value_unlimited;

		if ( $high_value_unlimited > 0 ) {
			$warnings[] = sprintf(
				/* translators: %d: count */
				__( '%d high-value coupons without usage limits - set limits to prevent abuse', 'wpshadow' ),
				$high_value_unlimited
			);
		}

		// Check minimum order value enforcement.
		$min_order_limit = get_option( 'woocommerce_coupon_min_order_value' );
		$stats['minimum_order_enforcement'] = ! empty( $min_order_limit );

		// Check for coupon redemption issues.
		$orders_with_coupons = 0;
		$coupon_total_value = 0;

		$recent_orders = wc_get_orders( array(
			'limit'      => 20,
			'orderby'    => 'date',
			'order'      => 'DESC',
		) );

		foreach ( $recent_orders as $order ) {
			$coupons_used = $order->get_coupon_codes();
			if ( ! empty( $coupons_used ) ) {
				$orders_with_coupons++;
				$coupon_total_value += $order->get_discount_total();
			}
		}

		$stats['recent_orders_with_coupons'] = $orders_with_coupons;
		$stats['coupon_discount_total'] = round( $coupon_total_value, 2 );

		// Check coupon application rate.
		if ( count( $recent_orders ) > 0 ) {
			$coupon_usage_rate = ( $orders_with_coupons / count( $recent_orders ) ) * 100;
			$stats['coupon_usage_rate_percent'] = round( $coupon_usage_rate, 1 );

			if ( $coupon_usage_rate === 0 && count( $coupons ) > 0 ) {
				$issues[] = __( 'Coupons exist but none are being used - check if they\'re working', 'wpshadow' );
			}
		}

		// Check for cart rule conflicts.
		$cart_rules = get_option( 'woocommerce_cart_discount_rules' );
		$stats['cart_rules_configured'] = ! empty( $cart_rules );

		// Check for coupon validity.
		if ( ! empty( $coupons ) ) {
			$invalid_coupons = 0;

			foreach ( $coupons as $coupon ) {
				$coupon_obj = new \WC_Coupon( $coupon->ID );

				// Check if coupon has valid settings.
				if ( empty( $coupon_obj->get_amount() ) ) {
					$invalid_coupons++;
				}
			}

			if ( $invalid_coupons > 0 ) {
				$warnings[] = sprintf(
					/* translators: %d: count */
					__( '%d coupons have invalid configuration', 'wpshadow' ),
					$invalid_coupons
				);
			}
		}

		// Check for coupon performance impact.
		$coupon_processing_time = get_option( 'woocommerce_coupon_processing_time' );
		$stats['coupon_processing_time_ms'] = ! empty( $coupon_processing_time ) ? intval( $coupon_processing_time ) : 'Not tracked';

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Coupon system has critical issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/coupon-system-health?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
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
				'description'  => __( 'Coupon system has recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/coupon-system-health?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'stats'    => $stats,
					'warnings' => $warnings,
				),
			);
		}

		return null; // Coupon system is healthy.
	}
}
