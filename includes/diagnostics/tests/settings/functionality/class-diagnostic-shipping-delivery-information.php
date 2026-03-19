<?php
/**
 * Shipping & Delivery Information Clarity Diagnostic
 *
 * Checks if shipping costs and delivery information are clear before checkout.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Diagnostics\Helpers\Diagnostic_HTML_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shipping & Delivery Information Clarity Diagnostic Class
 *
 * 60% abandon carts due to unexpected shipping costs. Showing shipping
 * information upfront reduces abandonment.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Shipping_Delivery_Information extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'shipping-delivery-information';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Shipping & Delivery Information Clarity';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if shipping costs and delivery timeframes are clear before checkout';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'conversion';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if ecommerce is active.
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$has_woocommerce = is_plugin_active( 'woocommerce/woocommerce.php' );
		$has_edd         = is_plugin_active( 'easy-digital-downloads/easy-digital-downloads.php' );

		if ( ! $has_woocommerce && ! $has_edd ) {
			return null; // Not applicable for non-ecommerce sites.
		}

		$issues         = array();
		$shipping_score = 0;
		$max_score      = 5;

		// Check for shipping calculator on product pages.
		$has_calculator = self::check_shipping_calculator();
		if ( $has_calculator ) {
			$shipping_score++;
		} else {
			$issues[] = 'shipping calculator on product pages';
		}

		// Check for shipping policy page.
		$has_policy = self::check_shipping_policy_page();
		if ( $has_policy ) {
			$shipping_score++;
		} else {
			$issues[] = 'shipping policy page';
		}

		// Check for delivery timeframes.
		$has_timeframes = self::check_delivery_timeframes();
		if ( $has_timeframes ) {
			$shipping_score++;
		} else {
			$issues[] = 'delivery timeframe information';
		}

		// Check for free shipping threshold.
		$has_free_shipping = self::check_free_shipping_threshold();
		if ( $has_free_shipping ) {
			$shipping_score++;
		} else {
			$issues[] = 'free shipping threshold display';
		}

		// Check for international shipping.
		$has_international = self::check_international_shipping();
		if ( $has_international ) {
			$shipping_score++;
		} else {
			$issues[] = 'international shipping option';
		}

		$completion_percentage = ( $shipping_score / $max_score ) * 100;

		if ( $completion_percentage >= 80 ) {
			return null; // Shipping information is clear.
		}

		$severity     = $completion_percentage < 40 ? 'high' : 'medium';
		$threat_level = $completion_percentage < 40 ? 65 : 45;

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: completion percentage, 2: missing features */
				__( 'Shipping clarity at %1$d%%. Missing: %2$s. 60%% abandon carts due to unexpected shipping costs.', 'wpshadow' ),
				(int) $completion_percentage,
				implode( ', ', $issues )
			),
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/shipping-delivery-information',
			'meta'         => array(
				'completion_percentage' => $completion_percentage,
				'missing_features'      => $issues,
			),
		);
	}

	/**
	 * Check if shipping calculator exists on product pages.
	 *
	 * @since 1.6093.1200
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
	 * @since 1.6093.1200
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
	 * @since 1.6093.1200
	 * @return bool True if timeframes exist.
	 */
	private static function check_delivery_timeframes(): bool {
		// Check homepage for delivery timeframe mentions.
		$home_url = home_url( '/' );
		$html     = Diagnostic_HTML_Helper::fetch_url_with_cache( $home_url );

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
	 * @since 1.6093.1200
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
	 * @since 1.6093.1200
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
