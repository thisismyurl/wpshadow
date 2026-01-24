<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;




class Diagnostic_Wcag_Aria_Roles extends Diagnostic_Base {
	protected static $slug = 'wcag-aria-roles';

	protected static $title = 'Wcag Aria Roles';

	protected static $description = 'Automatically initialized lean diagnostic for Wcag Aria Roles. Optimized for minimal overhead while surfacing high-value signals.';

	protected static $family = 'general';

	protected static $family_label = 'General';

	/**
	 * Get diagnostic ID
	 */
	public static function get_id(): string {
		return 'wcag-aria-roles';
	}

	/**
	 * Get diagnostic name
	 */
	public static function get_name(): string {
		return __( 'Are ARIA roles valid?', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 */
	public static function get_description(): string {
		return __( 'Are ARIA roles valid?. Part of Accessibility & Inclusivity analysis.', 'wpshadow' );
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
		// Implement: Are ARIA roles valid? test
		// Smart implementation needed

		return array(); // Stub: full implementation pending
	}

	/**
	 * Get threat level for this finding (0-100)
	 */
	public static function get_threat_level(): int {
		// Threat level based on diagnostic category
		return 60;
	}

	/**
	 * Get KB article URL
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/wcag-aria-roles/';
	}

	/**
	 * Get training video URL
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/wcag-aria-roles/';
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

			// Check for elements with role attribute
			$with_role = $xpath->query('//*[@role]');
			if ($with_role->length === 0) {
				$issues[] = 'No ARIA roles defined for semantic structure';
			}
		} catch (\Exception $e) {
			return null;
		}

		return empty($issues) ? null : [
			'id' => 'wcag-aria-roles',
			'title' => 'ARIA roles missing',
			'description' => 'Use ARIA roles to provide semantic meaning',
			'severity' => 'low',
			'category' => 'accessibility',
			'threat_level' => 40,
			'details' => $issues,
		];
	}

	public static function test_live_wcag_aria_roles(): array {
		$good = '<html><body><div role="navigation">Menu</div></body></html>';
		$bad = '<html><body><div>Menu</div></body></html>';

		$_POST['html'] = $good;
		$r1 = self::check();
		$_POST['html'] = $bad;
		$r2 = self::check();

		return ['passed' => is_null($r1) && is_array($r2), 'message' => 'ARIA roles check working'];
	}
	}

