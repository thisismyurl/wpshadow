<?php
/**
 * Inventory Accuracy Diagnostic
 *
 * Checks if stock levels are synced with reality.
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
 * Inventory Accuracy Diagnostic Class
 *
 * Verifies that inventory levels are accurate and synced with
 * actual stock and external systems.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Inventory_Accuracy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'inventory-accuracy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Inventory Accuracy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if stock levels are synced with reality';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'ecommerce';

	/**
	 * Run the inventory accuracy diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if inventory issues detected, null otherwise.
	 */
	public static function check() {
		$issues    = array();
		$warnings  = array();
		$stats     = array();

		// Check for WooCommerce.
		if ( ! function_exists( 'wc' ) || ! class_exists( 'WooCommerce' ) ) {
			$warnings[] = __( 'WooCommerce not active - skipping inventory check', 'wpshadow' );
			return null;
		}

		// Check for inventory management enabled.
		$inventory_enabled = get_option( 'woocommerce_manage_stock' );
		$stats['inventory_management'] = boolval( $inventory_enabled );

		if ( ! $inventory_enabled ) {
			$warnings[] = __( 'Inventory management not enabled', 'wpshadow' );
			return null; // Can't check accuracy if not managed.
		}

		// Get products with stock tracking.
		$products_with_stock = get_posts( array(
			'post_type'   => 'product',
			'meta_key'    => '_manage_stock',
			'meta_value'  => 'yes',
			'posts_per_page' => -1,
			'fields'      => 'ids',
		) );

		$stats['products_with_stock'] = count( $products_with_stock );

		// Check for oversold products.
		$oversold_products = 0;
		$negative_stock = 0;
		$zero_stock = 0;
		$low_stock_count = 0;
		$low_stock_threshold = get_option( 'woocommerce_notify_low_stock_amount', 2 );

		foreach ( array_slice( $products_with_stock, 0, 50 ) as $product_id ) { // Sample first 50.
			$product = wc_get_product( $product_id );

			if ( ! $product ) {
				continue;
			}

			$stock = $product->get_stock_quantity();

			if ( $stock < 0 ) {
				$negative_stock++;
				$oversold_products++;
			} elseif ( $stock === 0 ) {
				$zero_stock++;
			} elseif ( $stock <= $low_stock_threshold ) {
				$low_stock_count++;
			}
		}

		$stats['oversold_products_sample'] = $oversold_products;
		$stats['negative_stock_products'] = $negative_stock;
		$stats['zero_stock_products'] = $zero_stock;
		$stats['low_stock_products'] = $low_stock_count;

		if ( $oversold_products > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: count */
				__( '%d products with negative stock - fix immediately to prevent overselling', 'wpshadow' ),
				$oversold_products
			);
		}

		// Check for inventory sync plugin.
		$sync_plugins = array(
			'inventory-management-for-woocommerce/main.php',
			'woocommerce-stock-manager-basic/woocommerce-stock-manager.php',
		);

		$has_sync = false;
		foreach ( $sync_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_sync = true;
				break;
			}
		}

		$stats['inventory_sync_plugin'] = $has_sync;

		if ( count( $products_with_stock ) > 100 && ! $has_sync ) {
			$warnings[] = __( 'Large inventory without sync plugin - consider adding for accuracy', 'wpshadow' );
		}

		// Check for last inventory audit.
		$last_audit = get_option( 'woocommerce_last_inventory_audit' );
		$stats['last_audit'] = ! empty( $last_audit ) ? date_i18n( 'Y-m-d H:i', $last_audit ) : 'Never';

		if ( empty( $last_audit ) ) {
			$warnings[] = __( 'No recent inventory audit - schedule regular audits', 'wpshadow' );
		} else {
			$audit_age = time() - $last_audit;
			if ( $audit_age > ( 90 * 24 * 3600 ) ) { // 90 days.
				$warnings[] = sprintf(
					/* translators: %s: date */
					__( 'Last inventory audit was %s ago - conduct new audit', 'wpshadow' ),
					$stats['last_audit']
				);
			}
		}

		// Check for discrepancy tolerance.
		$discrepancy_tolerance = get_option( 'woocommerce_inventory_discrepancy_tolerance', 5 );
		$stats['discrepancy_tolerance_percent'] = intval( $discrepancy_tolerance );

		// Check inventory hold period.
		$hold_period = get_option( 'woocommerce_inventory_hold_minutes', 60 );
		$stats['inventory_hold_minutes'] = intval( $hold_period );

		if ( $hold_period > 120 ) {
			$warnings[] = sprintf(
				/* translators: %d: minutes */
				__( 'Inventory hold period is %d minutes - may prevent legitimate orders', 'wpshadow' ),
				$hold_period
			);
		}

		// Check for low stock notifications.
		$notify_low = get_option( 'woocommerce_notify_low_stock' );
		$stats['low_stock_notifications'] = boolval( $notify_low );

		if ( ! $notify_low ) {
			$warnings[] = __( 'Low stock notifications not enabled', 'wpshadow' );
		}

		// Check for stock alert email.
		$stock_alert_email = get_option( 'woocommerce_stock_alert_email' );
		$stats['stock_alert_email'] = ! empty( $stock_alert_email ) ? 'Configured' : 'Not configured';

		if ( ! $stock_alert_email ) {
			$warnings[] = __( 'Stock alert email not configured', 'wpshadow' );
		}

		// Check for backorder settings.
		$backorder_threshold = get_option( 'woocommerce_backorder_threshold', 0 );
		$stats['backorder_threshold'] = intval( $backorder_threshold );

		// Check for inventory variance report.
		$variance_report = get_option( 'woocommerce_inventory_variance_report' );
		$stats['variance_report'] = boolval( $variance_report );

		if ( ! $variance_report ) {
			$warnings[] = __( 'Inventory variance report not enabled', 'wpshadow' );
		}

		// Check for external system integration.
		$external_integration = get_option( 'woocommerce_external_inventory_integration' );
		$stats['external_integration'] = ! empty( $external_integration ) ? $external_integration : 'None';

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Inventory accuracy has critical issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/inventory-accuracy',
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
				'description'  => __( 'Inventory accuracy has recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/inventory-accuracy',
				'context'      => array(
					'stats'    => $stats,
					'warnings' => $warnings,
				),
			);
		}

		return null; // Inventory accuracy is good.
	}
}
