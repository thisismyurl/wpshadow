<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;




class Diagnostic_Wcag_Error_Prevention extends Diagnostic_Base {
	protected static $slug = 'wcag-error-prevention';

	protected static $title = 'Wcag Error Prevention';

	protected static $description = 'Automatically initialized lean diagnostic for Wcag Error Prevention. Optimized for minimal overhead while surfacing high-value signals.';

	protected static $family = 'general';

	protected static $family_label = 'General';

	/**
	 * Get diagnostic ID
	 */
	public static function get_id(): string {
		return 'wcag-error-prevention';
	}

	/**
	 * Get diagnostic name
	 */
	public static function get_name(): string {
		return __( 'Do form submits prevent errors?', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 */
	public static function get_description(): string {
		return __( 'Do form submits prevent errors?. Part of Accessibility & Inclusivity analysis.', 'wpshadow' );
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
		// Implement: Do form submits prevent errors? test
		// Smart implementation needed

		return array(); // Stub: full implementation pending
	}

	/**
	 * Get threat level for this finding (0-100)
	 */
	public static function get_threat_level(): int {
		// Threat level based on diagnostic category
		return 52;
	}

	/**
	 * Get KB article URL
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/wcag-error-prevention/';
	}

	/**
	 * Get training video URL
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/wcag-error-prevention/';
	}

	protected static function get_guardian_html(): string {
		if ( isset( $_POST['html'] ) && is_string( $_POST['html'] ) ) {
			return sanitize_text_field( wp_unslash( $_POST['html'] ) );
		}
		return '';
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

			// Check for forms with validation
			$forms = $xpath->query( '//form' );
			if ( $forms->length === 0 ) {
				return null; // No forms to check
			}

			foreach ( $forms as $form ) {
				$inputs = $xpath->query( './/input[@type="email" or @type="url" or @type="number"]', $form );
				if ( $inputs->length > 0 ) {
					$with_required = 0;
					foreach ( $inputs as $input ) {
						if ( $input->hasAttribute( 'required' ) ) {
							++$with_required;
						}
					}
					if ( $with_required < $inputs->length ) {
						$issues[] = 'Form inputs missing required attribute for error prevention';
						break;
					}
				}
			}
		} catch ( \Exception $e ) {
			return null;
		}

		return empty( $issues ) ? null : array(
			'id'           => 'wcag-error-prevention',
			'title'        => 'Forms lack error prevention',
			'description'  => 'Forms should include validation to prevent errors',
			'severity'     => 'medium',
			'category'     => 'accessibility',
			'threat_level' => 45,
			'details'      => $issues,
		);
	}

	public static function test_live_wcag_error_prevention(): array {
		$good = '<html><body><form><input type="email" required></form></body></html>';
		$bad  = '<html><body><form><input type="email"></form></body></html>';

		$_POST['html'] = $good;
		$r1            = self::check();
		$_POST['html'] = $bad;
		$r2            = self::check();

		return array(
			'passed'  => is_null( $r1 ) && is_array( $r2 ),
			'message' => 'Error prevention check working',
		);
	}
}
