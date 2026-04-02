<?php
/**
 * Volume Discount Diagnostic
 *
 * Checks whether volume or wholesale pricing options are available.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\PricingOptimization
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Volume Discount Diagnostic Class
 *
 * Verifies that bulk or wholesale pricing options exist.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Volume_Discount extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'volume-discount';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'No Volume or Wholesale Discount Structure';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if volume discounts or wholesale pricing are available';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'pricing-optimization';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$stats  = array();

		$wholesale_plugins = array(
			'woocommerce-wholesale-prices/woocommerce-wholesale-prices.bootstrap.php' => 'WooCommerce Wholesale Prices',
			'woocommerce-wholesale/woocommerce-wholesale.php' => 'WooCommerce Wholesale',
			'b2b-market/b2b-market.php' => 'B2B Market',
			'wholesale-suite/wholesale-suite.php' => 'Wholesale Suite',
		);

		$active_wholesale = array();
		foreach ( $wholesale_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_wholesale[] = $plugin_name;
			}
		}

		$stats['wholesale_tools'] = ! empty( $active_wholesale ) ? implode( ', ', $active_wholesale ) : 'none';

		$wholesale_pages = self::find_pages_by_keywords( array( 'wholesale', 'bulk pricing', 'volume discount', 'b2b' ) );
		$stats['wholesale_pages'] = ! empty( $wholesale_pages ) ? implode( ', ', $wholesale_pages ) : 'none';

		if ( empty( $active_wholesale ) && empty( $wholesale_pages ) ) {
			$issues[] = __( 'No wholesale or bulk pricing option detected', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Bulk buyers appreciate clear volume pricing. A simple wholesale tier can attract larger orders and repeat business.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/volume-discount',
				'context'      => array(
					'stats'  => $stats,
					'issues' => $issues,
				),
			);
		}

		return null;
	}

	/**
	 * Find pages or posts by keyword search.
	 *
	 * @since 1.6093.1200
	 * @param  array $keywords Keywords to search for.
	 * @return array List of matching page titles.
	 */
	private static function find_pages_by_keywords( array $keywords ): array {
		$matches = array();

		foreach ( $keywords as $keyword ) {
			$results = get_posts(
				array(
					's'              => $keyword,
					'post_type'      => array( 'page', 'post' ),
					'post_status'    => 'publish',
					'posts_per_page' => 5,
				)
			);

			foreach ( $results as $post ) {
				$matches[ $post->ID ] = get_the_title( $post );
			}
		}

		return array_values( $matches );
	}
}
