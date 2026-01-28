<?php
/**
 * Currency Conversion Not Available Diagnostic
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diagnostic_Currency_Conversion_Missing extends Diagnostic_Base {

	protected static $slug        = 'currency-conversion-missing';
	protected static $title       = 'Currency Conversion Not Available';
	protected static $description = 'Detects missing multi-currency support for international customers';
	protected static $family      = 'ecommerce';

	public static function check() {
		$cached = get_transient( 'wpshadow_diagnostic_currency' );
		if ( false !== $cached ) {
			return $cached;
		}

		if ( ! class_exists( 'WooCommerce' ) ) {
			set_transient( 'wpshadow_diagnostic_currency', null, 24 * HOUR_IN_SECONDS );
			return null;
		}

		$has_multi_currency = self::check_multi_currency_support();
		if ( $has_multi_currency ) {
			set_transient( 'wpshadow_diagnostic_currency', null, 24 * HOUR_IN_SECONDS );
			return null;
		}

		$finding = array(
			'id'             => self::$slug,
			'title'          => self::$title,
			'description'    => __( 'Site lacks multi-currency support, limiting international sales potential', 'wpshadow' ),
			'severity'       => 'medium',
			'threat_level'   => 55,
			'auto_fixable'   => false,
			'kb_link'        => 'https://wpshadow.com/kb/multi-currency',
			'details'        => array( __( 'Only single currency available for checkout', 'wpshadow' ) ),
			'recommendations' => array(
				__( 'Install WooCommerce Multi-Currency plugin', 'wpshadow' ),
				__( 'Implement geolocation-based currency detection', 'wpshadow' ),
			),
		);

		set_transient( 'wpshadow_diagnostic_currency', $finding, 24 * HOUR_IN_SECONDS );
		return $finding;
	}

	private static function check_multi_currency_support() {
		return class_exists( 'WOOMC\MultiCurrency' ) || 
		       function_exists( 'alg_wc_currency_switcher' ) ||
		       class_exists( 'WCML\MultiCurrency\MultiCurrency' );
	}
}
