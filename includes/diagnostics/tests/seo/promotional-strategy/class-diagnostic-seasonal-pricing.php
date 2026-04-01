<?php
/**
 * Seasonal Pricing Diagnostic
 *
 * Checks whether seasonal or promotional pricing tools are available.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\PromotionalStrategy
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Seasonal Pricing Diagnostic Class
 *
 * Verifies that promotional pricing tools or promo code systems exist.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Seasonal_Pricing extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'seasonal-pricing';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'No Seasonal or Promotional Pricing Strategy';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if promotional pricing tools or coupons are available';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'promotional-strategy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$stats  = array();

		$promo_plugins = array(
			'woocommerce/woocommerce.php' => 'WooCommerce',
			'advanced-coupons-for-woocommerce/advanced-coupons-for-woocommerce.php' => 'Advanced Coupons',
			'smart-coupons-for-woocommerce/woocommerce-smart-coupons.php' => 'Smart Coupons',
		);

		$active_promo = array();
		foreach ( $promo_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_promo[] = $plugin_name;
			}
		}

		$stats['promo_tools'] = ! empty( $active_promo ) ? implode( ', ', $active_promo ) : 'none';
		$promo_pages = self::find_pages_by_keywords( array( 'sale', 'promo', 'holiday', 'discount', 'black friday' ) );
		$stats['promo_pages'] = ! empty( $promo_pages ) ? implode( ', ', $promo_pages ) : 'none';

		if ( empty( $active_promo ) && empty( $promo_pages ) ) {
			$issues[] = __( 'No promotional pricing tools or seasonal offers detected', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Seasonal promotions help customers plan purchases and can create healthy revenue spikes. A simple promo system makes this easier.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/seasonal-pricing?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
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
	 * @since 0.6093.1200
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
