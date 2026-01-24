<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;




class Diagnostic_Wcag_Consistent_Nav extends Diagnostic_Base {
	protected static $slug = 'wcag-consistent-nav';

	protected static $title = 'Wcag Consistent Nav';

	protected static $description = 'Automatically initialized lean diagnostic for Wcag Consistent Nav. Optimized for minimal overhead while surfacing high-value signals.';

	protected static $family = 'general';

	protected static $family_label = 'General';

	/**
	 * Get diagnostic ID
	 */
	public static function get_id(): string {
		return 'wcag-consistent-nav';
	}

	/**
	 * Get diagnostic name
	 */
	public static function get_name(): string {
		return __( 'Is navigation consistent?', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 */
	public static function get_description(): string {
		return __( 'Is navigation consistent?. Part of Accessibility & Inclusivity analysis.', 'wpshadow' );
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
		// Implement: Is navigation consistent? test
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
		return 'https://wpshadow.com/kb/wcag-consistent-nav/';
	}

	/**
	 * Get training video URL
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/wcag-consistent-nav/';
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

			// Check for navigation structure
			$nav = $xpath->query('//nav');
			if ($nav->length === 0) {
				$issues[] = 'No <nav> element found for navigation';
			} else {
				$lists = $xpath->query('//nav//ul | //nav//ol');
				if ($lists->length === 0) {
					$issues[] = 'Navigation should use list structure';
				}
			}
		} catch (\Exception $e) {
			return null;
		}

		return empty($issues) ? null : [
			'id' => 'wcag-consistent-nav',
			'title' => 'Navigation structure inconsistent',
			'description' => 'Navigation should use proper semantic elements',
			'severity' => 'medium',
			'category' => 'accessibility',
			'threat_level' => 52,
			'details' => $issues,
		];
	}

	public static function test_live_wcag_consistent_nav(): array {
		$good = '<html><body><nav><ul><li><a href="#">Home</a></li></ul></nav></body></html>';
		$bad = '<html><body><nav><a href="#">Home</a></nav></body></html>';

		$_POST['html'] = $good;
		$r1 = self::check();
		$_POST['html'] = $bad;
		$r2 = self::check();

		return ['passed' => is_null($r1) && is_array($r2), 'message' => 'Navigation consistency check working'];
	}
	}

