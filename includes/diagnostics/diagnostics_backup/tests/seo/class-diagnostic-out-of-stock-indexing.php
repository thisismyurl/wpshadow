<?php
/**
 * Out-of-Stock Product Indexing Diagnostic
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diagnostic_Out_Of_Stock_Indexing extends Diagnostic_Base {

	protected static $slug        = 'out-of-stock-indexing';
	protected static $title       = 'Out-of-Stock Product Indexing';
	protected static $description = 'Detects indexed out-of-stock products wasting crawl budget';
	protected static $family      = 'seo';

	public static function check() {
		$cached = get_transient( 'wpshadow_diagnostic_oos_indexing' );
		if ( false !== $cached ) {
			return $cached;
		}

		if ( ! class_exists( 'WooCommerce' ) ) {
			set_transient( 'wpshadow_diagnostic_oos_indexing', null, 24 * HOUR_IN_SECONDS );
			return null;
		}

		$oos_data = self::check_oos_products();
		if ( $oos_data['indexed_oos_count'] === 0 ) {
			set_transient( 'wpshadow_diagnostic_oos_indexing', null, 24 * HOUR_IN_SECONDS );
			return null;
		}

		$finding = array(
			'id'             => self::$slug,
			'title'          => self::$title,
			'description'    => sprintf( __( '%d out-of-stock products are indexed, wasting crawl budget', 'wpshadow' ), $oos_data['indexed_oos_count'] ),
			'severity'       => 'medium',
			'threat_level'   => min( 65, 40 + $oos_data['indexed_oos_count'] ),
			'auto_fixable'   => false,
			'kb_link'        => 'https://wpshadow.com/kb/oos-indexing',
			'meta'           => $oos_data,
			'details'        => array( __( 'Out-of-stock products should use noindex to preserve crawl budget', 'wpshadow' ) ),
			'recommendations' => array(
				__( 'Add noindex meta tag to out-of-stock products', 'wpshadow' ),
				__( 'Use Yoast SEO or RankMath to automate noindex rules', 'wpshadow' ),
			),
		);

		set_transient( 'wpshadow_diagnostic_oos_indexing', $finding, 24 * HOUR_IN_SECONDS );
		return $finding;
	}

	private static function check_oos_products() {
		global $wpdb;

		$oos_count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(p.ID) FROM {$wpdb->posts} p
				INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
				WHERE p.post_type = %s 
				AND p.post_status = 'publish'
				AND pm.meta_key = '_stock_status'
				AND pm.meta_value = 'outofstock'",
				'product'
			)
		);

		$indexed_oos_count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(p.ID) FROM {$wpdb->posts} p
				INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
				LEFT JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_yoast_wpseo_meta-robots-noindex'
				WHERE p.post_type = %s 
				AND p.post_status = 'publish'
				AND pm.meta_key = '_stock_status'
				AND pm.meta_value = 'outofstock'
				AND (pm2.meta_value IS NULL OR pm2.meta_value != '1')",
				'product'
			)
		);

		return array(
			'total_oos'        => $oos_count,
			'indexed_oos_count' => $indexed_oos_count,
		);
	}
}
