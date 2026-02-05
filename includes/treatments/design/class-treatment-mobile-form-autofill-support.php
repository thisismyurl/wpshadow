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
		$issues = array();

		// Test key form pages.
		$test_pages = self::get_test_pages();

		foreach ( $test_pages as $page_url => $page_name ) {
			$html = self::capture_page_html( $page_url );
			if ( empty( $html ) ) {
				continue;
			}

			// Find inputs missing autocomplete attributes.
			$missing_autocomplete = self::find_missing_autocomplete( $html );

			if ( ! empty( $missing_autocomplete ) ) {
				foreach ( $missing_autocomplete as $input ) {
					$issues[] = array(
						'page'                      => $page_name,
						'field_name'                => $input['name'],
						'field_type'                => $input['type'],
						'suggested_autocomplete'    => $input['suggested'],
						'current_has_autocomplete'  => $input['has_autocomplete'],
					);
				}
			}
		}

		if ( empty( $issues ) ) {
			return null;
		}

		$issue_count    = count( $issues );
		$threat_level   = min( 70, 45 + ( $issue_count * 5 ) );
		$severity       = $threat_level >= 65 ? 'medium' : 'low';
		$auto_fixable   = false;

		$description = sprintf(
			/* translators: %d: number of inputs missing autocomplete */
			__( 'Found %d form input(s) without autocomplete attributes. Mobile users rely on auto-fill to complete forms 3x faster. Missing autocomplete reduces mobile conversions by 25-30%%.', 'wpshadow' ),
			$issue_count
		);

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => $description,
			'severity'    => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => $auto_fixable,
			'kb_link'     => 'https://wpshadow.com/kb/mobile-form-autofill',
			'details'     => array(
				'issue_count'   => $issue_count,
				'issues'        => array_slice( $issues, 0, 10 ),
				'why_important' => __(
					'Mobile auto-fill dramatically improves form completion:
					
					Speed Benefits:
					• Users complete forms 3x faster with auto-fill
					• Single tap vs typing entire address/payment info
					• Reduces typing errors by 80%
					
					Conversion Impact:
					• Google reports 25-30% increase in mobile conversions
					• Checkout abandonment drops from 70% to 50%
					• Customer satisfaction increases significantly
					
					Accessibility Benefits:
					• Required for WCAG 2.1 Level AA compliance
					• Helps users with motor disabilities
					• Assists users with cognitive disabilities
					• Screen readers announce field purpose
					
					Browser Support:
					• Safari iOS: Full support (saves to iCloud Keychain)
					• Chrome Android: Full support (Google Account sync)
					• Firefox Mobile: Full support
					• Edge Mobile: Full support
					
					Without autocomplete:
					• Users must manually type everything
					• Higher abandonment on mobile (70%+)
					• More errors and support requests
					• Poor accessibility experience',
					'wpshadow'
				),
				'how_to_fix'    => __(
					'Add autocomplete attributes to form inputs:
					
					Contact Information:
					<input type="text" name="name" autocomplete="name">
					<input type="email" name="email" autocomplete="email">
					<input type="tel" name="phone" autocomplete="tel">
					
					Address Information:
					<input type="text" name="address" autocomplete="street-address">
					<input type="text" name="city" autocomplete="address-level2">
					<input type="text" name="state" autocomplete="address-level1">
					<input type="text" name="zip" autocomplete="postal-code">
					<input type="text" name="country" autocomplete="country-name">
					
					Payment Information:
					<input type="text" name="cc-name" autocomplete="cc-name">
					<input type="text" name="cc-number" autocomplete="cc-number">
					<input type="text" name="cc-exp" autocomplete="cc-exp">
					<input type="text" name="cc-csc" autocomplete="cc-csc">
					
					Login Forms:
					<input type="text" name="username" autocomplete="username">
					<input type="password" name="password" autocomplete="current-password">
					<input type="password" name="new-password" autocomplete="new-password">
					
					For Form Plugins:
					• WooCommerce: Autocomplete automatic in recent versions
					• Gravity Forms: Add custom HTML to field settings
					• Contact Form 7: Use autocomplete attribute in shortcode
					
					Complete list: https://html.spec.whatwg.org/multipage/form-control-infrastructure.html#autofill',
					'wpshadow'
				),
			),
		);
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
