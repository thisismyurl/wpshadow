<?php
/**
 * Tax Configuration Diagnostic
 *
 * Verifies tax settings correctly configured for store location,
 * preventing over/under-charging and compliance issues.
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
 * Diagnostic_Tax_Configuration Class
 *
 * Verifies tax configuration.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Tax_Configuration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'tax-configuration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Tax Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies tax settings';

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
	 * @return array|null Finding array if tax issues, null otherwise.
	 */
	public static function check() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return null; // Not e-commerce
		}

		$tax_status = self::check_tax_configuration();

		if ( ! $tax_status['has_issue'] ) {
			return null; // Tax configured
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Tax configuration issues. Undercharge = lost profit. Overcharge = customer refunds + refund fees. Sales tax nexus = IRS audits. Incorrect setup = legal liability.', 'wpshadow' ),
			'severity'     => 'high',
			'threat_level' => 70,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/tax-configuration',
			'family'       => self::$family,
			'meta'         => array(
				'tax_enabled'  => $tax_status['tax_enabled'] ? 'yes' : 'no',
				'usa_based'    => $tax_status['usa_based'] ? 'yes' : 'no',
			),
			'details'      => array(
				'tax_basics'                  => array(
					'Sales Tax (USA)' => __( 'State/local consumption tax, varies by state' ),
					'VAT (EU)' => __( '17-27% value-added tax, included in price' ),
					'GST (AU/NZ)' => __( '10% goods and services tax, included' ),
					'Nexus' => __( 'Business obligation to collect tax in state' ),
				),
				'us_sales_tax_nexus'          => array(
					'Physical Presence' => array(
						'Store, warehouse, office in state',
						'Employees, contractors in state',
					),
					'Economic Nexus' => array(
						'$100K+ revenue from state (varies)',
						'1000+ transactions in state',
						'Recent change: All remote sellers now have nexus',
					),
					'Consequences' => array(
						'Obligation to collect sales tax',
						'IRS audits if not compliant',
						'Penalties: Back taxes + interest + fines',
					),
				),
				'setting_up_woocommerce_tax'  => array(
					'Enable Tax' => array(
						'WooCommerce → Settings → General',
						'Check: "Enable Taxes"',
						'This enables tax system',
					),
					'Add Tax Rate' => array(
						'WooCommerce → Settings → Tax',
						'+ Add Tax Rate',
						'State: CA (California)',
						'Rate: 7.25%',
						'Apply to: Products / Shipping / Both',
					),
					'Tax Classes' => array(
						'Standard: Taxable at regular rate',
						'Reduced: Lower tax rate items',
						'Zero Rate: Tax-exempt items',
					),
				),
				'complex_tax_scenarios'       => array(
					'Multi-State Sales' => array(
						'Rule: Collect tax in state of delivery',
						'System: WooCommerce can\'t auto-calculate all states',
						'Solution: Use tax automation service',
					),
					'Tax-Exempt Sales' => array(
						'Requirement: B2B sales may be exempt',
						'Requirement: Charity/government sales',
						'Solution: Verify tax ID before sale',
					),
					'International Sales' => array(
						'Rule: VAT collected at destination',
						'Complexity: Different rates by country',
						'Solution: TaxJar, Avalara for automation',
					),
				),
				'tax_automation_services'     => array(
					'TaxJar' => array(
						'Price: $15-25/month',
						'Features: Auto-calc tax, filing',
						'Integration: Direct WooCommerce plugin',
					),
					'Avalara' => array(
						'Price: $25+/month',
						'Features: Sales tax + other taxes',
						'Trusted: 90% of Fortune 100',
					),
					'WooCommerce Tax' => array(
						'Free: Built-in',
						'Limitations: Can\'t handle all complexity',
						'Best for: Simple, single-state',
					),
				),
				'tax_filing_and_remittance'   => array(
					'Frequency' => array(
						'Monthly: High-volume states',
						'Quarterly: Most states',
						'Annually: Low-volume states',
					),
					'Process' => array(
						'File: State tax return',
						'Pay: Tax collected from customers',
						'Deadline: Specific dates by state',
					),
					'Tools' => array(
						'TaxJar: Automates filing',
						'Avalara: Manages compliance',
						'Manual: Spreadsheet tracking',
					),
				),
				'compliance_risks'            => array(
					__( 'Undercharge: Lose profit margin' ),
					__( 'Overcharge: Customer dissatisfaction' ),
					__( 'Non-compliance: IRS penalties 15-25%' ),
					__( 'No nexus claim: Federal court cases' ),
					__( 'Audit risk: $10K-50K+ investigation costs' ),
				),
			),
		);
	}

	/**
	 * Check tax configuration.
	 *
	 * @since  1.2601.2148
	 * @return array Tax status.
	 */
	private static function check_tax_configuration() {
		$tax_enabled = get_option( 'woocommerce_calc_taxes' ) === 'yes';
		$tax_rates = WC_Tax::get_rates();

		// Determine if USA-based
		$base_location = wc_get_base_location();
		$usa_based = isset( $base_location['country'] ) && 'US' === $base_location['country'];

		// Issue if USA-based but tax not configured
		$has_issue = $usa_based && ( ! $tax_enabled || empty( $tax_rates ) );

		return array(
			'has_issue'   => $has_issue,
			'tax_enabled' => $tax_enabled,
			'usa_based'   => $usa_based,
		);
	}
}
