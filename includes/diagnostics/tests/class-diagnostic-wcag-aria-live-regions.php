<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;




class Diagnostic_Wcag_Aria_Live_Regions extends Diagnostic_Base {
	protected static $slug = 'wcag-aria-live-regions';

	protected static $title = 'Wcag Aria Live Regions';

	protected static $description = 'Automatically initialized lean diagnostic for Wcag Aria Live Regions. Optimized for minimal overhead while surfacing high-value signals.';

	protected static $family = 'general';

	protected static $family_label = 'General';

	/**
	 * Get diagnostic ID
	 */
	public static function get_id(): string {
		return 'wcag-aria-live-regions';
	}

	/**
	 * Get diagnostic name
	 */
	public static function get_name(): string {
		return __( 'Are dynamic content announced?', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 */
	public static function get_description(): string {
		return __( 'Are dynamic content announced?. Part of Accessibility & Inclusivity analysis.', 'wpshadow' );
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
		// Implement: Are dynamic content announced? test
		// Smart implementation needed

		return array(); // Stub: full implementation pending
	}

	/**
	 * Get threat level for this finding (0-100)
	 */
	public static function get_threat_level(): int {
		// Threat level based on diagnostic category
		return 58;
	}

	/**
	 * Get KB article URL
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/wcag-aria-live-regions/';
	}

	/**
	 * Get training video URL
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/wcag-aria-live-regions/';
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

			$live_regions = $xpath->query('//*[@aria-live]');
			if ($live_regions->length === 0) {
				$issues[] = 'No live regions defined with aria-live attribute';
			}
		} catch (\Exception $e) {
			return null;
		}

		return empty($issues) ? null : [
			'id' => 'wcag-aria-live-regions',
			'title' => 'Live regions not marked',
			'description' => 'Dynamic content updates should be marked with aria-live',
			'severity' => 'low',
			'category' => 'accessibility',
			'threat_level' => 38,
			'details' => $issues,
		];
	}

	public static function test_live_wcag_aria_live_regions(): array {
		$good = '<html><body><div aria-live="polite">Status</div></body></html>';
		$bad = '<html><body><div>Status</div></body></html>';

		$_POST['html'] = $good;
		$r1 = self::check();
		$_POST['html'] = $bad;
		$r2 = self::check();

		return ['passed' => is_null($r1) && is_array($r2), 'message' => 'Live regions check working'];
	}
	}

