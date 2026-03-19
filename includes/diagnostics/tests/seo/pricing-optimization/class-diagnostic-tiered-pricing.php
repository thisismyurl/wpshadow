<?php
/**
 * Tiered Pricing Diagnostic
 *
 * Checks whether tiered pricing or plan options are present.
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
 * Tiered Pricing Diagnostic Class
 *
 * Verifies that multiple pricing tiers exist.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Tiered_Pricing extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'tiered-pricing';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'No Tiered Pricing or Upsell Ladder';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if multiple pricing tiers or plans are available';

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

		$pricing_pages = self::find_pages_by_keywords( array( 'pricing', 'plans', 'tiers', 'packages' ) );
		$stats['pricing_pages'] = ! empty( $pricing_pages ) ? implode( ', ', $pricing_pages ) : 'none';

		$tier_plugins = array(
			'woocommerce-subscriptions/woocommerce-subscriptions.php' => 'WooCommerce Subscriptions',
			'pricing-table/price-table.php' => 'Pricing Table',
			'elementor-pro/elementor-pro.php' => 'Elementor Pro',
		);

		$active_tiers = array();
		foreach ( $tier_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_tiers[] = $plugin_name;
			}
		}

		$stats['tier_tools'] = ! empty( $active_tiers ) ? implode( ', ', $active_tiers ) : 'none';

		if ( empty( $pricing_pages ) && empty( $active_tiers ) ) {
			$issues[] = __( 'No pricing tiers or plan options detected', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Offering multiple pricing options lets people choose a plan that fits their needs. It also creates a natural upgrade path.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/tiered-pricing',
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
