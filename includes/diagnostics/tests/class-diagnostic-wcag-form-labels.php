<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;


class Diagnostic_Wcag_Form_Labels extends Diagnostic_Base {
	protected static $slug = 'wcag-form-labels';

	protected static $title = 'Wcag Form Labels';

	protected static $description = 'Automatically initialized lean diagnostic for Wcag Form Labels. Optimized for minimal overhead while surfacing high-value signals.';

	protected static $family = 'general';

	protected static $family_label = 'General';

	/**
	 * Get diagnostic ID
	 */
	public static function get_id(): string {
		return 'wcag-form-labels';
	}

	/**
	 * Get diagnostic name
	 */
	public static function get_name(): string {
		return __( 'Are form fields labeled?', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 */
	public static function get_description(): string {
		return __( 'Are form fields labeled?. Part of Accessibility & Inclusivity analysis.', 'wpshadow' );
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
		// Implement: Are form fields labeled? test
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
		return 'https://wpshadow.com/kb/wcag-form-labels/';
	}

	/**
	 * Get training video URL
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/wcag-form-labels/';
	}

	public static function check(): ?array {
		$html = self::get_guardian_html();
		if ( empty( $html ) ) {
			return null;
		}

		$issues = array();
		try {
			$dom = new \DOMDocument();
			@$dom->loadHTML( $html );
			$xpath = new \DOMXPath( $dom );

			// Check for unlabeled form inputs
			$inputs    = $xpath->query( '//input[@type!="hidden"] | //textarea | //select' );
			$unlabeled = 0;

			foreach ( $inputs as $input ) {
				$id        = $input->getAttribute( 'id' );
				$has_label = false;

				if ( $id ) {
					$label     = $xpath->query( '//label[@for="' . $id . '"]' );
					$has_label = $label->length > 0;
				}

				if ( ! $has_label && ! $input->getAttribute( 'aria-label' ) ) {
					++$unlabeled;
				}
			}

			if ( $unlabeled > 0 ) {
				$issues[] = $unlabeled . ' form field(s) missing label';
			}
		} catch ( \Exception $e ) {
			return null;
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'            => 'wcag-form-labels',
			'title'         => 'Form Fields Missing Labels',
			'description'   => 'Screen reader users cannot identify form fields. WCAG 2.1 requires labels for all form inputs.',
			'severity'      => 'high',
			'category'      => 'accessibility',
			'kb_link'       => 'https://wpshadow.com/kb/wcag-form-labels/',
			'training_link' => 'https://wpshadow.com/training/wcag-form-labels/',
			'auto_fixable'  => false,
			'threat_level'  => 75,
			'details'       => $issues,
		);
	}

	/**
	 * Get HTML from Guardian
	 */
	protected static function get_guardian_html(): string {
		if ( isset( $_POST['html'] ) && is_string( $_POST['html'] ) ) {
			return sanitize_text_field( wp_unslash( $_POST['html'] ) );
		}
		return apply_filters( 'wpshadow_diagnostic_html', '', 'wcag-form-labels' );
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Wcag Form Labels
	 * Slug: wcag-form-labels
	 *
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Automatically initialized lean diagnostic for Wcag Form Labels. Optimized for minimal overhead while surfacing high-value signals.
	 *
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_wcag_form_labels(): array {
		// Test with labeled form
		$good_html     = '<html><body><label for="name">Name:</label><input id="name" type="text"></body></html>';
		$_POST['html'] = $good_html;
		$result_good   = self::check();

		// Test with unlabeled form
		$bad_html      = '<html><body><input type="text" placeholder="Name"></body></html>';
		$_POST['html'] = $bad_html;
		$result_bad    = self::check();

		$passed = is_null( $result_good ) && is_array( $result_bad ) && isset( $result_bad['id'] ) && $result_bad['id'] === 'wcag-form-labels';

		return array(
			'passed'  => $passed,
			'message' => 'Form label detection working: ' . ( $passed ? 'PASS' : 'FAIL' ),
		);
	}
}
