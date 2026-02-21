<?php
/**
 * Mobile Form Field Labels Treatment
 *
 * Validates that all form inputs have associated labels for accessibility
 * and mobile usability (tap-to-focus).
 *
 * @package    WPShadow
 * @subpackage Treatments\Mobile
 * @since      1.602.1205
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Form Field Labels Treatment Class
 *
 * Ensures all form inputs have proper <label> elements associated via for/id.
 * Labels provide larger tap targets on mobile and are essential for screen readers.
 *
 * WCAG Reference: 3.3.2 Labels or Instructions (Level A)
 *
 * @since 1.602.1205
 */
class Treatment_Mobile_Form_Field_Labels extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-form-field-labels';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Form Field Labels';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates that all form inputs have associated labels for accessibility and tap-to-focus on mobile';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'mobile';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.602.1205
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Form_Field_Labels' );
	}

	/**
	 * Get list of pages to test.
	 *
	 * @since  1.602.1205
	 * @return array Pages with URLs as keys, names as values.
	 */
	private static function get_test_pages() {
		$pages = array(
			home_url( '/' ) => 'Homepage',
		);

		// Contact page.
		$contact_page = get_page_by_path( 'contact' );
		if ( $contact_page ) {
			$pages[ get_permalink( $contact_page ) ] = 'Contact Page';
		}

		// WooCommerce pages.
		if ( function_exists( 'wc_get_checkout_url' ) ) {
			$pages[ wc_get_checkout_url() ] = 'Checkout';
		}
		if ( function_exists( 'wc_get_page_permalink' ) ) {
			$account = wc_get_page_permalink( 'myaccount' );
			if ( $account ) {
				$pages[ $account ] = 'Account';
			}
		}

		// WordPress login/register.
		$pages[ wp_login_url() ] = 'Login Page';

		return $pages;
	}

	/**
	 * Capture HTML for page.
	 *
	 * @since  1.602.1205
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
	 * Find inputs without proper labels.
	 *
	 * @since  1.602.1205
	 * @param  string $html HTML to scan.
	 * @return array Unlabeled inputs.
	 */
	private static function find_unlabeled_inputs( $html ) {
		$unlabeled = array();

		// Find all input/select/textarea elements.
		$input_pattern = '/<(input|select|textarea)([^>]*)>/i';
		if ( ! preg_match_all( $input_pattern, $html, $input_matches, PREG_SET_ORDER ) ) {
			return $unlabeled;
		}

		foreach ( $input_matches as $match ) {
			$tag_name   = strtolower( $match[1] );
			$attributes = $match[2];

			// Skip hidden, submit, button inputs.
			if ( preg_match( '/type=["\'](?:hidden|submit|button|reset|image)["\']/', $attributes ) ) {
				continue;
			}

			// Extract id and name.
			$id   = '';
			$name = '';
			$type = $tag_name === 'input' ? 'text' : $tag_name;

			if ( preg_match( '/id=["\']([^"\']+)["\']/', $attributes, $id_match ) ) {
				$id = $id_match[1];
			}
			if ( preg_match( '/name=["\']([^"\']+)["\']/', $attributes, $name_match ) ) {
				$name = $name_match[1];
			}
			if ( preg_match( '/type=["\']([^"\']+)["\']/', $attributes, $type_match ) ) {
				$type = $type_match[1];
			}

			// Skip if no id and no name (can't be labeled).
			if ( empty( $id ) && empty( $name ) ) {
				continue;
			}

			// Check if labeled.
			$has_label = false;
			if ( ! empty( $id ) ) {
				// Look for <label for="id">.
				$has_label = (bool) preg_match( '/<label[^>]*for=["\']' . preg_quote( $id, '/' ) . '["\'][^>]*>/i', $html );
			}

			// Check for aria-label or aria-labelledby.
			$has_aria = (bool) preg_match( '/aria-(?:label|labelledby)=["\']/', $attributes );

			// If no label and no ARIA, it's unlabeled.
			if ( ! $has_label && ! $has_aria ) {
				$unlabeled[] = array(
					'id'       => $id,
					'name'     => $name,
					'type'     => $type,
					'has_aria' => $has_aria,
				);
			}
		}

		return $unlabeled;
	}
}
