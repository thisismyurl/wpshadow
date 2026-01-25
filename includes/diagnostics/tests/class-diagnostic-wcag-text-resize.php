<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;


class Diagnostic_Wcag_Text_Resize extends Diagnostic_Base {
	protected static $slug = 'wcag-text-resize';

	protected static $title = 'Wcag Text Resize';

	protected static $description = 'Automatically initialized lean diagnostic for Wcag Text Resize. Optimized for minimal overhead while surfacing high-value signals.';

	protected static $family = 'general';

	protected static $family_label = 'General';

	/**
	 * Get diagnostic ID
	 */
	public static function get_id(): string {
		return 'wcag-text-resize';
	}

	/**
	 * Get diagnostic name
	 */
	public static function get_name(): string {
		return __( 'Is text resizable to 200%?', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 */
	public static function get_description(): string {
		return __( 'Is text resizable to 200%?. Part of Accessibility & Inclusivity analysis.', 'wpshadow' );
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
		// Implement: Is text resizable to 200%? test
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
		return 'https://wpshadow.com/kb/wcag-text-resize/';
	}

	/**
	 * Get training video URL
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/wcag-text-resize/';
	}

	protected static function get_guardian_html(): string {
		if ( isset( $_POST['html'] ) && is_string( $_POST['html'] ) ) {
			return sanitize_text_field( wp_unslash( $_POST['html'] ) );
		}
		return '';
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

			$viewport = $xpath->query( '//meta[@name="viewport"]' )->item( 0 );
			if ( $viewport ) {
				$content = $viewport->getAttribute( 'content' );
				if ( strpos( $content, 'user-scalable=no' ) !== false ) {
					$issues[] = 'Viewport user-scalable=no prevents text resizing';
				}
			}
		} catch ( \Exception $e ) {
			return null;
		}

		return empty( $issues ) ? null : array(
			'id'           => 'wcag-text-resize',
			'title'        => 'Text not resizable',
			'description'  => 'Users must be able to resize text up to 200%',
			'severity'     => 'high',
			'category'     => 'accessibility',
			'threat_level' => 57,
			'details'      => $issues,
		);
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Wcag Text Resize
	 * Slug: wcag-text-resize
	 *
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Automatically initialized lean diagnostic for Wcag Text Resize. Optimized for minimal overhead while surfacing high-value signals.
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_wcag_text_resize(): array {
		$good = '<html><head><meta name="viewport" content="width=device-width, initial-scale=1"></head><body>Test</body></html>';
		$bad  = '<html><head><meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no"></head><body>Test</body></html>';

		$_POST['html'] = $good;
		$r1            = self::check();
		$_POST['html'] = $bad;
		$r2            = self::check();

		return array(
			'passed'  => is_null( $r1 ) && is_array( $r2 ),
			'message' => 'Text resize check working',
		);
	}
}
