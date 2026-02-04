<?php
/**
 * Phone Number Validation Restrictive Diagnostic
 *
 * Detects when phone number validation is too restrictive, rejecting valid international formats.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\UX
 * @since      1.6035.2306
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\UX;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Phone Number Validation Restrictive Diagnostic Class
 *
 * Checks if phone validation rejects valid international phone numbers.
 *
 * @since 1.6035.2306
 */
class Diagnostic_Phone_Number_Validation_Restrictive extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'phone-number-validation-restrictive';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Phone Number Validation Too Restrictive';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects when phone validation rejects valid international numbers';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'ux';

	/**
	 * Run the diagnostic check
	 *
	 * @since  1.6035.2306
	 * @return array|null Finding array or null if no issues found.
	 */
	public static function check() {
		// Check for plugins with phone validation.
		$form_plugins = array(
			'woocommerce/woocommerce.php'          => 'WooCommerce',
			'contact-form-7/wp-contact-form-7.php' => 'Contact Form 7',
			'ninja-forms/ninja-forms.php'          => 'Ninja Forms',
			'wpforms-lite/wpforms.php'             => 'WPForms',
			'gravityforms/gravityforms.php'        => 'Gravity Forms',
		);

		$active_form_plugins = array();
		foreach ( $form_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_form_plugins[] = $name;
			}
		}

		if ( empty( $active_form_plugins ) ) {
			return null; // No form plugins with phone fields.
		}

		// Check theme functions.php for phone validation patterns.
		$functions_file = get_stylesheet_directory() . '/functions.php';
		$restrictive_patterns_found = array();

		if ( file_exists( $functions_file ) ) {
			$content = file_get_contents( $functions_file );

			// Look for overly restrictive patterns.
			$bad_patterns = array(
				'/^\d{10}$/'                    => __( 'Only accepts exactly 10 digits', 'wpshadow' ),
				'/^\d{3}-\d{3}-\d{4}$/'         => __( 'Only accepts XXX-XXX-XXXX format', 'wpshadow' ),
				'/^\(\d{3}\) \d{3}-\d{4}$/'     => __( 'Only accepts (XXX) XXX-XXXX format', 'wpshadow' ),
				'/^1?\d{10}$/'                  => __( 'Only accepts US numbers', 'wpshadow' ),
				'/^[0-9]{3}-[0-9]{3}-[0-9]{4}$/' => __( 'Forces specific dash format', 'wpshadow' ),
			);

			foreach ( $bad_patterns as $pattern => $issue ) {
				if ( strpos( $content, $pattern ) !== false ) {
					$restrictive_patterns_found[ $pattern ] = $issue;
				}
			}
		}

		// If no restrictive patterns found and using modern form builders, assume it's okay.
		$modern_intl_plugins = array(
			'wpforms/wpforms.php',
			'gravityforms/gravityforms.php',
		);

		$has_modern_validation = false;
		foreach ( $modern_intl_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_modern_validation = true;
				break;
			}
		}

		if ( $has_modern_validation && empty( $restrictive_patterns_found ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Your phone number validation only accepts one specific format. International visitors and those with different formatting can\'t complete your forms', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 50,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/phone-validation',
			'context'      => array(
				'active_form_plugins'    => $active_form_plugins,
				'restrictive_patterns'   => $restrictive_patterns_found,
				'has_modern_validation'  => $has_modern_validation,
				'impact'                 => __( 'International customers can\'t complete checkout. Customers with extensions, spaces, or different formatting see "Invalid phone number" errors even though their number is valid.', 'wpshadow' ),
				'common_issues'          => array(
					__( 'Rejects international format: +44 20 7946 0958', 'wpshadow' ),
					__( 'Rejects extensions: (555) 123-4567 x890', 'wpshadow' ),
					__( 'Rejects spaces: 555 123 4567', 'wpshadow' ),
					__( 'Rejects country codes: +1-555-123-4567', 'wpshadow' ),
				),
				'recommendation'         => array(
					__( 'Accept all common phone formats (dashes, spaces, parentheses, dots)', 'wpshadow' ),
					__( 'Allow international country codes (+XX)', 'wpshadow' ),
					__( 'Allow extensions (x123, ext 123)', 'wpshadow' ),
					__( 'Strip formatting before validation', 'wpshadow' ),
					__( 'Use libphonenumber-based validation', 'wpshadow' ),
					__( 'Show example format but accept variations', 'wpshadow' ),
				),
				'good_validation'        => __( 'Strip all non-digits, verify length is 10-15 digits, allow any formatting', 'wpshadow' ),
			),
		);
	}
}
