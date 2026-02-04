<?php
/**
 * Mobile Input Type Support Diagnostic
 *
 * Validates that form inputs use appropriate HTML5 input types for mobile
 * keyboard optimization (email, tel, url, number, date, etc.).
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Mobile
 * @since      1.602.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Input Type Support Diagnostic Class
 *
 * Scans forms to ensure mobile-optimized input types are used instead of
 * generic text inputs. Proper input types trigger optimized mobile keyboards
 * (numeric keypad for tel, email keyboard for email, etc.).
 *
 * WCAG Reference: 3.2.2 On Input (Level A)
 *
 * @since 1.602.1200
 */
class Diagnostic_Mobile_Input_Type_Support extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-input-type-support';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Input Type Support';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates that form inputs use appropriate HTML5 input types for mobile keyboard optimization';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'mobile';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.602.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Capture homepage and key pages.
		$test_pages = self::get_test_pages();

		foreach ( $test_pages as $page_url => $page_name ) {
			$html = self::capture_page_html( $page_url );
			if ( empty( $html ) ) {
				continue;
			}

			// Find text inputs that should use specialized types.
			$generic_inputs = self::find_generic_inputs( $html );

			if ( ! empty( $generic_inputs ) ) {
				$issues = array_merge( $issues, $generic_inputs );
			}
		}

		if ( empty( $issues ) ) {
			return null;
		}

		// Calculate severity based on number of issues.
		$issue_count    = count( $issues );
		$threat_level   = min( 75, 50 + ( $issue_count * 5 ) );
		$severity       = $threat_level >= 70 ? 'high' : 'medium';
		$auto_fixable   = false; // Requires theme/plugin modification.

		$description = sprintf(
			/* translators: %d: number of form inputs */
			__( 'Found %d form input(s) using generic text type instead of mobile-optimized HTML5 input types. Mobile users experience suboptimal keyboards (e.g., full QWERTY instead of numeric keypad for phone numbers). This increases form abandonment by 30-40%% on mobile devices.', 'wpshadow' ),
			$issue_count
		);

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => $description,
			'severity'    => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => $auto_fixable,
			'kb_link'     => 'https://wpshadow.com/kb/mobile-input-types',
			'details'     => array(
				'issue_count'   => $issue_count,
				'issues'        => array_slice( $issues, 0, 10 ), // Limit to first 10.
				'why_important' => __(
					'HTML5 input types trigger device-specific keyboards on mobile:
					• type="tel" shows numeric keypad with large numbers
					• type="email" shows keyboard with @ and .com shortcuts
					• type="url" shows keyboard with / and .com
					• type="number" shows numeric keypad
					• type="date" shows native date picker
					
					Using generic type="text" for these fields forces users to:
					1. Use full QWERTY keyboard (harder to type numbers)
					2. Manually switch keyboards multiple times
					3. Higher error rates and slower completion
					4. 30-40% higher form abandonment on mobile
					
					Google reports that optimized input types improve mobile form completion rates by 25%.',
					'wpshadow'
				),
				'how_to_fix'    => __(
					'Update form inputs to use appropriate HTML5 types:
					
					Phone Numbers:
					<input type="tel" name="phone" placeholder="(555) 123-4567">
					
					Email Addresses:
					<input type="email" name="email" placeholder="user@example.com">
					
					URLs:
					<input type="url" name="website" placeholder="https://example.com">
					
					Numbers:
					<input type="number" name="quantity" min="1" max="100">
					
					Dates:
					<input type="date" name="birthdate">
					
					For WooCommerce/Contact Form 7/Gravity Forms:
					Most modern form plugins support these via settings or shortcode parameters.',
					'wpshadow'
				),
			),
		);
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
