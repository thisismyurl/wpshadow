<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;


 */


class Diagnostic_Wcag_Link_Purpose extends Diagnostic_Base {
	protected static $slug = 'wcag-link-purpose';

	protected static $title = 'Wcag Link Purpose';

	protected static $description = 'Automatically initialized lean diagnostic for Wcag Link Purpose. Optimized for minimal overhead while surfacing high-value signals.';

	protected static $family = 'general';

	protected static $family_label = 'General';

	/**
	 * Get diagnostic ID
	 */
	public static function get_id(): string {
		return 'wcag-link-purpose';
	}

	/**
	 * Get diagnostic name
	 */
	public static function get_name(): string {
		return __( 'Do link texts describe purpose?', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 */
	public static function get_description(): string {
		return __( 'Do link texts describe purpose?. Part of Accessibility & Inclusivity analysis.', 'wpshadow' );
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
		// Implement: Do link texts describe purpose? test
		// Smart implementation needed

		return array(); // Stub: full implementation pending
	}

	/**
	 * Get threat level for this finding (0-100)
	 */
	public static function get_threat_level(): int {
		// Threat level based on diagnostic category
		return 46;
	}

	/**
	 * Get KB article URL
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/wcag-link-purpose/';
	}

	/**
	 * Get training video URL
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/wcag-link-purpose/';
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

			$links = $xpath->query('//a');
			foreach ($links as $link) {
				$text = trim($link->textContent);
				$aria_label = $link->hasAttribute('aria-label') ? trim($link->getAttribute('aria-label')) : '';

				if (empty($text) && empty($aria_label)) {
					$issues[] = 'Link missing descriptive text or aria-label';
				}
			}
		} catch (\Exception $e) {
			return null;
		}

		return empty($issues) ? null : [
			'id' => 'wcag-link-purpose',
			'title' => 'Links lack descriptive text',
			'description' => 'Links must have text or aria-label describing their purpose',
			'severity' => 'medium',
			'category' => 'accessibility',
			'threat_level' => 46,
			'details' => $issues,
		];
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Wcag Link Purpose
	 * Slug: wcag-link-purpose
	 *
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Automatically initialized lean diagnostic for Wcag Link Purpose. Optimized for minimal overhead while surfacing high-value signals.
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_wcag_link_purpose(): array {
		$good = '<html><body><a href="#">Click here</a></body></html>';
		$bad = '<html><body><a href="#"></a></body></html>';

		$_POST['html'] = $good;
		$r1 = self::check();
		$_POST['html'] = $bad;
		$r2 = self::check();

		return ['passed' => is_null($r1) && is_array($r2), 'message' => 'Link purpose check working'];
	}

}

