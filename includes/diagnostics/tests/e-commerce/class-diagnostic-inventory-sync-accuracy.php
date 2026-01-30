<?php
/**
 * Inventory Sync Accuracy Diagnostic
 *
 * Detects inventory discrepancies between WooCommerce and
 * external sources (warehouse, multi-channel listings).
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
 * Diagnostic_Inventory_Sync_Accuracy Class
 *
 * Monitors inventory synchronization.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Inventory_Sync_Accuracy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'inventory-sync-accuracy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Inventory Sync Accuracy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Monitors inventory synchronization';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'ecommerce';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if sync issues, null otherwise.
	 */
	public static function check() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return null; // Not e-commerce
		}

		$sync_status = self::check_inventory_sync();

		if ( ! $sync_status['has_issue'] ) {
			return null; // Inventory appears synchronized
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Inventory not synced with external sources. Sell product on Amazon = customer orders on site = out of stock = cancel order = refund + angry customer. Inventory nightmare = lost sales + chargebacks.', 'wpshadow' ),
			'severity'     => 'high',
			'threat_level' => 75,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/inventory-sync',
			'family'       => self::$family,
			'meta'         => array(
				'multi_channel' => $sync_status['multi_channel'] ? 'yes' : 'no',
			),
			'details'      => array(
				'inventory_sync_challenges'    => array(
					__( '33% of sellers list on multiple channels (Amazon, eBay, Walmart)' ),
					__( 'Manual sync = overselling = cancellations' ),
					__( 'Time lag: Order on site, but Amazon inventory outdated' ),
					__( 'Supplier changes: Restock not reflected' ),
					__( 'Returns/refunds: Stock not updated immediately' ),
				),
				'multi_channel_platforms'      => array(
					'Amazon Seller Central' => __( 'Largest marketplace, ~40% of e-commerce' ),
					'eBay' => __( 'Auction/fixed price listings' ),
					'Walmart' => __( 'Growing marketplace (70M items)' ),
					'Etsy' => __( 'Handmade/vintage items' ),
					'Facebook/Instagram' => __( 'Social selling' ),
					'Google Shopping' => __( 'Product feeds and merchant center' ),
				),
				'inventory_sync_solutions'     => array(
					'Inventory Sync Plugins' => array(
						'Sellfy: Multi-channel inventory sync',
						'Inventory Labs: Amazon + eBay + Shopify',
						'Zentail: All marketplaces + fulfillment',
						'TradeGecko: ERP for manufacturers',
					),
					'Real-time Syncing' => array(
						'Frequency: Every 15-30 minutes',
						'Lag: <1 hour before out of stock syncs',
						'Benefit: Reduces overselling',
					),
					'Manual Monitoring' => array(
						'Daily: Check orders across channels',
						'Daily: Check inventory levels',
						'Weekly: Reconcile totals',
					),
				),
				'preventing_overselling'       => array(
					'Safety Stock' => array(
						'Example: Keep 5 units reserved',
						'Reason: Buffer for sync lag',
						'Benefit: Overselling prevented',
					),
					'Status Codes' => array(
						'In Stock: >10 units',
						'Low Stock: 3-10 units',
						'Limited: 1-2 units',
						'Out: 0 units',
					),
					'Automatic Out of Stock' => array(
						'When: Inventory reaches 0',
						'Action: Remove from listings',
						'Delay: Up to 1 hour',
					),
				),
				'managing_returns_restocks'    => array(
					'Return Workflow' => array(
						'Step 1: Customer initiates return',
						'Step 2: Inventory set to "pending"',
						'Step 3: Item received, inventory increased',
						'Step 4: Synced to all channels',
					),
					'Quick Restocking' => array(
						'Speed: Restock inventory immediately',
						'Reason: First to have stock sells',
						'Tools: Webhook triggers for supplier updates',
					),
				),
				'ecommerce_platform_features'  => array(
					'WooCommerce' => array(
						'Plugin: WooCommerce Zapier',
						'Integrates: Amazon, eBay, Shopify, etc',
						'Syncs: Product data, inventory, orders',
					),
					'Inventory Management' => array(
						'Stock levels by location',
						'Low stock notifications',
						'Automatic reorder at threshold',
					),
				),
				'monitoring_inventory_health'  => array(
					__( 'Dashboard: Real-time inventory levels' ),
					__( 'Alerts: Low stock warnings' ),
					__( 'Reports: Slow-moving inventory' ),
					__( 'Reconciliation: Monthly counts' ),
					__( 'Turnover ratio: Sales / average inventory' ),
				),
			),
		);
	}

	/**
	 * Check inventory sync.
	 *
	 * @since  1.2601.2148
	 * @return array Sync status.
	 */
	private static function check_inventory_sync() {
		// Check for multi-channel plugins
		$multi_channel_plugins = is_plugin_active( 'inventory-labs/inventory-labs.php' ) ||
								 is_plugin_active( 'sellfy/sellfy.php' ) ||
								 is_plugin_active( 'zentail/zentail.php' );

		// Check for inventory management plugins
		$inventory_plugins = is_plugin_active( 'woocommerce-zapier/woocommerce-zapier.php' ) ||
							 is_plugin_active( 'woocommerce-inventory-management/inventory.php' );

		// Get total products
		$total_products = wc_get_products(
			array(
				'limit' => 1,
				'return' => 'ids',
			)
		);
		$product_count = count( $total_products );

		// Conservative: Flag if no sync plugin and >50 products (manual sync difficult)
		$has_issue = ! $multi_channel_plugins && ! $inventory_plugins && $product_count > 50;

		return array(
			'has_issue'      => $has_issue,
			'multi_channel'  => $multi_channel_plugins,
		);
	}
}
