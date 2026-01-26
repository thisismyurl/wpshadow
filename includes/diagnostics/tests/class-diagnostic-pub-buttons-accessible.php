<?php
/**
 * Diagnostic: Pub Buttons Accessible
 *
 * Checks if buttons in published content have proper accessibility attributes.
 * Ensures buttons have aria-label when text is not descriptive, or have meaningful
 * text content for screen reader users. Part of the publishing workflow to ensure
 * content meets accessibility standards before publication.
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
 * Diagnostic_Pub_Buttons_Accessible Class
 *
 * Checks buttons in published content for proper accessibility attributes.
 * Ensures screen reader users can understand button purposes.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Pub_Buttons_Accessible extends Diagnostic_Base {
	protected static $slug = 'pub-buttons-accessible';

	protected static $title = 'Pub Buttons Accessible';

	protected static $description = 'Automatically initialized lean diagnostic for Pub Buttons Accessible. Optimized for minimal overhead while surfacing high-value signals.';

	protected static $family = 'general';

	protected static $family_label = 'General';

	/**
	 * Get diagnostic ID
	 */
	public static function get_id(): string {
		return 'pub-buttons-accessible';
	}

	/**
	 * Get diagnostic name
	 */
	public static function get_name(): string {
		return __( 'Buttons Are Accessible', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 */
	public static function get_description(): string {
		return __( 'Buttons have proper labels/ARIA?', 'wpshadow' );
	}

	/**
	 * Get diagnostic category
	 */
	public static function get_category(): string {
		return 'content_publishing';
	}

	/**
	 * Get threat level
	 *
	 * @return int 0-100 severity level
	 */
	public static function get_threat_level(): int {
		return 25;
	}

	/**
	 * Run diagnostic test
	 *
	 * @since  1.2601.2148
	 * @return array Diagnostic results
	 */
	public static function run(): array {
		$result = self::check();

		if ( is_null( $result ) ) {
			return array(
				'status'  => 'pass',
				'message' => __( 'All buttons have proper accessibility labels', 'wpshadow' ),
				'data'    => array(),
			);
		}

		return array(
			'status'  => 'fail',
			'message' => $result['description'],
			'data'    => $result,
		);
	}

	/**
	 * Get KB article URL
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/pub-buttons-accessible';
	}

	/**
	 * Get training video URL
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/category-content-publishing';
	}

	/**
	 * Get HTML content from Guardian for analysis.
	 *
	 * @since  1.2601.2148
	 * @return string HTML content or empty string.
	 */
	protected static function get_guardian_html(): string {
		// phpcs:disable WordPress.Security.NonceVerification.Missing -- Guardian provides HTML for analysis, not form submission.
		if ( isset( $_POST['html'] ) && is_string( $_POST['html'] ) ) {
			// Use wp_kses_post to allow HTML tags while sanitizing.
			return wp_kses_post( wp_unslash( $_POST['html'] ) );
		}
		// phpcs:enable WordPress.Security.NonceVerification.Missing
		return '';
	}

	/**
	 * Run the diagnostic check.
	 *
	 * Checks if buttons in published content have proper accessibility attributes:
	 * - Buttons should have aria-label when text is not descriptive
	 * - Buttons should have meaningful text content
	 * - No empty buttons without ARIA labels
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
			// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- Suppressing warnings for malformed HTML is expected.
			@$dom->loadHTML( $html, LIBXML_NOERROR | LIBXML_NOWARNING );
			$xpath = new \DOMXPath( $dom );

			// Check button elements.
			$buttons           = $xpath->query( '//button' );
			$problematic_count = 0;

			foreach ( $buttons as $button ) {
				$has_aria_label      = $button->hasAttribute( 'aria-label' ) && trim( $button->getAttribute( 'aria-label' ) ) !== '';
				$has_aria_labelledby = $button->hasAttribute( 'aria-labelledby' );
				// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- textContent is a DOM property.
				$text_content = trim( $button->textContent );

				// Check if button has some form of accessible label.
				if ( ! $has_aria_label && ! $has_aria_labelledby && empty( $text_content ) ) {
					++$problematic_count;
				} elseif ( ! $has_aria_label && ! empty( $text_content ) ) {
					// Check if text is non-descriptive (single character or icon-like).
					if ( strlen( $text_content ) <= 2 ) {
						++$problematic_count;
					}
				}
			}

			// Check input buttons.
			$input_buttons = $xpath->query( '//input[@type="button" or @type="submit"]' );
			foreach ( $input_buttons as $input ) {
				$has_aria_label = $input->hasAttribute( 'aria-label' ) && trim( $input->getAttribute( 'aria-label' ) ) !== '';
				$has_value      = $input->hasAttribute( 'value' ) && trim( $input->getAttribute( 'value' ) ) !== '';

				if ( ! $has_aria_label && ! $has_value ) {
					++$problematic_count;
				}
			}

			if ( $problematic_count > 0 ) {
				$issues[] = sprintf(
					/* translators: %d: number of buttons with accessibility issues */
					_n(
						'%d button found without proper accessible label',
						'%d buttons found without proper accessible labels',
						$problematic_count,
						'wpshadow'
					),
					$problematic_count
				);
			}
		} catch ( \Exception $e ) {
			// If parsing fails, return null (no finding).
			return null;
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => 'pub-buttons-accessible',
			'title'        => __( 'Buttons Missing Accessible Labels', 'wpshadow' ),
			'description'  => __( 'Buttons should have proper aria-label attributes or descriptive text content for screen reader users', 'wpshadow' ),
			'severity'     => 'medium',
			'category'     => 'accessibility',
			'threat_level' => 25,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/pub-buttons-accessible',
			'details'      => $issues,
		);
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Pub Buttons Accessible
	 * Slug: pub-buttons-accessible
	 *
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Checks if buttons have proper accessibility labels (aria-label or descriptive text)
	 *
	 * @since  1.2601.2148
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_pub_buttons_accessible(): array {
		// Test with good HTML (buttons with proper labels).
		$good_html = '<html><body>
			<button aria-label="' . esc_attr__( 'Close dialog', 'wpshadow' ) . '">X</button>
			<button>' . esc_html__( 'Submit Form', 'wpshadow' ) . '</button>
			<input type="submit" value="' . esc_attr__( 'Save Changes', 'wpshadow' ) . '" />
		</body></html>';

		$_POST['html'] = $good_html;
		$result_good   = self::check();

		// Test with bad HTML (buttons without proper labels).
		$bad_html = '<html><body>
			<button></button>
			<button>X</button>
			<input type="submit" />
		</body></html>';

		$_POST['html'] = $bad_html;
		$result_bad    = self::check();

		// Clean up.
		unset( $_POST['html'] );

		// Test passes if good HTML returns null and bad HTML returns array.
		$passed = is_null( $result_good ) && is_array( $result_bad );

		return array(
			'passed'  => $passed,
			'message' => $passed
				? __( 'Button accessibility check working correctly', 'wpshadow' )
				: __( 'Button accessibility check failed validation', 'wpshadow' ),
		);
	}
}
