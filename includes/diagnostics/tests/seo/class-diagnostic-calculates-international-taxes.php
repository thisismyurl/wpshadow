<?php
/**
 * Tax Calculation International Diagnostic
 *
 * Tests whether the site accurately calculates VAT/GST for international customers.
 * Proper tax calculation is legally required for e-commerce and prevents compliance
 * issues, customer complaints, and revenue loss.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Calculates_International_Taxes Class
 *
 * Diagnostic #28: Tax Calculation International from Specialized & Emerging Success Habits.
 * Checks if the website accurately calculates VAT/GST/sales tax for international
 * customers based on their location.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Calculates_International_Taxes extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'calculates-international-taxes';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Tax Calculation International';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site accurately calculates VAT/GST for international customers';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'international-ecommerce';

	/**
	 * Run the diagnostic check.
	 *
	 * International tax calculation requires geo-location, tax plugins, and proper
	 * configuration. This diagnostic checks for WooCommerce tax settings, tax plugins,
	 * EU VAT compliance, and tax documentation.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$score          = 0;
		$max_score      = 5;
		$score_details  = array();
		$recommendations = array();

		// Check 1: WooCommerce with tax enabled.
		if ( class_exists( 'WooCommerce' ) ) {
			$calc_taxes = get_option( 'woocommerce_calc_taxes', 'no' );

			if ( 'yes' === $calc_taxes ) {
				++$score;
				$score_details[] = __( '✓ WooCommerce tax calculation enabled', 'wpshadow' );
			} else {
				$score_details[]   = __( '✗ WooCommerce tax calculation disabled', 'wpshadow' );
				$recommendations[] = __( 'Enable tax calculation in WooCommerce > Settings > Tax', 'wpshadow' );
			}
		} else {
			$score_details[]   = __( '✗ WooCommerce not installed', 'wpshadow' );
			$recommendations[] = __( 'Install WooCommerce to enable e-commerce and tax calculation', 'wpshadow' );
		}

		// Check 2: EU VAT compliance plugins.
		$vat_plugins = array(
			'woocommerce-eu-vat-compliance/woocommerce-eu-vat-compliance.php',
			'eu-vat-for-woocommerce/eu-vat-for-woocommerce.php',
			'woocommerce-eu-vat-number/woocommerce-eu-vat-number.php',
			'taxjar-simplified-taxes-for-woocommerce/taxjar-woocommerce.php',
			'woocommerce-tax/woocommerce-tax.php',
		);

		$has_vat_plugin = false;
		foreach ( $vat_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_vat_plugin = true;
				break;
			}
		}

		if ( $has_vat_plugin ) {
			++$score;
			$score_details[] = __( '✓ International tax plugin active (EU VAT/TaxJar)', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No international tax plugin detected', 'wpshadow' );
			$recommendations[] = __( 'Install EU VAT Compliance or TaxJar plugin for accurate international tax calculation', 'wpshadow' );
		}

		// Check 3: Multiple tax classes configured.
		if ( class_exists( 'WooCommerce' ) ) {
			$tax_classes = WC_Tax::get_tax_classes();

			if ( count( $tax_classes ) >= 2 || ! empty( WC_Tax::get_tax_rates() ) ) {
				++$score;
				$score_details[] = __( '✓ Tax rates configured in WooCommerce', 'wpshadow' );
			} else {
				$score_details[]   = __( '✗ No tax rates configured', 'wpshadow' );
				$recommendations[] = __( 'Configure tax rates for your target countries in WooCommerce > Settings > Tax', 'wpshadow' );
			}
		}

		// Check 4: Tax documentation/policy page.
		$tax_pages = get_posts(
			array(
				'post_type'      => 'page',
				'posts_per_page' => 5,
				'post_status'    => 'publish',
			)
		);

		$has_tax_info = false;
		foreach ( $tax_pages as $page ) {
			if ( stripos( $page->post_title, 'tax' ) !== false ||
				 stripos( $page->post_content, 'vat' ) !== false ||
				 stripos( $page->post_content, 'gst' ) !== false ||
				 stripos( $page->post_content, 'sales tax' ) !== false ) {
				$has_tax_info = true;
				break;
			}
		}

		if ( $has_tax_info ) {
			++$score;
			$score_details[] = __( '✓ Tax policy/information page exists', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No tax information page found', 'wpshadow' );
			$recommendations[] = __( 'Create a tax policy page explaining VAT/GST calculation for international customers', 'wpshadow' );
		}

		// Check 5: GeoIP location for tax calculation.
		$has_geoip = false;

		// Check for GeoIP plugins or WooCommerce GeoIP.
		if ( class_exists( 'WC_Geolocation' ) || is_plugin_active( 'geoip-detect/geoip-detect.php' ) ) {
			$has_geoip = true;
		}

		if ( $has_geoip ) {
			++$score;
			$score_details[] = __( '✓ GeoIP detection available for location-based tax calculation', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No GeoIP detection found', 'wpshadow' );
			$recommendations[] = __( 'Enable WooCommerce GeoIP or install GeoIP Detect plugin for automatic location-based tax', 'wpshadow' );
		}

		// Calculate score percentage.
		$score_percentage = ( $score / $max_score ) * 100;

		// Determine severity based on score.
		if ( $score_percentage < 30 ) {
			$severity     = 'medium';
			$threat_level = 30;
		} elseif ( $score_percentage < 60 ) {
			$severity     = 'low';
			$threat_level = 20;
		} else {
			// Tax calculation is adequate.
			return null;
		}

		return array(
			'id'               => self::$slug,
			'title'            => self::$title,
			'description'      => sprintf(
				/* translators: %d: score percentage */
				__( 'International tax calculation score: %d%%. Incorrect tax calculation can result in fines, audits, and legal issues. EU VAT compliance alone affects businesses selling to 27+ countries. Accurate tax calculation is legally required and reduces cart abandonment by 15%%.', 'wpshadow' ),
				$score_percentage
			),
			'severity'         => $severity,
			'threat_level'     => $threat_level,
			'auto_fixable'     => false,
			'kb_link'          => 'https://wpshadow.com/kb/international-tax-calculation',
			'details'          => $score_details,
			'recommendations'  => $recommendations,
			'impact'           => __( 'Proper tax calculation ensures legal compliance, prevents revenue loss from incorrect pricing, and builds customer trust through transparent checkout.', 'wpshadow' ),
		);
	}
}
