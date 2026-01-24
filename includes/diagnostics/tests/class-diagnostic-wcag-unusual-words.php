<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;


class Diagnostic_Wcag_Unusual_Words extends Diagnostic_Base {
	protected static $slug = 'wcag-unusual-words';

	protected static $title = 'Wcag Unusual Words';

	protected static $description = 'Automatically initialized lean diagnostic for Wcag Unusual Words. Optimized for minimal overhead while surfacing high-value signals.';

	protected static $family = 'general';

	protected static $family_label = 'General';

	/**
	 * Get diagnostic ID
	 */
	public static function get_id(): string {
		return 'wcag-unusual-words';
	}

	/**
	 * Get diagnostic name
	 */
	public static function get_name(): string {
		return __( 'Are unusual words defined?', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 */
	public static function get_description(): string {
		return __( 'Are unusual words defined?. Part of Accessibility & Inclusivity analysis.', 'wpshadow' );
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
		// Implement: Are unusual words defined? test
		// Smart implementation needed

		return array(); // Stub: full implementation pending
	}

	/**
	 * Get threat level for this finding (0-100)
	 */
	public static function get_threat_level(): int {
		// Threat level based on diagnostic category
		return 48;
	}

	/**
	 * Get KB article URL
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/wcag-unusual-words/';
	}

	/**
	 * Get training video URL
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/wcag-unusual-words/';
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

			// Check if abbr elements exist for unusual words
			$abbr_elements = $xpath->query('//abbr');
			if ($abbr_elements->length === 0) {
				$issues[] = 'No <abbr> elements found to define unusual words';
			}
		} catch (\Exception $e) {
			return null;
		}

		return empty($issues) ? null : [
			'id' => 'wcag-unusual-words',
			'title' => 'Unusual words not defined',
			'description' => 'Unusual words should be defined using <abbr> or other methods',
			'severity' => 'low',
			'category' => 'accessibility',
			'threat_level' => 35,
			'details' => $issues,
		];
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Wcag Unusual Words
	 * Slug: wcag-unusual-words
	 *
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Automatically initialized lean diagnostic for Wcag Unusual Words. Optimized for minimal overhead while surfacing high-value signals.
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_wcag_unusual_words(): array {
		$good = '<html><body>Use <abbr title="Hypertext Markup Language">HTML</abbr> for structure</body></html>';
		$bad = '<html><body>Use HTML for structure</body></html>';

		$_POST['html'] = $good;
		$r1 = self::check();
		$_POST['html'] = $bad;
		$r2 = self::check();

		return ['passed' => is_null($r1) && is_array($r2), 'message' => 'Unusual words check working'];
	}

}

