<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;




class Diagnostic_Wcag_Consistent_Id extends Diagnostic_Base {
	protected static $slug = 'wcag-consistent-id';

	protected static $title = 'Wcag Consistent Id';

	protected static $description = 'Automatically initialized lean diagnostic for Wcag Consistent Id. Optimized for minimal overhead while surfacing high-value signals.';

	protected static $family = 'general';

	protected static $family_label = 'General';

	/**
	 * Get diagnostic ID
	 */
	public static function get_id(): string {
		return 'wcag-consistent-id';
	}

	/**
	 * Get diagnostic name
	 */
	public static function get_name(): string {
		return __( 'Are identical components consistent?', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 */
	public static function get_description(): string {
		return __( 'Are identical components consistent?. Part of Accessibility & Inclusivity analysis.', 'wpshadow' );
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
		// Implement: Are identical components consistent? test
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
		return 'https://wpshadow.com/kb/wcag-consistent-id/';
	}

	/**
	 * Get training video URL
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/wcag-consistent-id/';
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

			// Check for duplicate IDs
			$all_ids = [];
			$elements_with_id = $xpath->query('//*[@id]');
			foreach ($elements_with_id as $elem) {
				$id = $elem->getAttribute('id');
				if (in_array($id, $all_ids)) {
					$issues[] = 'Duplicate ID found: ' . $id;
					break;
				}
				$all_ids[] = $id;
			}
		} catch (\Exception $e) {
			return null;
		}

		return empty($issues) ? null : [
			'id' => 'wcag-consistent-id',
			'title' => 'Duplicate element IDs found',
			'description' => 'Element IDs must be unique within the page',
			'severity' => 'high',
			'category' => 'accessibility',
			'threat_level' => 60,
			'details' => $issues,
		];
	}

	public static function test_live_wcag_consistent_id(): array {
		$good = '<html><body><div id="unique1"></div><div id="unique2"></div></body></html>';
		$bad = '<html><body><div id="same"></div><div id="same"></div></body></html>';

		$_POST['html'] = $good;
		$r1 = self::check();
		$_POST['html'] = $bad;
		$r2 = self::check();

		return ['passed' => is_null($r1) && is_array($r2), 'message' => 'ID consistency check working'];
	}
	}

