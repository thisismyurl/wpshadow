<?php
/**
 * Mobile Form Validation Feedback
 *
 * Ensures validation errors are clearly visible and accessible on mobile.
 *
 * @package    WPShadow
 * @subpackage Treatments\Forms
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Treatments\Helpers\Treatment_HTML_Helper;
use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Form Validation Feedback
 *
 * Validates that form error messages are clearly visible on mobile,
 * use appropriate colors/contrast, and are accessible to screen readers.
 * WCAG 3.3.1 Level A requirement.
 *
 * @since 0.6093.1200
 */
class Treatment_Mobile_Form_Validation extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-form-validation-feedback';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Form Validation Feedback';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Ensures validation errors are visible on mobile';

	/**
	 * The treatment family.
	 *
	 * @var string
	 */
	protected static $family = 'forms';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Form_Validation' );
	}

	/**
	 * Find form validation issues.
	 *
	 * @since 0.6093.1200
	 * @return array Issues found.
	 */
	private static function find_validation_issues(): array {
		$html = self::get_page_html();
		if ( ! $html ) {
			return array( 'all' => array() );
		}

		$issues = array();

		// Check for error message elements
		$error_checks = self::check_error_messages( $html );
		$issues = array_merge( $issues, $error_checks );

		// Check for aria-invalid usage
		$aria_checks = self::check_aria_invalid( $html );
		$issues = array_merge( $issues, $aria_checks );

		// Check for inline validation
		$inline_checks = self::check_inline_validation( $html );
		$issues = array_merge( $issues, $inline_checks );

		return array( 'all' => $issues );
	}

	/**
	 * Check for error message implementation.
	 *
	 * @since 0.6093.1200
	 * @param  string $html HTML content.
	 * @return array Error message issues.
	 */
	private static function check_error_messages( string $html ): array {
		$issues = array();

		// Check if error messages exist
		$has_error_class = preg_match( '/class\s*=\s*["\'][^"\']*(?:error|invalid|warning)[^"\']*["\']/i', $html );
		$has_role_alert = preg_match( '/role\s*=\s*["\']alert["\']/i', $html );

		if ( ! $has_error_class && ! $has_role_alert ) {
			$issues[] = array(
				'type'  => 'no-error-messages',
				'issue' => 'No error message elements detected in HTML',
			);
		}

		// Check for color-only error indication
		preg_match_all( '/<style[^>]*>(.*?)<\/style>/is', $html, $style_matches );
		$css = implode( "\n", $style_matches[1] ?? array() );

		if ( preg_match( '/\.error\s*{[^}]*color\s*:\s*red/i', $css ) ) {
			if ( ! preg_match( '/\.error\s*{[^}]*(?:border|background|icon)/i', $css ) ) {
				$issues[] = array(
					'type'  => 'color-only-errors',
					'issue' => 'Errors indicated by color only (not accessible)',
				);
			}
		}

		return $issues;
	}

	/**
	 * Check for aria-invalid usage.
	 *
	 * @since 0.6093.1200
	 * @param  string $html HTML content.
	 * @return array ARIA issues.
	 */
	private static function check_aria_invalid( string $html ): array {
		$issues = array();

		// Check for aria-invalid on inputs
		$has_aria_invalid = preg_match( '/aria-invalid\s*=\s*["\']true["\']/i', $html );

		if ( ! $has_aria_invalid ) {
			$issues[] = array(
				'type'  => 'missing-aria-invalid',
				'issue' => 'No aria-invalid attributes detected on form fields',
			);
		}

		// Check for aria-describedby linking to errors
		$has_describedby = preg_match( '/aria-describedby\s*=\s*["\'][^"\']*error[^"\']*["\']/i', $html );

		if ( ! $has_describedby ) {
			$issues[] = array(
				'type'  => 'missing-aria-describedby',
				'issue' => 'Error messages not linked via aria-describedby',
			);
		}

		return $issues;
	}

	/**
	 * Check for inline validation implementation.
	 *
	 * @since 0.6093.1200
	 * @param  string $html HTML content.
	 * @return array Inline validation issues.
	 */
	private static function check_inline_validation( string $html ): array {
		$issues = array();

		// Check for JavaScript validation
		$has_validation_js = preg_match( '/validate|validation|invalid|error/i', $html );

		if ( ! $has_validation_js ) {
			$issues[] = array(
				'type'  => 'no-js-validation',
				'issue' => 'No JavaScript validation detected',
			);
		}

		// Check for HTML5 validation attributes
		$has_required = preg_match( '/<input[^>]*required/i', $html );
		$has_pattern = preg_match( '/<input[^>]*pattern\s*=\s*["\'][^"\']+["\']/i', $html );

		if ( ! $has_required && ! $has_pattern ) {
			$issues[] = array(
				'type'  => 'no-html5-validation',
				'issue' => 'No HTML5 validation attributes (required, pattern) detected',
			);
		}

		return $issues;
	}

	/**
	 * Get page HTML for analysis.
	 *
	 * @since 0.6093.1200
	 * @return string|null HTML content.
	 */
	private static function get_page_html(): ?string {
		return Treatment_HTML_Helper::fetch_homepage_html(
			array(
				'timeout'   => 5,
				'sslverify' => false,
			)
		);
	}
}
