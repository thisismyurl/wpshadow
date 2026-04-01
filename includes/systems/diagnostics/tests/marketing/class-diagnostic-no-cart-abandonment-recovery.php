<?php
/**
 * No Cart Abandonment Recovery Diagnostic
 *
 * Detects when cart abandonment recovery is not configured,
 * missing opportunity to recover 10-30% of abandoned sales.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Marketing
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Cart Abandonment Recovery
 *
 * Checks whether cart abandonment recovery
 * is configured for e-commerce sites.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Cart_Abandonment_Recovery extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-cart-abandonment-recovery';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Cart Abandonment Recovery';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether cart abandonment recovery is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Whether this diagnostic is auto-fixable
	 *
	 * @var bool
	 */
	protected static $auto_fixable = false;

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if WooCommerce is active
		if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			return null;
		}

		// Check for cart abandonment plugins
		$has_abandonment_recovery = is_plugin_active( 'woocommerce-abandoned-cart/woocommerce-ac.php' ) ||
			is_plugin_active( 'abandoned-cart-lite-for-woocommerce/woocommerce-abandoned-cart.php' ) ||
			is_plugin_active( 'cartflows/cartflows.php' );

		if ( ! $has_abandonment_recovery ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'Cart abandonment recovery isn\'t configured, which means you\'re losing 70% of potential sales. Average cart abandonment rate is 70%. Recovery works by: capturing email when users add to cart, sending automated reminders (1 hour, 24 hours, 3 days), offering small incentives (free shipping, 10% off). Well-designed recovery campaigns recover 10-30% of abandoned carts. For a store doing $10k/month, that\'s $2-3k in recovered revenue.',
					'wpshadow'
				),
				'severity'      => 'critical',
				'threat_level'  => 85,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Revenue Recovery',
					'potential_gain' => 'Recover 10-30% of abandoned sales',
					'roi_explanation' => 'Cart abandonment recovery converts 10-30% of the 70% who abandon, directly increasing revenue 7-21%.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/cart-abandonment-recovery?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
