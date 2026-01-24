<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;




class Diagnostic_Wcag_Abbreviations extends Diagnostic_Base {
	protected static $slug = 'wcag-abbreviations';

	protected static $title = 'Wcag Abbreviations';

	protected static $description = 'Automatically initialized lean diagnostic for Wcag Abbreviations. Optimized for minimal overhead while surfacing high-value signals.';

	protected static $family = 'general';

	protected static $family_label = 'General';

	/**
	 * Get diagnostic ID
	 */
	public static function get_id(): string {
		return 'wcag-abbreviations';
	}

	/**
	 * Get diagnostic name
	 */
	public static function get_name(): string {
		return __( 'Are abbreviations explained?', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 */
	public static function get_description(): string {
		return __( 'Are abbreviations explained?. Part of Accessibility & Inclusivity analysis.', 'wpshadow' );
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
		// Implement: Are abbreviations explained? test
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
		return 'https://wpshadow.com/kb/wcag-abbreviations/';
	}

	/**
	 * Get training video URL
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/wcag-abbreviations/';
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

			$abbrs = $xpath->query('//abbr');
			if ($abbrs->length === 0) {
				$issues[] = 'No abbreviations found or not marked with <abbr> tag';
			} else {
				foreach ($abbrs as $abbr) {
					if (!$abbr->hasAttribute('title')) {
						$issues[] = 'Abbreviation "' . trim($abbr->textContent) . '" missing title attribute';
						break;
					}
				}
			}
		} catch (\Exception $e) {
			return null;
		}

		return empty($issues) ? null : [
			'id' => 'wcag-abbreviations',
			'title' => 'Abbreviations not explained',
			'description' => 'Abbreviations must be explained with title attribute or definition',
			'severity' => 'low',
			'category' => 'accessibility',
			'threat_level' => 49,
			'details' => $issues,
		];
	}

	public static function test_live_wcag_abbreviations(): array {
		$good = '<html><body>Use <abbr title="Hyper Text Markup Language">HTML</abbr></body></html>';
		$bad = '<html><body>Use <abbr>HTML</abbr></body></html>';

		$_POST['html'] = $good;
		$r1 = self::check();
		$_POST['html'] = $bad;
		$r2 = self::check();

		return ['passed' => is_null($r1) && is_array($r2), 'message' => 'Abbreviations check working'];
	}
	}

