<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;


 */


class Diagnostic_Wcag_Aria_Attributes extends Diagnostic_Base {
	protected static $slug = 'wcag-aria-attributes';

	protected static $title = 'Wcag Aria Attributes';

	protected static $description = 'Automatically initialized lean diagnostic for Wcag Aria Attributes. Optimized for minimal overhead while surfacing high-value signals.';

	protected static $family = 'general';

	protected static $family_label = 'General';

	/**
	 * Get diagnostic ID
	 */
	public static function get_id(): string {
		return 'wcag-aria-attributes';
	}

	/**
	 * Get diagnostic name
	 */
	public static function get_name(): string {
		return __( 'Are ARIA attributes valid?', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 */
	public static function get_description(): string {
		return __( 'Are ARIA attributes valid?. Part of Accessibility & Inclusivity analysis.', 'wpshadow' );
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
		// Implement: Are ARIA attributes valid? test
		// Smart implementation needed

		return array(); // Stub: full implementation pending
	}

	/**
	 * Get threat level for this finding (0-100)
	 */
	public static function get_threat_level(): int {
		// Threat level based on diagnostic category
		return 49;
	}

	/**
	 * Get KB article URL
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/wcag-aria-attributes/';
	}

	/**
	 * Get training video URL
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/wcag-aria-attributes/';
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

			// Check interactive elements for ARIA attributes
			$buttons = $xpath->query('//button');
			$inputs = $xpath->query('//input[@type="button"]');
			$all_interactive = $buttons->length + $inputs->length;

			if ($all_interactive > 0) {
				$with_aria = 0;
				foreach ($buttons as $btn) {
					if ($btn->hasAttribute('aria-label') || $btn->hasAttribute('aria-describedby')) {
						$with_aria++;
					}
				}
				if ($with_aria < $all_interactive / 2) {
					$issues[] = 'Interactive elements missing ARIA attributes';
				}
			}
		} catch (\Exception $e) {
			return null;
		}

		return empty($issues) ? null : [
			'id' => 'wcag-aria-attributes',
			'title' => 'ARIA attributes missing',
			'description' => 'Interactive elements should have proper ARIA attributes',
			'severity' => 'medium',
			'category' => 'accessibility',
			'threat_level' => 54,
			'details' => $issues,
		];
	}

	public static function test_live_wcag_aria_attributes(): array {
		$good = '<html><body><button aria-label="Close">X</button></body></html>';
		$bad = '<html><body><button>X</button></body></html>';

		$_POST['html'] = $good;
		$r1 = self::check();
		$_POST['html'] = $bad;
		$r2 = self::check();

		return ['passed' => is_null($r1) && is_array($r2), 'message' => 'ARIA attributes check working'];
	}
	}

}

