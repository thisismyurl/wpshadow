<?php
/**
 * Checkout Field Count Diagnostic
 *
 * Counts required checkout fields. Each additional field increases
 * abandonment rate (Baymard Institute research).
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6028.1835
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Checkout_Field_Count Class
 *
 * Analyzes WooCommerce checkout form complexity.
 *
 * @since 1.6028.1835
 */
class Diagnostic_Checkout_Field_Count extends Diagnostic_Base {

	protected static $slug = 'checkout-field-count';
	protected static $title = 'Checkout Field Count Exceeds 7 Fields';
	protected static $description = 'Analyzes checkout form field count for friction';
	protected static $family = 'ux_engagement';

	public static function check() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return null;
		}

		$field_analysis = self::analyze_checkout_fields();
		$required_count = $field_analysis['required_count'];

		if ( $required_count <= 7 ) {
			return null; // Acceptable field count.
		}

		$severity = $required_count > 10 ? 'high' : 'medium';
		$threat_level = min( 70, 40 + ( $required_count - 7 ) * 5 );

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf( __( '%d required fields (recommended: ≤7)', 'wpshadow' ), $required_count ),
			'severity'    => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/checkout-fields',
			'family'      => self::$family,
			'meta'        => array(
				'required_count'    => $required_count,
				'total_count'       => $field_analysis['total_count'],
				'recommended'       => __( 'Reduce to ≤7 required fields', 'wpshadow' ),
				'impact_level'      => 'high',
				'immediate_actions' => array(
					__( 'Make optional fields truly optional', 'wpshadow' ),
					__( 'Remove unnecessary fields', 'wpshadow' ),
					__( 'Enable guest checkout', 'wpshadow' ),
					__( 'Use address autocomplete', 'wpshadow' ),
				),
			),
			'details'     => array(
				'why_important' => __( 'Baymard Institute research: each checkout field increases abandonment. Average checkout has 14.88 fields but optimal is ≤7 required. Extra fields cause 27% cart abandonment. Studies show reducing fields from 11 to 6 increases conversion 120%. Form friction is top reason for checkout abandonment after unexpected costs.', 'wpshadow' ),
				'user_impact'   => array(
					__( 'Higher Abandonment: 27% abandon due to form length', 'wpshadow' ),
					__( 'Mobile Friction: Typing on phone is tedious', 'wpshadow' ),
					__( 'Time Perception: Feels like too much work', 'wpshadow' ),
					__( 'Privacy Concerns: More data requested = less trust', 'wpshadow' ),
				),
				'field_analysis' => $field_analysis,
				'baymard_research' => array(
					'≤5 fields'  => __( 'Excellent - Minimal friction', 'wpshadow' ),
					'≤7 fields'  => __( 'Good - Acceptable experience', 'wpshadow' ),
					'7-10 fields' => __( 'Warning - Consider reduction', 'wpshadow' ),
					'>10 fields' => __( 'Critical - High abandonment risk', 'wpshadow' ),
				),
				'solution_options' => array(
					'free'     => array(
						'label'       => __( 'Remove Optional Fields', 'wpshadow' ),
						'description' => __( 'Disable unnecessary WooCommerce fields', 'wpshadow' ),
						'steps'       => array(
							__( 'Add filter to functions.php', 'wpshadow' ),
							__( 'unset( $fields[\'billing\'][\'billing_company\'] );', 'wpshadow' ),
							__( 'Remove phone2, fax, company if not needed', 'wpshadow' ),
							__( 'Make address_2 optional', 'wpshadow' ),
							__( 'Test checkout flow', 'wpshadow' ),
						),
					),
					'premium'  => array(
						'label'       => __( 'Checkout Field Editor', 'wpshadow' ),
						'description' => __( 'Visual field customization', 'wpshadow' ),
						'steps'       => array(
							__( 'Install Checkout Field Editor plugin', 'wpshadow' ),
							__( 'Mark fields as optional', 'wpshadow' ),
							__( 'Reorder for logical flow', 'wpshadow' ),
							__( 'Hide unnecessary fields', 'wpshadow' ),
							__( 'Save and test', 'wpshadow' ),
						),
					),
					'advanced' => array(
						'label'       => __( 'Progressive Disclosure', 'wpshadow' ),
						'description' => __( 'Multi-step checkout with minimal first step', 'wpshadow' ),
						'steps'       => array(
							__( 'Install multi-step checkout plugin', 'wpshadow' ),
							__( 'Step 1: Email + shipping basics (≤5 fields)', 'wpshadow' ),
							__( 'Step 2: Billing details', 'wpshadow' ),
							__( 'Step 3: Payment', 'wpshadow' ),
							__( 'Show progress indicator', 'wpshadow' ),
						),
					),
				),
				'best_practices' => array(
					__( 'Target ≤7 required fields total', 'wpshadow' ),
					__( 'Enable guest checkout (no account required)', 'wpshadow' ),
					__( 'Use address autocomplete (Google Places)', 'wpshadow' ),
					__( 'Make company name optional', 'wpshadow' ),
					__( 'Remove second address line if rarely used', 'wpshadow' ),
				),
				'testing_steps' => array(
					'verification' => array(
						__( 'Add item to cart, proceed to checkout', 'wpshadow' ),
						__( 'Count required fields (marked with *)', 'wpshadow' ),
						__( 'Test on mobile device', 'wpshadow' ),
						__( 'Time checkout completion', 'wpshadow' ),
					),
					'expected_result' => __( '≤7 required fields with guest checkout option', 'wpshadow' ),
				),
			),
		);
	}

	private static function analyze_checkout_fields() {
		$result = array(
			'required_count' => 0,
			'total_count'    => 0,
			'field_list'     => array(),
		);

		if ( ! function_exists( 'WC' ) ) {
			return $result;
		}

		// Get checkout fields.
		$checkout = WC()->checkout();
		if ( ! $checkout ) {
			return $result;
		}

		$fields = $checkout->get_checkout_fields();

		foreach ( array( 'billing', 'shipping' ) as $fieldset_key ) {
			if ( isset( $fields[ $fieldset_key ] ) ) {
				foreach ( $fields[ $fieldset_key ] as $field_key => $field ) {
					$result['total_count']++;
					$is_required = ! empty( $field['required'] );

					if ( $is_required ) {
						$result['required_count']++;
					}

					$result['field_list'][] = array(
						'key'      => $field_key,
						'label'    => $field['label'] ?? $field_key,
						'required' => $is_required,
					);
				}
			}
		}

		return $result;
	}
}
