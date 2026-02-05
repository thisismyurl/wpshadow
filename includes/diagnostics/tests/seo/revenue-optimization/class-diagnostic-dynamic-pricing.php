<?php
/**
 * Dynamic Pricing Diagnostic
 *
 * Checks whether dynamic pricing or personalization tools are available.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\RevenueOptimization
 * @since      1.6035.1400
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Dynamic Pricing Diagnostic Class
 *
 * Verifies dynamic pricing plugins or personalization tools.
 *
 * @since 1.6035.1400
 */
class Diagnostic_Dynamic_Pricing extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'dynamic-pricing';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'No Dynamic Pricing or Personalization';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if dynamic pricing or personalization tools are available';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'revenue-optimization';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.1400
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$stats  = array();

		$dynamic_plugins = array(
			'woocommerce-dynamic-pricing/woocommerce-dynamic-pricing.php' => 'WooCommerce Dynamic Pricing',
			'woo-dynamic-pricing-and-discounts/woo-dynamic-pricing-and-discounts.php' => 'Dynamic Pricing & Discounts',
			'woocommerce-advanced-pricing/woocommerce-advanced-pricing.php' => 'WooCommerce Advanced Pricing',
			'wootr/wootr.php' => 'WooCommerce Pricing Rules',
		);

		$active_dynamic = array();
		foreach ( $dynamic_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_dynamic[] = $plugin_name;
			}
		}

		$stats['dynamic_tools'] = ! empty( $active_dynamic ) ? implode( ', ', $active_dynamic ) : 'none';

		if ( empty( $active_dynamic ) ) {
			$issues[] = __( 'No dynamic pricing or personalization tools detected', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Dynamic pricing lets you offer tailored deals based on context or behavior. Used carefully, it can improve revenue while keeping offers relevant.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/dynamic-pricing',
				'context'      => array(
					'stats'  => $stats,
					'issues' => $issues,
				),
			);
		}

		return null;
	}
}
