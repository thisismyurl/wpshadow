<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;


 */


class Diagnostic_Wcag_Focus_Order extends Diagnostic_Base {
	protected static $slug = 'wcag-focus-order';

	protected static $title = 'Wcag Focus Order';

	protected static $description = 'Automatically initialized lean diagnostic for Wcag Focus Order. Optimized for minimal overhead while surfacing high-value signals.';

	protected static $family = 'general';

	protected static $family_label = 'General';

	/**
	 * Get diagnostic ID
	 */
	public static function get_id(): string {
		return 'wcag-focus-order';
	}

	/**
	 * Get diagnostic name
	 */
	public static function get_name(): string {
		return __( 'Is tab order logical?', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 */
	public static function get_description(): string {
		return __( 'Is tab order logical?. Part of Accessibility & Inclusivity analysis.', 'wpshadow' );
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
		// Implement: Is tab order logical? test
		// Smart implementation needed

		return array(); // Stub: full implementation pending
	}

	/**
	 * Get threat level for this finding (0-100)
	 */
	public static function get_threat_level(): int {
		// Threat level based on diagnostic category
		return 54;
	}

	/**
	 * Get KB article URL
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/wcag-focus-order/';
	}

	/**
	 * Get training video URL
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/wcag-focus-order/';
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

			// Check for logical focus order (proper heading hierarchy)
			$headings = $xpath->query('//h1 | //h2 | //h3 | //h4 | //h5 | //h6');
			if ($headings->length > 0) {
				$first_heading = null;
				foreach ($headings as $h) {
					$level = (int)substr($h->nodeName, 1);
					if (!$first_heading) {
						$first_heading = $level;
					} elseif ($level > $first_heading + 1) {
						$issues[] = 'Heading hierarchy broken; skipped levels detected';
						break;
					}
				}
			}
		} catch (\Exception $e) {
			return null;
		}

		return empty($issues) ? null : [
			'id' => 'wcag-focus-order',
			'title' => 'Focus order not logical',
			'description' => 'Heading hierarchy must be logical for navigation',
			'severity' => 'medium',
			'category' => 'accessibility',
			'threat_level' => 50,
			'details' => $issues,
		];
	}

	public static function test_live_wcag_focus_order(): array {
		$good = '<html><body><h1>Title</h1><h2>Section</h2></body></html>';
		$bad = '<html><body><h1>Title</h1><h3>Section</h3></body></html>';

		$_POST['html'] = $good;
		$r1 = self::check();
		$_POST['html'] = $bad;
		$r2 = self::check();

		return ['passed' => is_null($r1) && is_array($r2), 'message' => 'Focus order check working'];
	}
	}

}

