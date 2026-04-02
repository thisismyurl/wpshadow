<?php
/**
 * Sales and Pricing Transparency Diagnostic
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

class Diagnostic_Sales_Pricing_Transparency extends Diagnostic_Base {
	protected static $slug = 'sales-pricing-transparency';
	protected static $title = 'Sales and Pricing Transparency';
	protected static $description = 'Verifies pricing and sales comply with consumer protection laws';
	protected static $family = 'ecommerce';

	public static function check() {
		// Check if WooCommerce or Easy Digital Downloads is active.
		if ( ! class_exists( 'WooCommerce' ) && ! class_exists( 'Easy_Digital_Downloads' ) ) {
			return null; // No e-commerce plugin active.
		}

		$issues = array();

		// Check if prices include or exclude tax and if it's clearly stated.
		if ( class_exists( 'WooCommerce' ) ) {
			$tax_display_shop = get_option( 'woocommerce_tax_display_shop', 'excl' );
			$tax_display_cart = get_option( 'woocommerce_tax_display_cart', 'excl' );

			// Check if prices display suffixes.
			$price_display_suffix = get_option( 'woocommerce_price_display_suffix', '' );

			if ( empty( $price_display_suffix ) && 'excl' === $tax_display_shop ) {
				$issues[] = array(
					'issue'       => 'no_tax_suffix',
					'description' => __( 'Prices exclude tax but no suffix shown (e.g., "excl. VAT") - may confuse customers', 'wpshadow' ),
					'severity'    => 'medium',
				);
			}

			// Check for sale products without regular price shown.
			global $wpdb;
			$sales_without_regular = $wpdb->get_var(
				"SELECT COUNT(DISTINCT post_id) FROM {$wpdb->postmeta}
				WHERE meta_key = '_sale_price'
				AND meta_value != ''
				AND post_id NOT IN (
					SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_regular_price' AND meta_value != ''
				)"
			);

			if ( $sales_without_regular > 0 ) {
				$issues[] = array(
					'issue'       => 'sale_without_regular_price',
					'description' => sprintf(
						__( '%d products on sale without showing original price - required in many regions', 'wpshadow' ),
						$sales_without_regular
					),
					'severity'    => 'high',
				);
			}

			// Check if shipping costs are hidden until checkout.
			$shipping_zones = \WC_Shipping_Zones::get_zones();
			if ( empty( $shipping_zones ) ) {
				$issues[] = array(
					'issue'       => 'no_shipping_info',
					'description' => __( 'No shipping zones configured - customers cannot see shipping costs before checkout', 'wpshadow' ),
					'severity'    => 'medium',
				);
			}
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				__( 'Found %d pricing transparency issues', 'wpshadow' ),
				count( $issues )
			),
			'severity'     => 'medium',
			'threat_level' => 70,
			'auto_fixable' => false,
			'details'      => $issues,
			'kb_link'      => 'https://wpshadow.com/kb/sales-pricing-transparency',
		);
	}
}
