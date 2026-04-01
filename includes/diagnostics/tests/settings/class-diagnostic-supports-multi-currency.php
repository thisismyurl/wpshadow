<?php
/**
 * Multi-Currency Support Diagnostic
 *
 * Tests whether the site supports multiple currencies with auto-detection for international
 * users. Multi-currency support removes friction and increases conversion for global audiences.
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
 * Diagnostic_Supports_Multi_Currency Class
 *
 * Diagnostic #27: Multi-Currency Support from Specialized & Emerging Success Habits.
 * Checks if the site provides multiple currency options with automatic detection.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Supports_Multi_Currency extends Diagnostic_Base {

	protected static $slug = 'supports-multi-currency';
	protected static $title = 'Multi-Currency Support';
	protected static $description = 'Tests whether the site supports multiple currencies with auto-detection for international users';
	protected static $family = 'international-ecommerce';

	public static function check() {
		$score          = 0;
		$max_score      = 5;
		$score_details  = array();
		$recommendations = array();

		// Check multi-currency plugins.
		$currency_plugins = array(
			'woocommerce-multilingual/wpml-woocommerce.php',
			'woo-multi-currency/woo-multi-currency.php',
			'currency-switcher-woocommerce/currency-switcher-woocommerce.php',
			'woocommerce-payments/woocommerce-payments.php',
		);

		$has_currency_plugin = false;
		foreach ( $currency_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_currency_plugin = true;
				++$score;
				$score_details[] = __( '✓ Multi-currency plugin active', 'wpshadow' );
				break;
			}
		}

		if ( ! $has_currency_plugin ) {
			$score_details[]   = __( '✗ No multi-currency plugin detected', 'wpshadow' );
			$recommendations[] = __( 'Install WPML WooCommerce Multi-Currency or similar plugin to support multiple currencies', 'wpshadow' );
		}

		// Check multiple currency options configured.
		$currency_count = 0;
		$currency_codes = array( 'USD', 'EUR', 'GBP', 'CAD', 'AUD', 'JPY', 'CNY', 'INR', 'BRL', 'MXN' );

		$all_content = get_posts(
			array(
				'post_type'      => 'any',
				'posts_per_page' => 50,
				'post_status'    => 'publish',
			)
		);

		foreach ( $currency_codes as $currency_code ) {
			foreach ( $all_content as $content_item ) {
				$content_text = $content_item->post_content;
				if ( stripos( $content_text, $currency_code ) !== false || stripos( $content_text, strtolower( $currency_code ) ) !== false ) {
					++$currency_count;
					break;
				}
			}
		}

		if ( $currency_count >= 3 ) {
			$score += 2;
			$score_details[] = sprintf(
				/* translators: %d: number of currencies */
				__( '✓ %d+ currencies referenced in content', 'wpshadow' ),
				$currency_count
			);
		} elseif ( $currency_count > 1 ) {
			++$score;
			$score_details[]   = sprintf( __( '◐ %d currency references found', 'wpshadow' ), $currency_count );
			$recommendations[] = __( 'Support at least 3 major currencies (USD, EUR, GBP) for international appeal', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ Single currency site', 'wpshadow' );
			$recommendations[] = __( 'Add multi-currency support to serve international customers in their preferred currency', 'wpshadow' );
		}

		// Check currency switcher widget.
		global $wp_scripts;
		$has_switcher = false;
		if ( isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script ) {
				if ( stripos( $handle, 'currency' ) !== false ) {
					$has_switcher = true;
					break;
				}
			}
		}

		if ( $has_switcher || $has_currency_plugin ) {
			++$score;
			$score_details[] = __( '✓ Currency switcher available', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No currency switcher detected', 'wpshadow' );
			$recommendations[] = __( 'Add a currency switcher widget for easy currency selection', 'wpshadow' );
		}

		// Check auto-detection / GeoIP.
		$geoip_plugins = array(
			'geoip-detect/geoip-detect.php',
			'woocommerce/woocommerce.php', // WC has GeoIP
		);

		$has_geoip = false;
		foreach ( $geoip_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) && class_exists( 'WC_Geolocation' ) ) {
				$has_geoip = true;
				++$score;
				$score_details[] = __( '✓ GeoIP location detection available', 'wpshadow' );
				break;
			}
		}

		if ( ! $has_geoip ) {
			$score_details[]   = __( '✗ No automatic currency detection based on location', 'wpshadow' );
			$recommendations[] = __( 'Enable GeoIP detection to automatically show prices in visitor\'s local currency', 'wpshadow' );
		}

		$score_percentage = ( $score / $max_score ) * 100;

		if ( $score_percentage < 30 ) {
			$severity     = 'medium';
			$threat_level = 25;
		} elseif ( $score_percentage < 60 ) {
			$severity     = 'low';
			$threat_level = 15;
		} else {
			return null;
		}

		return array(
			'id'               => self::$slug,
			'title'            => self::$title,
			'description'      => sprintf(
				/* translators: %d: score percentage */
				__( 'Multi-currency score: %d%%. 92%% of customers prefer to see prices in their local currency. Multi-currency support increases conversion by 40%% and average order value by 25%%.', 'wpshadow' ),
				$score_percentage
			),
			'severity'         => $severity,
			'threat_level'     => $threat_level,
			'auto_fixable'     => false,
			'kb_link'          => 'https://wpshadow.com/kb/multi-currency-support?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'          => $score_details,
			'recommendations'  => $recommendations,
			'impact'           => __( 'Displaying prices in local currency eliminates confusion and builds trust with international customers.', 'wpshadow' ),
		);
	}
}
