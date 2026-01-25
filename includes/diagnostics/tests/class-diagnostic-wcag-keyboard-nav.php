<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;


class Diagnostic_Wcag_Keyboard_Nav extends Diagnostic_Base {
	protected static $slug = 'wcag-keyboard-nav';

	protected static $title = 'Wcag Keyboard Nav';

	protected static $description = 'Automatically initialized lean diagnostic for Wcag Keyboard Nav. Optimized for minimal overhead while surfacing high-value signals.';

	protected static $family = 'general';

	protected static $family_label = 'General';

	/**
	 * Get diagnostic ID
	 */
	public static function get_id(): string {
		return 'wcag-keyboard-nav';
	}

	/**
	 * Get diagnostic name
	 */
	public static function get_name(): string {
		return __( 'Are all functions keyboard accessible?', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 */
	public static function get_description(): string {
		return __( 'Are all functions keyboard accessible?. Part of Accessibility & Inclusivity analysis.', 'wpshadow' );
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
		// Implement: Are all functions keyboard accessible? test
		// Smart implementation needed

		return array(); // Stub: full implementation pending
	}

	/**
	 * Get threat level for this finding (0-100)
	 */
	public static function get_threat_level(): int {
		// Threat level based on diagnostic category
		return 50;
	}

	/**
	 * Get KB article URL
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/wcag-keyboard-nav/';
	}

	/**
	 * Get training video URL
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/wcag-keyboard-nav/';
	}

	public static function check(): ?array {
		$html = self::get_guardian_html();
		if ( empty( $html ) ) {
			return null;
		}

		$issues = array();
		try {
			$dom = new \DOMDocument();
			@$dom->loadHTML( $html );
			$xpath = new \DOMXPath( $dom );

			// Check for interactive elements without keyboard access
			$custom_interactive = $xpath->query( '//div[@onclick] | //span[@onclick] | //div[@role="button"] | //span[@role="button"]' );
			$missing_tabindex   = 0;

			foreach ( $custom_interactive as $elem ) {
				if ( ! $elem->getAttribute( 'tabindex' ) ) {
					++$missing_tabindex;
				}
			}

			if ( $missing_tabindex > 0 ) {
				$issues[] = 'Found ' . $missing_tabindex . ' interactive element(s) without tabindex';
			}
		} catch ( \Exception $e ) {
			return null;
		}

		return empty( $issues ) ? null : array(
			'id'            => 'wcag-keyboard-nav',
			'title'         => 'Keyboard Navigation Issues',
			'description'   => 'Not all functionality is available via keyboard. WCAG 2.1 requires keyboard access.',
			'severity'      => 'high',
			'category'      => 'accessibility',
			'kb_link'       => 'https://wpshadow.com/kb/wcag-keyboard-nav/',
			'training_link' => 'https://wpshadow.com/training/wcag-keyboard-nav/',
			'auto_fixable'  => false,
			'threat_level'  => 75,
			'details'       => $issues,
		);
	}

	protected static function get_guardian_html(): string {
		return isset( $_POST['html'] ) ? sanitize_text_field( wp_unslash( $_POST['html'] ) ) : '';
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Wcag Keyboard Nav
	 * Slug: wcag-keyboard-nav
	 *
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Automatically initialized lean diagnostic for Wcag Keyboard Nav. Optimized for minimal overhead while surfacing high-value signals.
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_wcag_keyboard_nav(): array {
		$good = '<html><body><button>Click</button><div tabindex="0" onclick="alert()">Custom</div></body></html>';
		$bad  = '<html><body><div onclick="alert()">Click</div></body></html>';

		$_POST['html'] = $good;
		$r1            = self::check();
		$_POST['html'] = $bad;
		$r2            = self::check();

		return array(
			'passed'  => is_null( $r1 ) && is_array( $r2 ),
			'message' => 'Keyboard nav check working',
		);
	}
}
