<?php
/**
 * WooCommerce Performance
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5029.1800
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diagnostic_WooCommerce_Performance extends Diagnostic_Base {

	protected static $slug        = 'woocommerce-performance';
	protected static $title       = 'WooCommerce Performance';
	protected static $description = 'Checks WooCommerce caching settings';
	protected static $family      = 'performance';

	public static function check() {
		$cache_key = 'wpshadow_wc_performance';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		if ( ! class_exists( 'WooCommerce' ) ) {
			set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
			return null;
		}

		$issues = array();

		// Check object caching.
		if ( ! wp_using_ext_object_cache() ) {
			$issues[] = 'No external object cache detected';
		}

		// Check cart fragments (major performance issue).
		if ( 'yes' === get_option( 'woocommerce_enable_ajax_add_to_cart', 'yes' ) ) {
			$issues[] = 'AJAX add to cart enabled (causes cart fragment issues)';
		}

		if ( ! empty( $issues ) ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'WooCommerce performance optimization needed.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/woocommerce-performance',
				'data'         => array(
					'issues' => $issues,
				),
			);

			set_transient( $cache_key, $result, 12 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}
}
