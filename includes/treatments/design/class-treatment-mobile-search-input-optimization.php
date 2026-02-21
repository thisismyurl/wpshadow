<?php
/**
 * Mobile Search Input Optimization Treatment
 *
 * Validates that search inputs are optimized for mobile with proper keyboard,
 * autocomplete, and visual design.
 *
 * @package    WPShadow
 * @subpackage Treatments\Mobile
 * @since      1.602.1225
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Search Input Optimization Treatment Class
 *
 * Checks search forms for mobile-specific optimizations including input type,
 * autocomplete, button size, and keyboard appearance.
 *
 * @since 1.602.1225
 */
class Treatment_Mobile_Search_Input_Optimization extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-search-input-optimization';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Search Input Optimization';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates that search inputs are optimized for mobile with proper keyboard, autocomplete, and design';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'mobile';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.602.1225
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Search_Input_Optimization' );
	}

	/**
	 * Check search forms for mobile issues.
	 *
	 * @since  1.602.1225
	 * @param  string $html HTML to scan.
	 * @return array Issues found.
	 */
	private static function check_search_forms( $html ) {
		$issues = array();

		// Find search forms.
		if ( ! preg_match_all( '/<form[^>]*role=["\']search["\'][^>]*>(.*?)<\/form>/is', $html, $form_matches ) ) {
			// Try alternate pattern (name="s").
			if ( ! preg_match_all( '/<form[^>]*>(.*?name=["\']s["\'].*?)<\/form>/is', $html, $form_matches ) ) {
				// No search form found.
				$issues[] = array(
					'issue_type'  => 'no_search_form',
					'severity'    => 'low',
					'description' => 'No search form detected on homepage',
				);
				return $issues;
			}
		}

		foreach ( $form_matches[0] as $idx => $form_html ) {
			$form_content = $form_matches[1][ $idx ];

			// Check 1: Using type="search" (not type="text").
			if ( ! preg_match( '/type=["\']search["\']/', $form_content ) ) {
				if ( preg_match( '/type=["\']text["\'][^>]*name=["\']s["\']/', $form_content ) ||
					 preg_match( '/name=["\']s["\'][^>]*type=["\']text["\']/', $form_content ) ) {
					$issues[] = array(
						'issue_type'  => 'wrong_input_type',
						'severity'    => 'medium',
						'description' => 'Search input uses type="text" instead of type="search"',
						'impact'      => 'Mobile keyboard doesn\'t show search button',
					);
				}
			}

			// Check 2: Submit button size/tap target.
			if ( preg_match( '/<button[^>]*type=["\']submit["\'][^>]*>/i', $form_content, $button_match ) ) {
				// Can't reliably check size from HTML, but we can check if button has content.
				if ( strpos( $button_match[0], 'aria-label' ) === false && ! preg_match( '/>.*?<\/button>/', $form_content ) ) {
					$issues[] = array(
						'issue_type'  => 'button_no_label',
						'severity'    => 'medium',
						'description' => 'Search button has no aria-label or visible text',
						'impact'      => 'Accessibility issue for screen readers',
					);
				}
			}

			// Check 3: Font size (should be 16px+ to prevent iOS zoom).
			// This is difficult to check from HTML, but we can check for inline styles.
			if ( preg_match( '/style=["\'][^"\']*font-size:\s*([0-9]+)px/', $form_content, $size_match ) ) {
				$font_size = (int) $size_match[1];
				if ( $font_size < 16 ) {
					$issues[] = array(
						'issue_type'  => 'font_too_small',
						'severity'    => 'medium',
						'description' => sprintf( 'Search input font-size is %dpx (should be 16px+ for iOS)', $font_size ),
						'impact'      => 'iOS will zoom page when input focused',
					);
				}
			}

			// Check 4: Autocomplete attribute.
			if ( strpos( $form_content, 'autocomplete' ) === false ) {
				$issues[] = array(
					'issue_type'  => 'no_autocomplete',
					'severity'    => 'low',
					'description' => 'Search input missing autocomplete attribute',
					'impact'      => 'Browser won\'t suggest previous searches',
				);
			}

			// Check 5: Label for accessibility.
			if ( ! preg_match( '/<label[^>]*>/', $form_content ) && ! preg_match( '/aria-label=["\']/', $form_content ) ) {
				$issues[] = array(
					'issue_type'  => 'no_label',
					'severity'    => 'medium',
					'description' => 'Search input has no <label> or aria-label',
					'impact'      => 'Accessibility issue for screen readers',
				);
			}
		}

		return $issues;
	}

	/**
	 * Capture page HTML.
	 *
	 * @since  1.602.1225
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
}
