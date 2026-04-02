<?php
/**
 * Currency Localization Diagnostic
 *
 * Checks if currency localization is properly configured for international markets.
 *
 * @package WPShadow\Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Currency Localization
 *
 * Detects whether currency handling respects different regions and locales.
 */
class Diagnostic_Currency_Localization extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'currency-localization';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Currency Localization';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for currency localization configuration';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'internationalization';

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Finding array if issues detected, null otherwise
	 */
	public static function check() {
		$issues  = array();
		$stats   = array();
		$plugins = array(
			'multi-currency-for-woocommerce/multi-currency.php' => 'Multi Currency for WooCommerce',
			'currency-switcher/currency-switcher.php'           => 'Currency Switcher',
			'woocommerce-multilingual/wpml-woocommerce.php'    => 'WPML WooCommerce',
			'product-currency-exchange/exchange.php'            => 'Product Currency Exchange',
		);

		$active = array();
		foreach ( $plugins as $file => $name ) {
			if ( is_plugin_active( $file ) ) {
				$active[] = $name;
			}
		}

		$stats['active_currency_tools']  = count( $active );
		$stats['currency_plugins_found'] = $active;

		// Check WooCommerce currency settings
		$woo_currency = get_option( 'woocommerce_currency', '' );
		$stats['woocommerce_active']     = is_plugin_active( 'woocommerce/woocommerce.php' );
		$stats['woocommerce_currency']   = $woo_currency;

		if ( is_plugin_active( 'woocommerce/woocommerce.php' ) && empty( $active ) ) {
			$issues[] = __( 'WooCommerce active but no multi-currency support detected', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Showing prices in local currencies removes friction for international customers. Multi-currency support increases conversion rates from foreign markets and provides a professional, globally-aware shopping experience.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 50,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/currency-localization',
				'context'       => array(
					'stats'  => $stats,
					'issues' => $issues,
				),
			);
		}

		return null;
	}
}
