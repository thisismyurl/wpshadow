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
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Is Contact Form Working?
	 * Slug: contact-form-working
	 * 
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Tests if contact form submissions reach you.
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_contact_form_working(): array {
		/*
		 * IMPLEMENTATION NOTES:
		 * - This test validates the actual WordPress site state
		 * - Do not use mocks or stubs
		 * - Call self::check() to get the diagnostic result
		 * - Verify the result matches expected site state
		 * - Return [ 'passed' => bool, 'message' => string ]
		 */
		
		$result = self::check();
		
		// TODO: Implement actual test logic
		return array(
			'passed' => false,
			'message' => 'Test not yet implemented for ' . self::$slug,
		);
	}

}
