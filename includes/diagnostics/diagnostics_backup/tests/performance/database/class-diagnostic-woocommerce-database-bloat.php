<?php
/**
 * WooCommerce Database Bloat Diagnostic
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5029.1730
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diagnostic_WooCommerce_Database_Bloat extends Diagnostic_Base {

	protected static $slug        = 'woocommerce-database-bloat';
	protected static $title       = 'WooCommerce Database Bloat';
	protected static $description = 'Detects database bloat from old orders';
	protected static $family      = 'database';

	public static function check() {
		$cache_key = 'wpshadow_wc_db_bloat';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		if ( ! class_exists( 'WooCommerce' ) ) {
			set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
			return null;
		}

		global $wpdb;

		$trash_orders = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} 
			WHERE post_type = 'shop_order' AND post_status = 'trash'"
		);

		$orphaned_meta = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta} pm
			LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID
			WHERE p.ID IS NULL AND pm.meta_key LIKE '_order_%'"
		);

		$one_year_ago = date( 'Y-m-d H:i:s', strtotime( '-1 year' ) );
		$old_orders = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->posts}
			WHERE post_type = 'shop_order'
			AND post_status IN ('wc-completed', 'wc-cancelled')
			AND post_date < %s",
			$one_year_ago
		) );

		$is_bloated = $trash_orders > 100 || $orphaned_meta > 500 || $old_orders > 1000;

		if ( $is_bloated ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'WooCommerce database has significant bloat. Clean up for better performance.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/database-woocommerce-bloat',
				'data'         => array(
					'trash_orders' => (int) $trash_orders,
					'orphaned_meta' => (int) $orphaned_meta,
					'old_orders' => (int) $old_orders,
				),
			);

			set_transient( $cache_key, $result, 12 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}
}
