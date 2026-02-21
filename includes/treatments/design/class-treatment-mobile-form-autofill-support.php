<?php
/**
 * Mobile Form Auto-fill Support Treatment
 *
 * Validates that form inputs have appropriate autocomplete attributes for
 * mobile auto-fill functionality.
 *
 * @package    WPShadow
 * @subpackage Treatments\Mobile
 * @since      1.602.1215
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Form Auto-fill Support Treatment Class
 *
 * Checks that forms use HTML5 autocomplete attributes to enable mobile
 * browsers' auto-fill features, dramatically improving form completion speed.
 *
 * WCAG Reference: 1.3.5 Identify Input Purpose (Level AA)
 *
 * @since 1.602.1215
 */
class Treatment_Mobile_Form_Autofill_Support extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-form-autofill-support';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Form Auto-fill Support';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates that form inputs have appropriate autocomplete attributes for mobile auto-fill functionality';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'mobile';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.602.1215
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Form_Autofill_Support' );
	}

	/**
	 * Get test pages.
	 *
	 * @since  1.602.1215
	 * @return array Pages to test.
	 */
	private static function get_test_pages() {
		$pages = array();

		// Contact page.
		$contact = get_page_by_path( 'contact' );
		if ( $contact ) {
			$pages[ get_permalink( $contact ) ] = 'Contact Page';
		}

		// WooCommerce.
		if ( function_exists( 'wc_get_checkout_url' ) ) {
			$pages[ wc_get_checkout_url() ] = 'Checkout';
		}
		if ( function_exists( 'wc_get_page_permalink' ) ) {
			$account = wc_get_page_permalink( 'myaccount' );
			if ( $account ) {
				$pages[ $account ] = 'My Account';
			}
		}

		// WordPress login.
		$pages[ wp_login_url() ] = 'Login';

		return $pages;
	}

	/**
	 * Capture page HTML.
	 *
	 * @since  1.602.1215
	 * @param  string $url Page URL.
	 * @return string HTML content.
	 */
	private static function capture_page_html( $url ) {
		$response = wp_remote_get(
			$url,
			array(
				'timeout'    => 10,
				'user-agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X)',
			)
		);

		if ( is_wp_error( $response ) ) {
			return '';
		}

		return wp_remote_retrieve_body( $response );
	}

	/**
	 * Find inputs missing autocomplete attributes.
	 *
	 * @since  1.602.1215
	 * @param  string $html HTML to scan.
	 * @return array Missing autocomplete inputs.
	 */
	private static function find_missing_autocomplete( $html ) {
		$missing = array();

		// Define fields that should have autocomplete.
		$autocomplete_map = array(
			'name'              => 'name',
			'first_name'        => 'given-name',
			'firstname'         => 'given-name',
			'last_name'         => 'family-name',
			'lastname'          => 'family-name',
			'email'             => 'email',
			'phone'             => 'tel',
			'tel'               => 'tel',
			'mobile'            => 'tel',
			'address'           => 'street-address',
			'street'            => 'street-address',
			'address1'          => 'address-line1',
			'address2'          => 'address-line2',
			'city'              => 'address-level2',
			'state'             => 'address-level1',
			'province'          => 'address-level1',
			'zip'               => 'postal-code',
			'zipcode'           => 'postal-code',
			'postal'            => 'postal-code',
			'country'           => 'country-name',
			'username'          => 'username',
			'user'              => 'username',
			'password'          => 'current-password',
			'pass'              => 'current-password',
			'card_number'       => 'cc-number',
			'cardnumber'        => 'cc-number',
			'card_name'         => 'cc-name',
			'cardholder'        => 'cc-name',
			'expiry'            => 'cc-exp',
			'exp_date'          => 'cc-exp',
			'cvv'               => 'cc-csc',
			'cvc'               => 'cc-csc',
			'security_code'     => 'cc-csc',
		);

		// Find all inputs.
		if ( ! preg_match_all( '/<input([^>]*)>/i', $html, $matches ) ) {
			return $missing;
		}

		foreach ( $matches[1] as $attributes ) {
			// Skip hidden, submit, button.
			if ( preg_match( '/type=["\'](?:hidden|submit|button|reset)["\']/', $attributes ) ) {
				continue;
			}

			// Extract name and type.
			$name = '';
			$type = 'text';
			$has_autocomplete = false;

			if ( preg_match( '/name=["\']([^"\']+)["\']/', $attributes, $name_match ) ) {
				$name = strtolower( $name_match[1] );
			}
			if ( preg_match( '/type=["\']([^"\']+)["\']/', $attributes, $type_match ) ) {
				$type = $type_match[1];
			}
			if ( preg_match( '/autocomplete=["\']/', $attributes ) ) {
				$has_autocomplete = true;
			}

			// Check if this field should have autocomplete.
			$suggested = '';
			foreach ( $autocomplete_map as $pattern => $autocomplete_value ) {
				if ( strpos( $name, $pattern ) !== false ) {
					$suggested = $autocomplete_value;
					break;
				}
			}

			// If suggested and doesn't have it, add to missing.
			if ( ! empty( $suggested ) && ! $has_autocomplete ) {
				$missing[] = array(
					'name'            => $name,
					'type'            => $type,
					'suggested'       => $suggested,
					'has_autocomplete' => $has_autocomplete,
				);
			}
		}

		return $missing;
	}
}
