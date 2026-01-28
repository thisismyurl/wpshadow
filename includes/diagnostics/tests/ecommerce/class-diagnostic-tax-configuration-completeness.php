<?php
/**
 * Tax Configuration Completeness Diagnostic
 *
 * Verifies WooCommerce tax settings are properly configured
 * to avoid legal issues and incorrect tax calculations.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Tax_Configuration_Completeness Class
 *
 * Verifies WooCommerce tax configuration is complete.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Tax_Configuration_Completeness extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'tax-configuration-completeness';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Tax Configuration Completeness';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies WooCommerce tax settings are complete';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'ecommerce';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if tax configuration issues found, null otherwise.
	 */
	public static function check() {
		// Check if WooCommerce is active
		if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			return null; // Not an e-commerce site
		}

		$tax_config = self::validate_tax_configuration();

		if ( $tax_config['is_valid'] ) {
			return null; // Tax properly configured
		}

		$severity = count( $tax_config['missing_items'] ) > 5 ? 'critical' : 'high';
		$threat   = count( $tax_config['missing_items'] ) > 5 ? 85 : 70;

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Tax configuration is incomplete. Incorrect tax calculations could expose you to legal liability and audit penalties.', 'wpshadow' ),
			'severity'     => $severity,
			'threat_level' => $threat,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/woocommerce-tax',
			'family'       => self::$family,
			'meta'         => array(
				'missing_config_items' => count( $tax_config['missing_items'] ),
				'legal_risk'           => __( 'Incorrect taxes can trigger IRS/state audits' ),
				'penalty_risk'          => __( 'Failure to collect taxes: $1,000-10,000+ per violation' ),
			),
			'details'      => array(
				'configuration_checklist' => array(
					'Tax Enabled/Disabled Decision' => array(
						__( 'If selling digital goods: may not need sales tax' ),
						__( 'If selling physical goods: need sales tax' ),
						__( 'If selling services: rules vary by state' ),
						__( 'Cross-border: need to understand nexus laws' ),
					),
					'Tax Rates Configured' => array(
						__( 'Must create tax rates for all sales regions' ),
						__( 'Most states require state + local tax collection' ),
						__( 'Some products tax-exempt (groceries, clothing)' ),
						__( 'Different rates for shipping (taxable/non-taxable)' ),
					),
					'Tax Classes' => array(
						__( 'Standard Rate (most products)' ),
						__( 'Reduced Rate (food, medicine in some states)' ),
						__( 'Zero Rate (exempt products)' ),
					),
					'Tax Calculation Method' => array(
						__( 'Based on shipping address (most states)' ),
						__( 'Based on billing address (few states)' ),
						__( 'Based on store address (rare)' ),
					),
				),
				'setup_guide'             => array(
					'Step 1: Determine Your Sales Tax Obligations' => array(
						__( 'Do you have nexus? (employee/office/inventory)' ),
						__( 'Which states do you ship to?' ),
						__( 'What are your annual sales?' ),
					),
					'Step 2: Enable Tax Calculation' => array(
						__( 'WooCommerce → Settings → General' ),
						__( 'Enable "Enable taxes" checkbox' ),
						__( 'Set calculation based on: Shipping Address' ),
					),
					'Step 3: Add Tax Rates' => array(
						__( 'Go to WooCommerce → Settings → Taxes' ),
						__( 'Add rate for each state/province you ship to' ),
						__( 'Format: State, Tax Rate (e.g., CA, 8.625%)' ),
						__( 'Set compound = No (unless really complex)' ),
					),
					'Step 4: Configure Tax Classes' => array(
						__( 'Add tax classes for exempt items' ),
						__( 'Example: "Food" (tax-exempt in some states)' ),
						__( 'Assign products to correct class' ),
					),
					'Step 5: Test Tax Calculation' => array(
						__( 'Create test order from different states' ),
						__( 'Verify tax amount is correct' ),
						__( 'Test with shipping address vs billing' ),
					),
					'Step 6: Integrate with Tax Service (Optional)' => array(
						__( 'TaxJar: Automatic tax rates, filing' ),
						__( 'Avalara: Enterprise tax solution' ),
						__( 'WooCommerce built-in: Manual setup only' ),
					),
				),
				'compliance_notes'        => array(
					__( 'Sales tax is customer\'s responsibility in some states' ),
					__( 'As merchant: obligation to collect/remit' ),
					__( 'Economic Nexus: Now applies to all online sellers' ),
					__( 'Most states: Must collect tax if $100K+ annual sales' ),
					__( 'Some states: Collect immediately regardless of sales' ),
				),
				'plugin_recommendations' => array(
					'TaxJar (Recommended)' => array(
						'Automatic tax rates',
						'Automated filing',
						'Nexus calculations',
						'Cost: $70-100/month',
					),
					'WooCommerce Built-in' => array(
						'Basic manual tax setup',
						'No automation',
						'Cost: Free (your time)',
					),
				),
			),
		);
	}

	/**
	 * Validate tax configuration.
	 *
	 * @since  1.2601.2148
	 * @return array Tax configuration status.
	 */
	private static function validate_tax_configuration() {
		$missing_items = array();

		// Check if taxes enabled
		$taxes_enabled = get_option( 'woocommerce_calc_taxes' );
		if ( ! $taxes_enabled ) {
			$missing_items[] = __( 'Tax calculation not enabled' );
		}

		// Check if tax rates configured
		$tax_rates = \WC_Tax::get_rates();
		if ( empty( $tax_rates ) ) {
			$missing_items[] = __( 'No tax rates configured' );
		}

		// Check if shipping tax configured
		$shipping_tax = get_option( 'woocommerce_shipping_tax_class' );
		if ( $shipping_tax === '' ) {
			$missing_items[] = __( 'Shipping tax configuration not set' );
		}

		return array(
			'is_valid'      => empty( $missing_items ),
			'missing_items' => $missing_items,
		);
	}
}
