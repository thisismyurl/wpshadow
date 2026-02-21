<?php
/**
 * Shipping & Delivery Information Clarity Treatment
 *
 * Checks if shipping costs and delivery information are clear before checkout.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6035.1020
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Treatments\Helpers\Treatment_HTML_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shipping & Delivery Information Clarity Treatment Class
 *
 * 60% abandon carts due to unexpected shipping costs. Showing shipping
 * information upfront reduces abandonment.
 *
 * @since 1.6035.1020
 */
class Treatment_Shipping_Delivery_Information extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'shipping-delivery-information';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Shipping & Delivery Information Clarity';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if shipping costs and delivery timeframes are clear before checkout';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'conversion';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6035.1020
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Shipping_Delivery_Information' );
	}

	/**
	 * Check if shipping calculator exists on product pages.
	 *
	 * @since  1.6035.1020
	 * @return bool True if calculator exists.
	 */
	private static function check_shipping_calculator(): bool {
		// Check for WooCommerce shipping calculator.
		if ( function_exists( 'WC' ) ) {
			$calculator_enabled = get_option( 'woocommerce_enable_shipping_calc', 'yes' );
			if ( 'yes' === $calculator_enabled ) {
				return true;
			}
		}

		// Check for shipping calculator shortcode/widget.
		global $wpdb;
		$shortcodes = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} 
			WHERE post_content LIKE '%shipping_calculator%' 
			AND post_status = 'publish' 
			LIMIT 1"
		);

		return $shortcodes > 0;
	}

	/**
	 * Check if shipping policy page exists.
	 *
	 * @since  1.6035.1020
	 * @return bool True if policy exists.
	 */
	private static function check_shipping_policy_page(): bool {
		// Check for shipping policy page.
		$args = array(
			'post_type'      => 'page',
			'posts_per_page' => 1,
			's'              => 'shipping policy delivery',
			'post_status'    => 'publish',
		);

		$policy_pages = get_posts( $args );
		if ( ! empty( $policy_pages ) ) {
			return true;
		}

		// Check WooCommerce shipping page setting.
		$shipping_page_id = get_option( 'woocommerce_shipping_policy_page_id', 0 );
		if ( $shipping_page_id > 0 ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if delivery timeframes are mentioned.
	 *
	 * @since  1.6035.1020
	 * @return bool True if timeframes exist.
	 */
	private static function check_delivery_timeframes(): bool {
		// Check homepage for delivery timeframe mentions.
		$home_url = home_url( '/' );
		$html     = Treatment_HTML_Helper::fetch_url_with_cache( $home_url );

		if ( empty( $html ) ) {
			return false;
		}

		$timeframe_keywords = array( 'delivery', 'business days', 'shipping time', 'estimated arrival' );
		foreach ( $timeframe_keywords as $keyword ) {
			if ( false !== stripos( $html, $keyword ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if free shipping threshold is displayed.
	 *
	 * @since  1.6035.1020
	 * @return bool True if threshold exists.
	 */
	private static function check_free_shipping_threshold(): bool {
		// Check for free shipping zones in WooCommerce.
		if ( function_exists( 'WC' ) && class_exists( 'WC_Shipping_Zones' ) ) {
			$zones = \WC_Shipping_Zones::get_zones();
			foreach ( $zones as $zone ) {
				if ( isset( $zone['shipping_methods'] ) ) {
					foreach ( $zone['shipping_methods'] as $method ) {
						if ( 'free_shipping' === $method->id && 'yes' === $method->enabled ) {
							return true;
						}
					}
				}
			}
		}

		return false;
	}

	/**
	 * Check if international shipping is available.
	 *
	 * @since  1.6035.1020
	 * @return bool True if international shipping exists.
	 */
	private static function check_international_shipping(): bool {
		// Check for multiple shipping zones.
		if ( function_exists( 'WC' ) && class_exists( 'WC_Shipping_Zones' ) ) {
			$zones = \WC_Shipping_Zones::get_zones();
			if ( count( $zones ) > 1 ) {
				return true; // Multiple zones suggest international shipping.
			}
		}

		return false;
	}
}
