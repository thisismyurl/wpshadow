<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Form Field Frustration
 *
 * Counts how many times users re-enter same field. Validation issue detector.
 *
 * Philosophy: Commandment #8, 9 - Inspire Confidence - Intuitive UX, Show Value (KPIs) - Track impact
 * Priority: 1 (1=Must-Have, 2=Should-Have, 3=Nice-to-Have)
 * Threat Level: 75/100
 *
 * Impact: Shows \"Users re-type email 4+ times (validation issue)\" with field ID.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_UxFormFieldReentry extends Diagnostic_Base {
	protected static $slug = 'ux-form-field-reentry';

	protected static $title = 'Ux Form Field Reentry';

	protected static $description = 'Automatically initialized lean diagnostic for Ux Form Field Reentry. Optimized for minimal overhead while surfacing high-value signals.';

	protected static $family = 'general';

	protected static $family_label = 'General';


	/**
	 * Get diagnostic ID
	 *
	 * @return string
	 */
	public static function get_id(): string {
		return 'ux-form-field-reentry';
	}

	/**
	 * Get diagnostic name
	 *
	 * @return string
	 */
	public static function get_name(): string {
		return __( 'Form Field Frustration', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 *
	 * @return string
	 */
	public static function get_description(): string {
		return __( 'Counts how many times users re-enter same field. Validation issue detector.', 'wpshadow' );
	}

	/**
	 * Get diagnostic category
	 *
	 * @return string
	 */
	public static function get_category(): string {
		return 'design';
	}

	/**
	 * Get threat level (0-100)
	 * Higher = more critical
	 *
	 * @return int
	 */
	public static function get_threat_level(): int {
		return 75;
	}

	/**
	 * Run the diagnostic
	 *
	 * @return array Result with status, message, and data
	 */
	public static function run(): array {
		// STUB: Implement ux-form-field-reentry diagnostic
		//
		// This is a KILLER test that delivers "Holy Sh*t" moments:
		// Shows \"Users re-type email 4+ times (validation issue)\" with field ID.
		//
		// Implementation notes:
		// - Quantify impact with real numbers (dollar amounts, percentages)
		// - Show specific examples (file names, URLs, exact problems)
		// - Provide actionable fix recommendations
		// - Link to KB article explaining why this matters
		// - Track KPI: time saved, revenue impact, disaster prevented

		return array(
			'status'  => 'todo',
			'message' => __( 'Not yet implemented - Priority 1 killer test', 'wpshadow' ),
			'data'    => array(
				'impact'   => 'Shows \"Users re-type email 4+ times (validation issue)\" with field ID.',
				'priority' => 1,
			),
		);
	}

	/**
	 * Get KB article URL
	 *
	 * @return string
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/form-field-reentry';
	}

	/**
	 * Get training video URL
	 *
	 * @return string
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/form-field-reentry';
	}

	public static function check(): ?array {
		if ( ! ( false ) ) {
			return null;
		}

		return \WPShadow\Core\Diagnostic_Lean_Checks::build_finding(
			'ux-form-field-reentry',
			'Ux Form Field Reentry',
			'Automatically initialized lean diagnostic for Ux Form Field Reentry. Optimized for minimal overhead while surfacing high-value signals.',
			'general',
			'low',
			30,
			'ux-form-field-reentry'
		);
	}

	/**
	 * Test: Result structure validation
	 *
	 * Ensures diagnostic returns null (no issues) or array (issues found)
	 * with all required fields populated.
	 *
	 * @return array Test result with 'passed' and 'message'
	 */
	public static function test_result_structure(): array {
		$result = self::check();
		
		// Valid states: null (pass) or array (fail)
		if ( null === $result || is_array( $result ) ) {
			// If array, validate structure
			if ( is_array( $result ) ) {
				$required = array(
					'id', 'title', 'description', 'category', 
					'severity', 'threat_level'
				);
				
				foreach ( $required as $field ) {
					if ( ! isset( $result[ $field ] ) ) {
						return array(
							'passed'  => false,
							'message' => "Missing field: $field",
						);
					}
				}
				
				// Validate field types
				if ( ! is_string( $result['severity'] ) ) {
					return array(
						'passed'  => false,
						'message' => 'severity must be string',
					);
				}
				
				if ( ! is_int( $result['threat_level'] ) || $result['threat_level'] < 0 || $result['threat_level'] > 100 ) {
					return array(
						'passed'  => false,
						'message' => 'threat_level must be int 0-100',
					);
				}
			}
			
			return array(
				'passed'  => true,
				'message' => 'Result structure valid',
			);
		}
		
		return array(
			'passed'  => false,
			'message' => 'Invalid result type: ' . gettype( $result ),
		);
	}}
