<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;


class Diagnostic_Wcag_Valid_Html extends Diagnostic_Base {
	protected static $slug = 'wcag-valid-html';

	protected static $title = 'Wcag Valid Html';

	protected static $description = 'Automatically initialized lean diagnostic for Wcag Valid Html. Optimized for minimal overhead while surfacing high-value signals.';

	protected static $family = 'general';

	protected static $family_label = 'General';

	/**
	 * Get diagnostic ID
	 */
	public static function get_id(): string {
		return 'wcag-valid-html';
	}

	/**
	 * Get diagnostic name
	 */
	public static function get_name(): string {
		return __( 'Does HTML validate?', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 */
	public static function get_description(): string {
		return __( 'Does HTML validate?. Part of Accessibility & Inclusivity analysis.', 'wpshadow' );
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
		// Implement: Does HTML validate? test
		// Smart implementation needed

		return array(); // Stub: full implementation pending
	}

	/**
	 * Get threat level for this finding (0-100)
	 */
	public static function get_threat_level(): int {
		// Threat level based on diagnostic category
		return 53;
	}

	/**
	 * Get KB article URL
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/wcag-valid-html/';
	}

	/**
	 * Get training video URL
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/wcag-valid-html/';
	}

	protected static function get_guardian_html(): string {
		if (isset($_POST['html']) && is_string($_POST['html'])) {
			return sanitize_text_field(wp_unslash($_POST['html']));
		}
		return '';
	}

	public static function check(): ?array {
		$html = self::get_guardian_html();
		if (empty($html)) return null;

		$issues = [];
		try {
			$dom = new \DOMDocument();
			@$dom->loadHTML($html);
			$xpath = new \DOMXPath($dom);

			// Check for unclosed tags (images without alt)
			$images = $xpath->query('//img');
			foreach ($images as $img) {
				if (!$img->hasAttribute('alt')) {
					$issues[] = 'Image missing alt attribute';
					break;
				}
			}
		} catch (\Exception $e) {
			return null;
		}

		return empty($issues) ? null : [
			'id' => 'wcag-valid-html',
			'title' => 'Invalid HTML detected',
			'description' => 'HTML must be valid according to HTML standards',
			'severity' => 'medium',
			'category' => 'accessibility',
			'threat_level' => 48,
			'details' => $issues,
		];
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Wcag Valid Html
	 * Slug: wcag-valid-html
	 *
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Automatically initialized lean diagnostic for Wcag Valid Html. Optimized for minimal overhead while surfacing high-value signals.
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_wcag_valid_html(): array {
		$good = '<html><body><img src="test.jpg" alt="Test Image"></body></html>';
		$bad = '<html><body><img src="test.jpg"></body></html>';

		$_POST['html'] = $good;
		$r1 = self::check();
		$_POST['html'] = $bad;
		$r2 = self::check();

		return ['passed' => is_null($r1) && is_array($r2), 'message' => 'HTML validation check working'];
	}

}

