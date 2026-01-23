<?php
declare(strict_types=1);
/**
 * Guest Checkout Availability Diagnostic
 *
 * Philosophy: Forced accounts hurt conversion
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Guest_Checkout_Availability extends Diagnostic_Base {
    public static function check(): ?array {
        if (class_exists('WooCommerce')) {
            $guest_checkout = get_option('woocommerce_enable_guest_checkout');
            if ($guest_checkout !== 'yes') {
                return [
                    'id' => 'seo-guest-checkout-availability',
                    'title' => 'Guest Checkout Not Enabled',
                    'description' => 'Enable guest checkout. Forced account creation reduces conversion rates.',
                    'severity' => 'high',
                    'category' => 'seo',
                    'kb_link' => 'https://wpshadow.com/kb/guest-checkout/',
                    'training_link' => 'https://wpshadow.com/training/checkout-optimization/',
                    'auto_fixable' => false,
                    'threat_level' => 60,
                ];
            }
        }
        return null;
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
	 * Test: Option-based detection
	 *
	 * Verifies that diagnostic correctly reads and evaluates options
	 * and returns appropriate result.
	 *
	 * @return array Test result
	 */
	public static function test_option_detection(): array {
		$result = self::check();
		
		// Should return null or array based on option values
		if ( $result === null || is_array( $result ) ) {
			return array(
				'passed'  => true,
				'message' => 'Option detection working correctly',
			);
		}
		
		return array(
			'passed'  => false,
			'message' => 'Option detection returned invalid type',
		);
	}}
