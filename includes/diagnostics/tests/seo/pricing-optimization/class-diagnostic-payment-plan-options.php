<?php
/**
 * Payment Plan Options Diagnostic
 *
 * Checks whether payment plans or financing options are available.
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
 * Payment Plan Options Diagnostic Class
 *
 * Verifies that financing or installment tools are present.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Payment_Plan_Options extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'payment-plan-options';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'No Payment Plan or Financing Options';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if financing or installment options are offered';

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

		$financing_plugins = array(
			'klarna-payments-for-woocommerce/klarna-payments-for-woocommerce.php' => 'Klarna Payments',
			'woocommerce-gateway-paypal-express-checkout/woocommerce-gateway-paypal-express-checkout.php' => 'PayPal',
			'woocommerce-payments/woocommerce-payments.php' => 'WooCommerce Payments',
			'afterpay-gateway-for-woocommerce/afterpay-gateway-for-woocommerce.php' => 'Afterpay',
			'affirm-payments/affirm-payments.php' => 'Affirm Payments',
		);

		$active_financing = array();
		foreach ( $financing_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_financing[] = $plugin_name;
			}
		}

		$stats['financing_tools'] = ! empty( $active_financing ) ? implode( ', ', $active_financing ) : 'none';

		$financing_pages = self::find_pages_by_keywords( array( 'payment plan', 'financing', 'installments', 'pay over time' ) );
		$stats['financing_pages'] = ! empty( $financing_pages ) ? implode( ', ', $financing_pages ) : 'none';

		if ( empty( $active_financing ) && empty( $financing_pages ) ) {
			$issues[] = __( 'No payment plan or financing option detected', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Payment plans make higher-priced items more accessible. Clear installment options help customers feel comfortable with the purchase.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/payment-plan-options',
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
