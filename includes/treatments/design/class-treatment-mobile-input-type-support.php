<?php
/**
 * Mobile Input Type Support Treatment
 *
 * Validates that form inputs use appropriate HTML5 input types for mobile
 * keyboard optimization (email, tel, url, number, date, etc.).
 *
 * @package    WPShadow
 * @subpackage Treatments\Mobile
 * @since      1.602.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Input Type Support Treatment Class
 *
 * Scans forms to ensure mobile-optimized input types are used instead of
 * generic text inputs. Proper input types trigger optimized mobile keyboards
 * (numeric keypad for tel, email keyboard for email, etc.).
 *
 * WCAG Reference: 3.2.2 On Input (Level A)
 *
 * @since 1.602.1200
 */
class Treatment_Mobile_Input_Type_Support extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-input-type-support';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Input Type Support';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates that form inputs use appropriate HTML5 input types for mobile keyboard optimization';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'mobile';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.602.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Input_Type_Support' );
	}

	/**
	 * Get list of pages to test.
	 *
	 * @since  1.602.1200
	 * @return array Pages to test with URLs as keys, names as values.
	 */
	private static function get_test_pages() {
		$pages = array(
			home_url( '/' ) => 'Homepage',
		);

		// Add contact page if exists.
		$contact_page = get_page_by_path( 'contact' );
		if ( $contact_page ) {
			$pages[ get_permalink( $contact_page ) ] = 'Contact Page';
		}

		// Add checkout page if WooCommerce active.
		if ( function_exists( 'wc_get_checkout_url' ) ) {
			$pages[ wc_get_checkout_url() ] = 'Checkout Page';
		}

		// Add account page if WooCommerce active.
		if ( function_exists( 'wc_get_page_permalink' ) ) {
			$account_url = wc_get_page_permalink( 'myaccount' );
			if ( $account_url ) {
				$pages[ $account_url ] = 'Account Page';
			}
		}

		return $pages;
	}

	/**
	 * Capture HTML for a given page URL.
	 *
	 * @since  1.602.1200
	 * @param  string $url Page URL.
	 * @return string HTML content.
	 */
	private static function capture_page_html( $url ) {
		$response = wp_remote_get(
			$url,
			array(
				'timeout'    => 10,
				'user-agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/605.1.15',
			)
		);

		if ( is_wp_error( $response ) ) {
			return '';
		}

		return wp_remote_retrieve_body( $response );
	}

	/**
	 * Find generic text inputs that should use specialized types.
	 *
	 * @since  1.602.1200
	 * @param  string $html HTML content to scan.
	 * @return array List of issues found.
	 */
	private static function find_generic_inputs( $html ) {
		$issues = array();

		// Pattern: Find input type="text" with names/placeholders suggesting other types.
		$patterns = array(
			'phone'  => array(
				'regex'          => '/<input[^>]*type=["\']text["\'][^>]*(?:name|id|placeholder)=["\'][^"\']*(?:phone|tel|mobile|cell)[^"\']*["\'][^>]*>/i',
				'suggested_type' => 'tel',
				'reason'         => 'Phone number field using type="text" instead of type="tel"',
			),
			'email'  => array(
				'regex'          => '/<input[^>]*type=["\']text["\'][^>]*(?:name|id|placeholder)=["\'][^"\']*(?:email|e-mail)[^"\']*["\'][^>]*>/i',
				'suggested_type' => 'email',
				'reason'         => 'Email field using type="text" instead of type="email"',
			),
			'url'    => array(
				'regex'          => '/<input[^>]*type=["\']text["\'][^>]*(?:name|id|placeholder)=["\'][^"\']*(?:url|website|link)[^"\']*["\'][^>]*>/i',
				'suggested_type' => 'url',
				'reason'         => 'URL field using type="text" instead of type="url"',
			),
			'number' => array(
				'regex'          => '/<input[^>]*type=["\']text["\'][^>]*(?:name|id|placeholder)=["\'][^"\']*(?:zip|postal|quantity|age|year)[^"\']*["\'][^>]*>/i',
				'suggested_type' => 'number',
				'reason'         => 'Numeric field using type="text" instead of type="number"',
			),
			'date'   => array(
				'regex'          => '/<input[^>]*type=["\']text["\'][^>]*(?:name|id|placeholder)=["\'][^"\']*(?:date|birthday|dob)[^"\']*["\'][^>]*>/i',
				'suggested_type' => 'date',
				'reason'         => 'Date field using type="text" instead of type="date"',
			),
		);

		foreach ( $patterns as $category => $pattern_info ) {
			if ( preg_match_all( $pattern_info['regex'], $html, $matches ) ) {
				foreach ( $matches[0] as $match ) {
					// Extract name or id for identification.
					$name = '';
					if ( preg_match( '/(?:name|id)=["\']([^"\']+)["\']/', $match, $name_match ) ) {
						$name = $name_match[1];
					}

					$issues[] = array(
						'category'       => $category,
						'field_name'     => $name,
						'current_type'   => 'text',
						'suggested_type' => $pattern_info['suggested_type'],
						'reason'         => $pattern_info['reason'],
						'html_snippet'   => substr( $match, 0, 150 ) . ( strlen( $match ) > 150 ? '...' : '' ),
					);
				}
			}
		}

		return $issues;
	}
}
