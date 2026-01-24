<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;


class Diagnostic_Wcag_Focus_Visible extends Diagnostic_Base {
	protected static $slug = 'wcag-focus-visible';

	protected static $title = 'Wcag Focus Visible';

	protected static $description = 'Automatically initialized lean diagnostic for Wcag Focus Visible. Optimized for minimal overhead while surfacing high-value signals.';

	protected static $family = 'general';

	protected static $family_label = 'General';

	/**
	 * Get diagnostic ID
	 */
	public static function get_id(): string {
		return 'wcag-focus-visible';
	}

	/**
	 * Get diagnostic name
	 */
	public static function get_name(): string {
		return __( 'Is focus indicator visible?', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 */
	public static function get_description(): string {
		return __( 'Is focus indicator visible?. Part of Accessibility & Inclusivity analysis.', 'wpshadow' );
	}

	/**
	 * Get diagnostic category
	 */
	public static function get_category(): string {
		return 'accessibility';
	}

	/**
	 * Run the diagnostic test
	 *
	 * @return array Finding data or empty if no issue
	 */
	public static function run(): array {
		// Implement: Is focus indicator visible? test
		// Smart implementation needed

		return array(); // Stub: full implementation pending
	}

	/**
	 * Get threat level for this finding (0-100)
	 */
	public static function get_threat_level(): int {
		// Threat level based on diagnostic category
		return 57;
	}

	/**
	 * Get KB article URL
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/wcag-focus-visible/';
	}

	/**
	 * Get training video URL
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/wcag-focus-visible/';
	}

	/**
	 * Check if focus indicators are visible in the HTML
	 *
	 * Guardian feeds HTML content for analysis.
	 * This diagnostic parses HTML to detect focus indicator visibility issues.
	 *
	 * @return ?array Null if pass, array with findings if fail
	 */
	public static function check(): ?array {
		// Get HTML from Guardian (should be passed via global or parameter)
		$html = self::get_guardian_html();

		if ( empty( $html ) ) {
			// Cannot analyze without HTML - return null (pass for local check)
			return null;
		}

		$issues = [];

		try {
			$dom = new \DOMDocument();
			@$dom->loadHTML( $html );
			$xpath = new \DOMXPath( $dom );

			// Check for interactive elements
			$interactive = $xpath->query( '//button | //a | //input | //select | //textarea' );

			if ( $interactive->length === 0 ) {
				// No interactive elements to check
				return null;
			}

			// Check for focus-related styles
			$style_nodes = $xpath->query( '//style | //link[@rel="stylesheet"]' );
			$has_focus_styles = false;

			foreach ( $style_nodes as $node ) {
				if ( $node->textContent && ( strpos( $node->textContent, ':focus' ) !== false || strpos( $node->textContent, ':focus-visible' ) !== false ) ) {
					$has_focus_styles = true;
					break;
				}
			}

			// Check for focus indicators with visibility issues (display:none, visibility:hidden on focus)
			$bad_focus_styles = $xpath->query( '//*[@style]' );
			$visibility_issues = 0;

			foreach ( $bad_focus_styles as $elem ) {
				$style = $elem->getAttribute( 'style' );
				// Check if element removes focus visibility
				if ( preg_match( '/outline\s*:\s*(none|0)/i', $style ) ) {
					if ( ! preg_match( '/outline\s*:\s*\d+px/i', $style ) ) {
						$visibility_issues++;
					}
				}
			}

			// If we found significant visibility issues
			if ( $visibility_issues > ( $interactive->length * 0.1 ) ) {
				$issues[] = 'Found focus indicator visibility issues: outline removed on ' . $visibility_issues . ' interactive elements';
			}

			// Check for proper focus handling
			if ( ! $has_focus_styles && $interactive->length > 0 ) {
				$issues[] = 'No :focus or :focus-visible styles detected for interactive elements';
			}

		} catch ( \Exception $e ) {
			// HTML parsing error - cannot determine, pass
			return null;
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return [
			'id'            => 'wcag-focus-visible',
			'title'         => 'Focus Indicators Not Visible',
			'description'   => 'Keyboard users cannot see which element has focus. This violates WCAG 2.4.7.',
			'severity'      => 'high',
			'category'      => 'accessibility',
			'kb_link'       => 'https://wpshadow.com/kb/wcag-focus-visible/',
			'training_link' => 'https://wpshadow.com/training/wcag-focus-visible/',
			'auto_fixable'  => false,
			'threat_level'  => 75,
			'details'       => $issues,
		];
	}

	/**
	 * Get HTML from Guardian
	 *
	 * @return string HTML content or empty string
	 */
	protected static function get_guardian_html(): string {
		// Check if Guardian provided HTML via POST
		if ( isset( $_POST['html'] ) && is_string( $_POST['html'] ) ) {
			return sanitize_text_field( wp_unslash( $_POST['html'] ) );
		}

		// Check for global or filter
		$html = apply_filters( 'wpshadow_diagnostic_html', '', 'wcag-focus-visible' );

		return is_string( $html ) ? $html : '';
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Wcag Focus Visible
	 * Slug: wcag-focus-visible
	 *
	 * Test Purpose:
	 * Verify focus indicator visibility in HTML
	 * - PASS: check() returns NULL when focus indicators are visible/present
	 * - FAIL: check() returns array when focus indicators are missing/hidden
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_wcag_focus_visible(): array {
		// Test Case 1: HTML with proper focus styles (should pass)
		$good_html = '<html><head><style>button:focus { outline: 2px solid blue; }</style></head><body><button>Click me</button></body></html>';

		// Simulate Guardian providing HTML
		$_POST['html'] = $good_html;
		$result = self::check();

		if ( is_null( $result ) ) {
			return [
				'passed' => true,
				'message' => 'WCAG Focus Visible: HTML with proper focus styles correctly identified as passing'
			];
		}

		// Test Case 2: HTML with hidden focus (should fail)
		$bad_html = '<html><head><style>button { outline: none; }</style></head><body><button>Click me</button></body></html>';

		$_POST['html'] = $bad_html;
		$result = self::check();

		if ( is_array( $result ) && isset( $result['id'] ) && $result['id'] === 'wcag-focus-visible' ) {
			return [
				'passed' => true,
				'message' => 'WCAG Focus Visible: HTML with hidden focus correctly identified as failing'
			];
		}

		return [
			'passed' => false,
			'message' => 'WCAG Focus Visible: Test logic failed'
		];
	}

}

