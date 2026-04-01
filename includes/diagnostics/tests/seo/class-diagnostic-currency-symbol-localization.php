<?php
/**
 * Currency Symbol Localization Diagnostic
 *
 * Tests if currency symbols and prices are properly localized for e-commerce sites.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Currency Symbol Localization Diagnostic Class
 *
 * Validates that e-commerce sites properly format currency according
 * to locale conventions for international customers.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Currency_Symbol_Localization extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'currency-symbol-localization';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Currency Symbol Localization';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if currency symbols and prices are properly localized for e-commerce sites';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'internationalization';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests currency localization including symbol position, decimal
	 * separators, and multi-currency support.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		// Check if WooCommerce is active.
		if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			return null; // Not an e-commerce site.
		}

		// Get WooCommerce currency settings.
		$currency = get_woocommerce_currency();
		$currency_symbol = get_woocommerce_currency_symbol();
		$currency_pos = get_option( 'woocommerce_currency_pos', 'left' );
		$price_thousand_sep = get_option( 'woocommerce_price_thousand_sep', ',' );
		$price_decimal_sep = get_option( 'woocommerce_price_decimal_sep', '.' );
		$price_num_decimals = get_option( 'woocommerce_price_num_decimals', 2 );

		// Get site locale.
		$site_locale = get_locale();

		// Check for multi-currency plugin.
		$multicurrency_plugins = array(
			'woocommerce-multilingual/wpml-woocommerce.php' => 'WPML WooCommerce',
			'woocommerce-multicurrency/woocommerce-multicurrency.php' => 'WooCommerce Multi-Currency',
			'currency-switcher-woocommerce/currency-switcher-woocommerce.php' => 'WOOCS',
			'woo-multi-currency/woo-multi-currency.php' => 'Multi Currency for WooCommerce',
		);

		$active_multicurrency_plugin = null;
		foreach ( $multicurrency_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_multicurrency_plugin = $name;
				break;
			}
		}

		// Check if currency matches locale expectations.
		$locale_currency_map = array(
			'en_US' => 'USD',
			'en_GB' => 'GBP',
			'fr_FR' => 'EUR',
			'de_DE' => 'EUR',
			'es_ES' => 'EUR',
			'it_IT' => 'EUR',
			'ja'    => 'JPY',
			'zh_CN' => 'CNY',
			'ru_RU' => 'RUB',
			'pt_BR' => 'BRL',
		);

		$expected_currency = isset( $locale_currency_map[ $site_locale ] ) ? $locale_currency_map[ $site_locale ] : null;
		$currency_matches_locale = ( $expected_currency === null || $currency === $expected_currency );

		// Check if decimal/thousand separators match locale.
		$locale_separator_map = array(
			'en_US' => array( 'thousand' => ',', 'decimal' => '.' ),
			'en_GB' => array( 'thousand' => ',', 'decimal' => '.' ),
			'fr_FR' => array( 'thousand' => ' ', 'decimal' => ',' ),
			'de_DE' => array( 'thousand' => '.', 'decimal' => ',' ),
			'es_ES' => array( 'thousand' => '.', 'decimal' => ',' ),
			'it_IT' => array( 'thousand' => '.', 'decimal' => ',' ),
		);

		$expected_separators = isset( $locale_separator_map[ $site_locale ] ) ? $locale_separator_map[ $site_locale ] : null;
		$separators_match_locale = true;

		if ( $expected_separators !== null ) {
			$separators_match_locale = ( $price_thousand_sep === $expected_separators['thousand'] ) &&
									 ( $price_decimal_sep === $expected_separators['decimal'] );
		}

		// Check currency position for locale.
		$locale_position_map = array(
			'en_US' => 'left',
			'en_GB' => 'left',
			'fr_FR' => 'right_space',
			'de_DE' => 'right_space',
			'es_ES' => 'right_space',
		);

		$expected_position = isset( $locale_position_map[ $site_locale ] ) ? $locale_position_map[ $site_locale ] : null;
		$position_matches_locale = ( $expected_position === null || $currency_pos === $expected_position );

		// Check for currency switcher visibility.
		$has_currency_switcher = false;
		if ( $active_multicurrency_plugin ) {
			// Check header for currency switcher.
			$header_file = get_template_directory() . '/header.php';
			if ( file_exists( $header_file ) ) {
				$header_content = file_get_contents( $header_file );
				$has_currency_switcher = ( strpos( $header_content, 'currency' ) !== false ) ||
									   ( strpos( $header_content, 'wcml_currency_switcher' ) !== false );
			}
		}

		// Check if prices use correct number of decimals.
		$uses_correct_decimals = true;
		if ( $currency === 'JPY' && $price_num_decimals !== 0 ) {
			$uses_correct_decimals = false; // Japanese Yen has no decimals.
		}

		// Check theme for hardcoded currency symbols.
		$theme_dir = get_template_directory();
		$hardcoded_currency = false;
		$theme_files = array( 'woocommerce.php', 'woocommerce/single-product.php', 'woocommerce/archive-product.php' );

		foreach ( $theme_files as $file ) {
			$filepath = $theme_dir . '/' . $file;
			if ( file_exists( $filepath ) ) {
				$content = file_get_contents( $filepath );
				// Look for hardcoded $ or €.
				if ( preg_match( '/["\'][\$€£¥]["\']/', $content ) ) {
					$hardcoded_currency = true;
					break;
				}
			}
		}

		// Check for issues.
		$issues = array();

		// Issue 1: Currency doesn't match locale.
		if ( ! $currency_matches_locale && $expected_currency !== null ) {
			$issues[] = array(
				'type'        => 'currency_locale_mismatch',
				'description' => sprintf(
					/* translators: 1: current currency, 2: expected currency */
					__( 'Site uses %1$s but locale suggests %2$s; may confuse local customers', 'wpshadow' ),
					$currency,
					$expected_currency
				),
			);
		}

		// Issue 2: Decimal/thousand separators don't match locale.
		if ( ! $separators_match_locale && $expected_separators !== null ) {
			$issues[] = array(
				'type'        => 'separator_locale_mismatch',
				'description' => __( 'Price separators do not match locale conventions; may be misread', 'wpshadow' ),
			);
		}

		// Issue 3: Currency position doesn't match locale.
		if ( ! $position_matches_locale && $expected_position !== null ) {
			$issues[] = array(
				'type'        => 'position_locale_mismatch',
				'description' => __( 'Currency symbol position does not match locale conventions', 'wpshadow' ),
			);
		}

		// Issue 4: Multi-currency plugin but no visible switcher.
		if ( $active_multicurrency_plugin && ! $has_currency_switcher ) {
			$issues[] = array(
				'type'        => 'no_currency_switcher',
				'description' => sprintf(
					/* translators: %s: plugin name */
					__( '%s is active but no currency switcher visible to customers', 'wpshadow' ),
					$active_multicurrency_plugin
				),
			);
		}

		// Issue 5: Incorrect decimal places for currency.
		if ( ! $uses_correct_decimals ) {
			$issues[] = array(
				'type'        => 'incorrect_decimals',
				'description' => sprintf(
					/* translators: %s: currency code */
					__( '%s should not use decimal places; currently showing cents', 'wpshadow' ),
					$currency
				),
			);
		}

		// Issue 6: Hardcoded currency symbols in theme.
		if ( $hardcoded_currency ) {
			$issues[] = array(
				'type'        => 'hardcoded_currency',
				'description' => __( 'Currency symbols hardcoded in theme; not using WooCommerce currency settings', 'wpshadow' ),
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Currency formatting does not match locale conventions, causing confusion for international customers', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/currency-symbol-localization?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'currency'                => $currency,
					'currency_symbol'         => $currency_symbol,
					'currency_pos'            => $currency_pos,
					'price_thousand_sep'      => $price_thousand_sep,
					'price_decimal_sep'       => $price_decimal_sep,
					'price_num_decimals'      => $price_num_decimals,
					'site_locale'             => $site_locale,
					'expected_currency'       => $expected_currency,
					'currency_matches_locale' => $currency_matches_locale,
					'expected_separators'     => $expected_separators,
					'separators_match_locale' => $separators_match_locale,
					'expected_position'       => $expected_position,
					'position_matches_locale' => $position_matches_locale,
					'active_multicurrency_plugin' => $active_multicurrency_plugin,
					'has_currency_switcher'   => $has_currency_switcher,
					'uses_correct_decimals'   => $uses_correct_decimals,
					'hardcoded_currency'      => $hardcoded_currency,
					'issues_detected'         => $issues,
					'recommendation'          => __( 'Match currency to locale, use correct separators and position, add currency switcher if multi-currency', 'wpshadow' ),
					'currency_formats'        => array(
						'US/UK'      => '$1,234.56 (symbol left, comma thousand, period decimal)',
						'France'     => '1 234,56 € (symbol right with space, space thousand, comma decimal)',
						'Germany'    => '1.234,56 € (symbol right, period thousand, comma decimal)',
						'Japan'      => '¥1,234 (symbol left, no decimals)',
					),
					'currency_position_options' => array(
						'left'        => '$99.99',
						'right'       => '99.99$',
						'left_space'  => '$ 99.99',
						'right_space' => '99.99 $',
					),
					'zero_decimal_currencies' => array( 'JPY', 'KRW', 'VND', 'CLP', 'ISK' ),
					'multicurrency_benefits'  => 'Increases conversion by 20-40% for international customers',
				),
			);
		}

		return null;
	}
}
