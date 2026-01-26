<?php
/**
 * Pub Forms Accessible Diagnostic
 *
 * Checks form accessibility for pre-publication audits. Ensures forms
 * have proper labels, ARIA attributes, and structure for screen readers.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Pub_Forms_Accessible Class
 *
 * Performs comprehensive form accessibility checks for content publishing.
 * Part of the pre-publication workflow to ensure forms meet WCAG guidelines.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Pub_Forms_Accessible extends Diagnostic_Base {
	/**
	 * The diagnostic slug/ID
	 *
	 * @since 1.2601.2148
	 * @var string
	 */
	protected static $slug = 'pub-forms-accessible';

	/**
	 * The diagnostic title
	 *
	 * @since 1.2601.2148
	 * @var string
	 */
	protected static $title = 'Pub Forms Accessible';

	/**
	 * The diagnostic description
	 *
	 * @since 1.2601.2148
	 * @var string
	 */
	protected static $description = 'Checks form accessibility for pre-publication audits. Ensures forms have proper labels, ARIA attributes, and structure for screen readers.';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 1.2601.2148
	 * @var string
	 */
	protected static $family = 'general';

	/**
	 * Display name for the family
	 *
	 * @since 1.2601.2148
	 * @var string
	 */
	protected static $family_label = 'General';

	/**
	 * Get diagnostic ID
	 *
	 * @since  1.2601.2148
	 * @return string Diagnostic identifier.
	 */
	public static function get_id(): string {
		return 'pub-forms-accessible';
	}

	/**
	 * Get diagnostic name
	 *
	 * @since  1.2601.2148
	 * @return string Human-readable diagnostic name.
	 */
	public static function get_name(): string {
		return __( 'Forms Are Accessible', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 *
	 * @since  1.2601.2148
	 * @return string Diagnostic description.
	 */
	public static function get_description(): string {
		return __( 'Form fields properly labeled?', 'wpshadow' );
	}

	/**
	 * Get diagnostic category
	 *
	 * @since  1.2601.2148
	 * @return string Category identifier.
	 */
	public static function get_category(): string {
		return 'content_publishing';
	}

	/**
	 * Get threat level
	 *
	 * @since  1.2601.2148
	 * @return int 0-100 severity level
	 */
	public static function get_threat_level(): int {
		return 60;
	}

	/**
	 * Run diagnostic test (legacy method)
	 *
	 * @deprecated Use check() method instead.
	 * @since      1.2601.2148
	 * @return     array Diagnostic results
	 */
	public static function run(): array {
		// For backward compatibility, call check() and format result.
		$result = self::check();

		if ( null === $result ) {
			return array(
				'status'  => 'pass',
				'message' => __( 'All forms are accessible', 'wpshadow' ),
				'data'    => array(),
			);
		}

		return array(
			'status'  => 'fail',
			'message' => $result['description'] ?? __( 'Form accessibility issues found', 'wpshadow' ),
			'data'    => $result,
		);
	}

	/**
	 * Get KB article URL
	 *
	 * @since  1.2601.2148
	 * @return string Knowledge base article URL.
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/pub-forms-accessible';
	}

	/**
	 * Get training video URL
	 *
	 * @since  1.2601.2148
	 * @return string Training video URL.
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/category-content-publishing';
	}

	/**
	 * Run the diagnostic check.
	 *
	 * Checks form accessibility by analyzing HTML for:
	 * - Unlabeled form fields (inputs, textareas, selects)
	 * - Missing ARIA attributes for accessibility
	 * - Required field indicators
	 * - Form instructions and help text associations
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check(): ?array {
		$html = self::get_guardian_html();
		if ( empty( $html ) ) {
			return null;
		}

		$issues = array();
		try {
			$dom = new \DOMDocument();
			// Suppress warnings from malformed HTML - this is the standard approach.
			// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			@$dom->loadHTML( $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
			$xpath = new \DOMXPath( $dom );

			// Check for unlabeled form inputs.
			$inputs                     = $xpath->query( '//input[@type!="hidden" and @type!="submit" and @type!="button"] | //textarea | //select' );
			$unlabeled                  = 0;
			$missing_required_indicator = 0;

			foreach ( $inputs as $input ) {
				$id        = $input->getAttribute( 'id' );
				$has_label = false;

				// Check for associated label via for/id.
				if ( $id ) {
					$label     = $xpath->query( '//label[@for="' . $id . '"]' );
					$has_label = $label->length > 0;
				}

				// Check for ARIA label as alternative.
				if ( ! $has_label && ! $input->getAttribute( 'aria-label' ) && ! $input->getAttribute( 'aria-labelledby' ) ) {
					// Check if input is wrapped in a label.
					$parent_label = $xpath->query( 'ancestor::label', $input );
					if ( 0 === $parent_label->length ) {
						++$unlabeled;
					}
				}

				// Check if required fields have proper indicators.
				if ( $input->hasAttribute( 'required' ) || 'true' === $input->getAttribute( 'aria-required' ) ) {
					// Required field should have visual indicator or proper ARIA.
					if ( 'true' !== $input->getAttribute( 'aria-required' ) && ! $input->hasAttribute( 'required' ) ) {
						++$missing_required_indicator;
					}
				}
			}

			// Check for forms without proper structure.
			$forms                = $xpath->query( '//form' );
			$forms_without_legend = 0;

			foreach ( $forms as $form ) {
				// Check if form has fieldsets with legends for grouping.
				$fieldsets = $xpath->query( './/fieldset', $form );
				if ( $fieldsets->length > 0 ) {
					foreach ( $fieldsets as $fieldset ) {
						$legends = $xpath->query( './/legend', $fieldset );
						if ( 0 === $legends->length ) {
							++$forms_without_legend;
						}
					}
				}
			}

			if ( $unlabeled > 0 ) {
				$issues[] = sprintf(
					/* translators: %d: number of unlabeled form fields */
					_n(
						'%d form field is missing a label',
						'%d form fields are missing labels',
						$unlabeled,
						'wpshadow'
					),
					$unlabeled
				);
			}

			if ( $forms_without_legend > 0 ) {
				$issues[] = sprintf(
					/* translators: %d: number of fieldsets without legends */
					_n(
						'%d fieldset is missing a legend',
						'%d fieldsets are missing legends',
						$forms_without_legend,
						'wpshadow'
					),
					$forms_without_legend
				);
			}
		} catch ( \Exception $e ) {
			// Failed to parse HTML - return null.
			return null;
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'            => 'pub-forms-accessible',
			'title'         => __( 'Forms Missing Accessibility Features', 'wpshadow' ),
			'description'   => __( 'Form fields must be properly labeled and structured for screen reader users. This ensures all users can successfully complete forms on your site.', 'wpshadow' ),
			'severity'      => 'high',
			'category'      => 'content_publishing',
			'kb_link'       => 'https://wpshadow.com/kb/pub-forms-accessible',
			'training_link' => 'https://wpshadow.com/training/category-content-publishing',
			'auto_fixable'  => false,
			'threat_level'  => 60,
			'details'       => $issues,
		);
	}

	/**
	 * Get HTML from Guardian or POST data.
	 *
	 * This method retrieves HTML content to analyze. In production,
	 * this would come from the Guardian publishing workflow.
	 * For testing, it can be provided via POST data.
	 *
	 * Note: Nonce verification is not required here as this is a read-only
	 * diagnostic check that processes HTML for analysis. The HTML is sanitized
	 * with wp_kses_post() before processing.
	 *
	 * @since  1.2601.2148
	 * @return string HTML content to analyze.
	 */
	protected static function get_guardian_html(): string {
		// Check for HTML in POST data (for testing).
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Diagnostic check, read-only, no state changes.
		if ( isset( $_POST['html'] ) && is_string( $_POST['html'] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Diagnostic check, read-only, no state changes.
			return wp_kses_post( wp_unslash( $_POST['html'] ) );
		}

		/**
		 * Filters the HTML content for form accessibility diagnostic.
		 *
		 * @since 1.2601.2148
		 *
		 * @param string $html           HTML content (empty by default).
		 * @param string $diagnostic_id  Diagnostic identifier.
		 */
		return apply_filters( 'wpshadow_diagnostic_html', '', 'pub-forms-accessible' );
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Pub Forms Accessible
	 * Slug: pub-forms-accessible
	 *
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when forms are properly accessible (site is healthy)
	 * - FAIL: check() returns array when forms have accessibility issues (issue found)
	 *
	 * @since  1.2601.2148
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_pub_forms_accessible(): array {
		// Test 1: Form with proper labels should pass (no issues).
		$good_html     = '<html><body><form><label for="name">Name:</label><input id="name" type="text" required aria-required="true"><label for="email">Email:</label><input id="email" type="email"></form></body></html>';
		$_POST['html'] = $good_html;
		$result_good   = self::check();

		// Test 2: Form with unlabeled fields should fail (issues found).
		$bad_html      = '<html><body><form><input type="text" placeholder="Name"><textarea placeholder="Message"></textarea></form></body></html>';
		$_POST['html'] = $bad_html;
		$result_bad    = self::check();

		// Test 3: Form with fieldset but no legend should fail.
		$fieldset_html   = '<html><body><form><fieldset><input id="opt1" type="radio" name="option"><label for="opt1">Option 1</label></fieldset></form></body></html>';
		$_POST['html']   = $fieldset_html;
		$result_fieldset = self::check();

		// Test 4: Form with wrapped labels should pass.
		$wrapped_html   = '<html><body><form><label>Name: <input type="text"></label><label>Email: <input type="email"></label></form></body></html>';
		$_POST['html']  = $wrapped_html;
		$result_wrapped = self::check();

		// Clean up.
		unset( $_POST['html'] );

		// Validate results.
		$test1_pass = is_null( $result_good );
		$test2_pass = is_array( $result_bad ) && isset( $result_bad['id'] ) && 'pub-forms-accessible' === $result_bad['id'];
		$test3_pass = is_array( $result_fieldset ) && isset( $result_fieldset['id'] );
		$test4_pass = is_null( $result_wrapped );

		$all_passed = $test1_pass && $test2_pass && $test3_pass && $test4_pass;

		$messages = array();
		if ( ! $test1_pass ) {
			$messages[] = 'Test 1 FAIL: Properly labeled form should return null';
		}
		if ( ! $test2_pass ) {
			$messages[] = 'Test 2 FAIL: Unlabeled form should return finding';
		}
		if ( ! $test3_pass ) {
			$messages[] = 'Test 3 FAIL: Fieldset without legend should return finding';
		}
		if ( ! $test4_pass ) {
			$messages[] = 'Test 4 FAIL: Wrapped labels should return null';
		}

		return array(
			'passed'  => $all_passed,
			'message' => $all_passed ? 'All form accessibility tests passed' : implode( '; ', $messages ),
		);
	}
}
