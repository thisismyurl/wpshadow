<?php
/**
 * WooCommerce Order Status Customization Issue Diagnostic
 *
 * Checks if order statuses are properly configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2310
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WooCommerce Order Status Customization Issue Diagnostic Class
 *
 * Detects order status configuration problems.
 *
 * @since 1.2601.2310
 */
class Diagnostic_WooCommerce_Order_Status_Customization_Issue extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'woocommerce-order-status-customization-issue';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'WooCommerce Order Status Customization Issue';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if order statuses are configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'plugins';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if WooCommerce is active
		if ( ! function_exists( 'wc_get_order_statuses' ) ) {
			return null;
		}

		// Get order statuses
		$order_statuses = wc_get_order_statuses();

		// Check if critical statuses exist
		$critical_statuses = array( 'wc-pending', 'wc-processing', 'wc-completed' );
		$missing_statuses  = array();

		foreach ( $critical_statuses as $status ) {
			if ( ! isset( $order_statuses[ $status ] ) ) {
				$missing_statuses[] = $status;
			}
		}

		if ( ! empty( $missing_statuses ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					__( 'Critical order statuses are missing: %s. Order management will not work properly.', 'wpshadow' ),
					implode( ', ', $missing_statuses )
				),
				'severity'      => 'high',
				'threat_level'  => 70,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/woocommerce-order-status-customization-issue',
			);
		}

		return null;
	}
}
