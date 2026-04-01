<?php
/**
 * Accessibility Compliance Diagnostic
 *
 * Checks if theme meets basic WCAG AA accessibility standards.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Accessibility Compliance Diagnostic Class
 *
 * Verifies that the theme meets basic WCAG AA accessibility standards
 * including skip links, ARIA labels, and semantic HTML.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Accessibility_Compliance extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'accessibility-compliance';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Accessibility Compliance (WCAG AA)';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if theme meets basic WCAG AA accessibility standards';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the accessibility compliance diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if accessibility issues detected, null otherwise.
	 */
	public static function check() {
		$theme     = wp_get_theme();
		$theme_dir = $theme->get_stylesheet_directory();
		$issues    = array();
		$warnings  = array();

		// Check for accessibility-ready tag.
		$tags                   = $theme->get( 'Tags' );
		$is_accessibility_ready = is_array( $tags ) && in_array( 'accessibility-ready', $tags, true );

		// Check for skip link in header.
		$header_file = $theme_dir . '/header.php';
		if ( file_exists( $header_file ) ) {
			$header_content = file_get_contents( $header_file );

			if ( strpos( $header_content, 'skip-link' ) === false &&
				strpos( $header_content, 'skip-to-content' ) === false ) {
				$issues[] = __( 'Missing skip-to-content link in header', 'wpshadow' );
			}

			// Check for proper language attribute.
			if ( strpos( $header_content, 'lang=' ) === false &&
				strpos( $header_content, 'language_attributes()' ) === false ) {
				$issues[] = __( 'Missing language attribute in HTML tag', 'wpshadow' );
			}
		}

		// Check for ARIA landmarks.
		$template_files = array(
			$theme_dir . '/header.php',
			$theme_dir . '/footer.php',
			$theme_dir . '/sidebar.php',
		);

		$has_aria_landmarks = false;
		foreach ( $template_files as $file ) {
			if ( file_exists( $file ) ) {
				$content = file_get_contents( $file );
				if ( strpos( $content, 'role=' ) !== false ||
					strpos( $content, 'aria-label' ) !== false ) {
					$has_aria_landmarks = true;
					break;
				}
			}
		}

		if ( ! $has_aria_landmarks ) {
			$warnings[] = __( 'No ARIA landmarks detected - improves screen reader navigation', 'wpshadow' );
		}

		// Check for wp_body_open hook (accessibility improvement in WP 5.2+).
		if ( file_exists( $header_file ) ) {
			$header_content = file_get_contents( $header_file );
			if ( strpos( $header_content, 'wp_body_open' ) === false ) {
				$warnings[] = __( 'Missing wp_body_open() hook - required for some accessibility plugins', 'wpshadow' );
			}
		}

		// Check for keyboard navigation support in CSS.
		$style_files      = glob( $theme_dir . '/*.css' );
		$has_focus_styles = false;

		foreach ( $style_files as $style_file ) {
			$content = file_get_contents( $style_file );
			if ( strpos( $content, ':focus' ) !== false ) {
				$has_focus_styles = true;
				break;
			}
		}

		if ( ! $has_focus_styles ) {
			$issues[] = __( 'Missing :focus styles for keyboard navigation', 'wpshadow' );
		}

		// Check for form labels.
		$form_files = array_merge(
			glob( $theme_dir . '/*search*.php' ),
			glob( $theme_dir . '/*comment*.php' )
		);

		if ( ! empty( $form_files ) ) {
			foreach ( $form_files as $file ) {
				$content = file_get_contents( $file );
				if ( strpos( $content, '<input' ) !== false &&
					strpos( $content, '<label' ) === false &&
					strpos( $content, 'aria-label' ) === false ) {
					$warnings[] = __( 'Form inputs should have associated labels or ARIA labels', 'wpshadow' );
					break;
				}
			}
		}

		// Check if theme declares accessibility support.
		$functions_php         = $theme_dir . '/functions.php';
		$declares_a11y_support = false;

		if ( file_exists( $functions_php ) ) {
			$functions_content = file_get_contents( $functions_php );
			if ( strpos( $functions_content, 'accessibility' ) !== false ||
				strpos( $functions_content, 'a11y' ) !== false ) {
				$declares_a11y_support = true;
			}
		}

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Theme has accessibility compliance issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/accessibility-compliance?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'theme_name'             => $theme->get( 'Name' ),
					'is_accessibility_ready' => $is_accessibility_ready,
					'issues'                 => $issues,
					'warnings'               => $warnings,
				),
			);
		}

		// If only warnings.
		if ( ! empty( $warnings ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Theme accessibility has recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/accessibility-compliance?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'theme_name'             => $theme->get( 'Name' ),
					'is_accessibility_ready' => $is_accessibility_ready,
					'warnings'               => $warnings,
				),
			);
		}

		return null; // Theme meets basic accessibility standards.
	}
}
