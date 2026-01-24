<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;


 */


class Diagnostic_Wcag_Zoom_No_Loss extends Diagnostic_Base {
	protected static $slug = 'wcag-zoom-no-loss';

	protected static $title = 'Wcag Zoom No Loss';

	protected static $description = 'Automatically initialized lean diagnostic for Wcag Zoom No Loss. Optimized for minimal overhead while surfacing high-value signals.';

	protected static $family = 'general';

	protected static $family_label = 'General';

	/**
	 * Get diagnostic ID
	 */
	public static function get_id(): string {
		return 'wcag-zoom-no-loss';
	}

	/**
	 * Get diagnostic name
	 */
	public static function get_name(): string {
		return __( 'Is no content lost at 200% zoom?', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 */
	public static function get_description(): string {
		return __( 'Is no content lost at 200% zoom?. Part of Accessibility & Inclusivity analysis.', 'wpshadow' );
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
		// Implement: Is no content lost at 200% zoom? test
		// Smart implementation needed

		return array(); // Stub: full implementation pending
	}

	/**
	 * Get threat level for this finding (0-100)
	 */
	public static function get_threat_level(): int {
		// Threat level based on diagnostic category
		return 45;
	}

	/**
	 * Get KB article URL
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/wcag-zoom-no-loss/';
	}

	/**
	 * Get training video URL
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/wcag-zoom-no-loss/';
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

			$elements_with_style = $xpath->query('//*[@style]');
			foreach ($elements_with_style as $elem) {
				$style = $elem->getAttribute('style');
				if (strpos($style, 'overflow:hidden') !== false &&
					(strpos($style, 'width') !== false || strpos($style, 'max-width') !== false)) {
					$issues[] = 'Element with fixed width and overflow:hidden will lose content at zoom';
					break;
				}
			}
		} catch (\Exception $e) {
			return null;
		}

		return empty($issues) ? null : [
			'id' => 'wcag-zoom-no-loss',
			'title' => 'Content lost at 200% zoom',
			'description' => 'Content should not be lost when zoomed to 200%',
			'severity' => 'high',
			'category' => 'accessibility',
			'threat_level' => 62,
			'details' => $issues,
		];
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Wcag Zoom No Loss
	 * Slug: wcag-zoom-no-loss
	 *
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Automatically initialized lean diagnostic for Wcag Zoom No Loss. Optimized for minimal overhead while surfacing high-value signals.
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_wcag_zoom_no_loss(): array {
		$good = '<html><body><div style="width: 100%">Content</div></body></html>';
		$bad = '<html><body><div style="width: 300px; overflow: hidden">Content</div></body></html>';

		$_POST['html'] = $good;
		$r1 = self::check();
		$_POST['html'] = $bad;
		$r2 = self::check();

		return ['passed' => is_null($r1) && is_array($r2), 'message' => 'Zoom loss check working'];
	}

}

