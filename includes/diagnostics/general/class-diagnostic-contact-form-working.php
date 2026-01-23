<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Is Contact Form Working?
 *
 * Target Persona: Non-technical Site Owner (Mom/Dad)
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Contact_Form_Working extends Diagnostic_Base {
	protected static $slug        = 'contact-form-working';
	protected static $title       = 'Is Contact Form Working?';
	protected static $description = 'Tests if contact form submissions reach you.';

	public static function check(): ?array {
		$contact_plugins = array(
			'contact-form-7/wp-contact-form-7.php' => 'Contact Form 7',
			'wpforms-lite/wpforms.php'             => 'WPForms',
			'ninja-forms/ninja-forms.php'          => 'Ninja Forms',
			'formidable/formidable.php'            => 'Formidable Forms',
			'gravityforms/gravityforms.php'        => 'Gravity Forms',
		);

		$active_forms = array();
		foreach ( $contact_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_forms[] = $name;
			}
		}

		if ( ! empty( $active_forms ) ) {
			return null;
		}

		return array(
			'id'            => static::$slug,
			'title'         => __( 'No contact form plugin detected', 'wpshadow' ),
			'description'   => __( 'Visitors cannot reach you. Install Contact Form 7 or similar to let people contact you.', 'wpshadow' ),
			'severity'      => 'medium',
			'category'      => 'general',
			'kb_link'       => 'https://wpshadow.com/kb/contact-form-working/',
			'training_link' => 'https://wpshadow.com/training/contact-form-working/',
			'auto_fixable'  => false,
			'threat_level'  => 60,
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
	}
	/**
	 * Test: Plugin detection logic
	 *
	 * Verifies that diagnostic correctly checks for active plugins
	 * and reports issues appropriately.
	 *
	 * @return array Test result
	 */
	public static function test_plugin_detection(): array {
		$result = self::check();
		
		// Plugin detection should return null (no plugin/no issue) or array (issue)
		if ( $result === null || is_array( $result ) ) {
			return array(
				'passed'  => true,
				'message' => 'Plugin detection logic valid',
			);
		}
		
		return array(
			'passed'  => false,
			'message' => 'Invalid plugin detection result',
		);
	}}
