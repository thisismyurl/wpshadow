<?php
/**
 * Mobile Form Validation Feedback
 *
 * Ensures validation errors are clearly visible and accessible on mobile.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Forms
 * @since      1.602.1445
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Diagnostics\Helpers\Diagnostic_HTML_Helper;
use WPShadow\Core\Diagnostic_Base;

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
 * @since 1.602.1445
 */
class Diagnostic_Mobile_Form_Validation extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-form-validation-feedback';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Form Validation Feedback';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Ensures validation errors are visible on mobile';

	/**
	 * The diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'forms';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.602.1445
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = self::find_validation_issues();

		if ( empty( $issues['all'] ) ) {
			return null;
		}

		return array(
			'id'              => self::$slug,
			'title'           => self::$title,
			'description'     => sprintf(
				/* translators: %d: number of validation issues */
				__( 'Found %d form validation accessibility issues', 'wpshadow' ),
				count( $issues['all'] )
			),
			'severity'        => 'high',
			'threat_level'    => 65,
			'issues'          => $issues['all'],
			'wcag_violation'  => '3.3.1 Error Identification (Level A)',
			'user_impact'     => __( 'Users cannot see or understand form errors on mobile', 'wpshadow' ),
			'auto_fixable'    => false,
			'kb_link'         => 'https://wpshadow.com/kb/form-validation',
		);
	}

	/**
	 * Find form validation issues.
	 *
	 * @since  1.602.1445
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
	 * @since  1.602.1445
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
	 * @since  1.602.1445
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
	 * @since  1.602.1445
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
	 * @since  1.602.1445
	 * @return string|null HTML content.
	 */
	private static function get_page_html(): ?string {
		return Diagnostic_HTML_Helper::fetch_homepage_html(
			array(
				'timeout'   => 5,
				'sslverify' => false,
			)
		);
	}
}
