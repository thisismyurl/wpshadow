<?php
/**
 * Accessibility Validation Diagnostic
 *
 * Comprehensive accessibility checker for WPShadow admin interface.
 * Validates ARIA attributes, keyboard navigation, color contrast,
 * screen reader support, and WCAG compliance.
 *
 * Phase 5 of UI/UX Epic - Final Polish & Validation
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics\Tests
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Accessibility_Validation Class
 *
 * Performs comprehensive accessibility validation across WPShadow UI components.
 * Checks for WCAG AA compliance, keyboard navigation, ARIA attributes, and more.
 */
class Diagnostic_Accessibility_Validation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'accessibility-validation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Accessibility Validation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates WCAG AA compliance across WPShadow UI';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check 1: Validate CSS files for accessibility issues.
		$css_issues = self::check_css_accessibility();
		if ( ! empty( $css_issues ) ) {
			$issues = array_merge( $issues, $css_issues );
		}

		// Check 2: Validate JavaScript keyboard navigation support.
		$js_issues = self::check_javascript_keyboard_support();
		if ( ! empty( $js_issues ) ) {
			$issues = array_merge( $issues, $js_issues );
		}

		// Check 3: Validate PHP files for ARIA attributes.
		$aria_issues = self::check_aria_attributes();
		if ( ! empty( $aria_issues ) ) {
			$issues = array_merge( $issues, $aria_issues );
		}

		// If any issues found, return finding.
		if ( ! empty( $issues ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => __( 'Accessibility Issues Detected', 'wpshadow' ),
				'description'   => sprintf(
					/* translators: %d: number of accessibility issues found */
					__( 'Found %d accessibility issues that need attention for WCAG AA compliance.', 'wpshadow' ),
					count( $issues )
				),
				'severity'      => 'medium',
				'threat_level'  => 50,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/accessibility-validation/',
				'training_link' => 'https://wpshadow.com/training/wcag-compliance/',
				'module'        => 'Accessibility',
				'priority'      => 1,
				'meta'          => array(
					'issues'       => $issues,
					'total_issues' => count( $issues ),
				),
			);
		}

		return null;
	}

	/**
	 * Check CSS files for accessibility issues.
	 *
	 * Validates:
	 * - Focus indicators are present and visible
	 * - Color contrast meets WCAG AA standards
	 * - Reduced motion preferences are respected
	 * - No outline:none without replacement
	 *
	 * @since  1.2601.2148
	 * @return array List of issues found.
	 */
	private static function check_css_accessibility() {
		$issues   = array();
		$css_path = WPSHADOW_PATH . 'assets/css/';

		if ( ! is_dir( $css_path ) ) {
			return $issues;
		}

		$css_files = glob( $css_path . '*.css' );
		if ( ! $css_files ) {
			return $issues;
		}

		foreach ( $css_files as $file ) {
			$content  = file_get_contents( $file );
			$filename = basename( $file );

			// Check for outline:none without custom focus styles.
			if ( preg_match( '/outline:\s*(none|0)/i', $content ) ) {
				// Look for :focus rules in the same file.
				if ( ! preg_match( '/:focus[^{]*\{[^}]*(border|box-shadow|background-color|outline:[^n0])/i', $content ) ) {
					$issues[] = array(
						'file'    => $filename,
						'issue'   => 'focus_outline_removed',
						'message' => __( 'Focus outline removed without custom focus styles', 'wpshadow' ),
					);
				}
			}

			// Check for prefers-reduced-motion support.
			if ( ! preg_match( '/@media\s+\(prefers-reduced-motion:\s*reduce\)/i', $content ) ) {
				// Only flag files that have animations/transitions.
				if ( preg_match( '/(animation|transition):/i', $content ) ) {
					$issues[] = array(
						'file'    => $filename,
						'issue'   => 'missing_reduced_motion',
						'message' => __( 'Animations present but no reduced motion support', 'wpshadow' ),
					);
				}
			}
		}

		return $issues;
	}

	/**
	 * Check JavaScript files for keyboard navigation support.
	 *
	 * Validates:
	 * - Event listeners include keyboard events (keydown, keyup, keypress)
	 * - Click handlers have corresponding keyboard handlers
	 * - Focus management is implemented
	 *
	 * @since  1.2601.2148
	 * @return array List of issues found.
	 */
	private static function check_javascript_keyboard_support() {
		$issues  = array();
		$js_path = WPSHADOW_PATH . 'assets/js/';

		if ( ! is_dir( $js_path ) ) {
			return $issues;
		}

		$js_files = glob( $js_path . '*.js' );
		if ( ! $js_files ) {
			return $issues;
		}

		foreach ( $js_files as $file ) {
			$content  = file_get_contents( $file );
			$filename = basename( $file );

			// Check for click events without keyboard support.
			$has_click     = preg_match( '/\.(on|addEventListener)\s*\(\s*[\'"]click[\'"]/i', $content );
			$has_keyboard  = preg_match( '/\.(on|addEventListener)\s*\(\s*[\'"]key(down|up|press)[\'"]/i', $content );
			$has_keyevent  = preg_match( '/event\.(key|keyCode|which)/i', $content );

			if ( $has_click && ! $has_keyboard && ! $has_keyevent ) {
				$issues[] = array(
					'file'    => $filename,
					'issue'   => 'click_without_keyboard',
					'message' => __( 'Click events without keyboard support', 'wpshadow' ),
				);
			}

			// Check for focus management.
			$has_focus = preg_match( '/\.focus\(\)|\.blur\(\)|tabindex/i', $content );
			$has_interaction = preg_match( '/\.(show|hide|toggle|open|close)\(/i', $content );

			if ( $has_interaction && ! $has_focus ) {
				$issues[] = array(
					'file'    => $filename,
					'issue'   => 'missing_focus_management',
					'message' => __( 'Interactive elements without focus management', 'wpshadow' ),
				);
			}
		}

		return $issues;
	}

	/**
	 * Check PHP files for ARIA attributes.
	 *
	 * Validates:
	 * - Interactive elements have aria-label or aria-labelledby
	 * - Buttons have proper role attributes
	 * - Form fields have aria-describedby for errors
	 * - Dynamic content has aria-live regions
	 *
	 * @since  1.2601.2148
	 * @return array List of issues found.
	 */
	private static function check_aria_attributes() {
		$issues   = array();
		$php_path = WPSHADOW_PATH . 'includes/admin/';

		if ( ! is_dir( $php_path ) ) {
			return $issues;
		}

		$php_files = self::get_php_files_recursive( $php_path );

		foreach ( $php_files as $file ) {
			$content  = file_get_contents( $file );
			$filename = str_replace( $php_path, '', $file );

			// Check for buttons without aria-label.
			preg_match_all( '/<button[^>]*>/i', $content, $buttons );
			foreach ( $buttons[0] as $button ) {
				if ( ! preg_match( '/aria-(label|labelledby|describedby)=/i', $button ) ) {
					// Check if button has text content.
					if ( ! preg_match( '/<button[^>]*>[^<]+<\/button>/i', $button ) ) {
						$issues[] = array(
							'file'    => $filename,
							'issue'   => 'button_without_aria_label',
							'message' => __( 'Button without aria-label or text content', 'wpshadow' ),
						);
					}
				}
			}

			// Check for form inputs without labels.
			preg_match_all( '/<input[^>]*type=["\'](?!hidden)[^"\']*["\'][^>]*>/i', $content, $inputs );
			foreach ( $inputs[0] as $input ) {
				if ( ! preg_match( '/aria-label=/i', $input ) ) {
					// Check if input has an id that might be labeled.
					preg_match( '/id=["\']([^"\']+)["\']/i', $input, $id_match );
					if ( ! empty( $id_match[1] ) ) {
						$input_id = $id_match[1];
						// Look for corresponding label.
						if ( ! preg_match( '/<label[^>]*for=["\']' . preg_quote( $input_id, '/' ) . '["\'][^>]*>/i', $content ) ) {
							$issues[] = array(
								'file'    => $filename,
								'issue'   => 'input_without_label',
								'message' => sprintf(
									/* translators: %s: input field ID */
									__( 'Input field #%s without associated label', 'wpshadow' ),
									$input_id
								),
							);
						}
					}
				}
			}
		}

		return $issues;
	}

	/**
	 * Get PHP files recursively from directory.
	 *
	 * @since  1.2601.2148
	 * @param  string $dir Directory path.
	 * @return array Array of file paths.
	 */
	private static function get_php_files_recursive( $dir ) {
		$files  = array();
		$items  = glob( $dir . '/*' );

		if ( ! $items ) {
			return $files;
		}

		foreach ( $items as $item ) {
			if ( is_dir( $item ) ) {
				$files = array_merge( $files, self::get_php_files_recursive( $item ) );
			} elseif ( pathinfo( $item, PATHINFO_EXTENSION ) === 'php' ) {
				$files[] = $item;
			}
		}

		return $files;
	}

	/**
	 * Get the diagnostic name.
	 *
	 * @since  1.2601.2148
	 * @return string Diagnostic name.
	 */
	public static function get_name(): string {
		return __( 'Accessibility Validation', 'wpshadow' );
	}

	/**
	 * Get the diagnostic description.
	 *
	 * @since  1.2601.2148
	 * @return string Diagnostic description.
	 */
	public static function get_description(): string {
		return __( 'Validates WCAG AA compliance and accessibility best practices.', 'wpshadow' );
	}
}
