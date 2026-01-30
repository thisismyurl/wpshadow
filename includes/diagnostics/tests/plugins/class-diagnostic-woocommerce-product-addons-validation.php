<?php
/**
 * Woocommerce Product Addons Validation Diagnostic
 *
 * Woocommerce Product Addons Validation issues detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.645.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Woocommerce Product Addons Validation Diagnostic Class
 *
 * @since 1.645.0000
 */
class Diagnostic_WoocommerceProductAddonsValidation extends Diagnostic_Base {

	protected static $slug = 'woocommerce-product-addons-validation';
	protected static $title = 'Woocommerce Product Addons Validation';
	protected static $description = 'Woocommerce Product Addons Validation issues detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return null;
		}
		
		// Check if Product Addons is active
		if ( ! class_exists( 'WC_Product_Addons' ) && ! defined( 'WC_PRODUCT_ADDONS_VERSION' ) ) {
			return null;
		}

		$issues = array();
		$threat_level = 0;

		global $wpdb;

		// Check addon groups
		$addon_groups = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'global_product_addon'"
		);

		if ( $addon_groups > 0 ) {
			// Check for required field validation
			$validation_enabled = get_option( 'wc_product_addons_validate_required', 'yes' );
			if ( $validation_enabled === 'no' ) {
				$issues[] = 'required_field_validation_disabled';
				$threat_level += 25;
			}

			// Check price calculation
			$price_calculation = get_option( 'wc_product_addons_price_calculation', 'standard' );
			if ( $price_calculation === 'none' ) {
				$issues[] = 'price_calculation_disabled';
				$threat_level += 30;
			}
		}

		// Check for products with addons
		$products_with_addons = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta} 
			 WHERE meta_key = '_product_addons'"
		);

		if ( $products_with_addons > 0 ) {
			// Check cart validation
			$cart_validation = get_option( 'wc_product_addons_cart_validation', 'yes' );
			if ( $cart_validation === 'no' ) {
				$issues[] = 'cart_validation_disabled';
				$threat_level += 20;
			}

			// Check conditional logic
			$conditional_logic = get_option( 'wc_product_addons_conditional_logic', 'yes' );
			if ( $conditional_logic === 'no' ) {
				$issues[] = 'conditional_logic_disabled';
				$threat_level += 15;
			}
		}

		// Check file upload security
		$file_upload_validation = get_option( 'wc_product_addons_file_upload_validation', 'yes' );
		if ( $file_upload_validation === 'no' ) {
			$issues[] = 'file_upload_validation_disabled';
			$threat_level += 25;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of validation issues */
				__( 'WooCommerce Product Addons validation has problems: %s. This allows invalid data and incorrect pricing.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/woocommerce-product-addons-validation',
			);
		}
		
		return null;
	}
}
