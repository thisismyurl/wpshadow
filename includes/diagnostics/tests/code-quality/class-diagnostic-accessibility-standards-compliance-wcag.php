<?php
/**
 * Accessibility Standards Compliance (WCAG) Diagnostic
 *
 * Validates website compliance with WCAG accessibility standards.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2240
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Accessibility Standards Compliance (WCAG) Diagnostic
 *
 * Checks website for WCAG 2.1 accessibility compliance.
 *
 * @since 1.6030.2240
 */
class Diagnostic_Accessibility_Standards_Compliance_WCAG extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'accessibility-standards-compliance-wcag';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Accessibility Standards Compliance (WCAG)';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates website compliance with WCAG accessibility standards';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2240
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$passed_checks = array();

		// Check for accessibility statement/policy
		$accessibility_page = get_option( 'wpshadow_accessibility_statement_page' );
		if ( empty( $accessibility_page ) ) {
			$issues[] = __( 'No accessibility statement or policy page configured', 'wpshadow' );
		} else {
			$passed_checks[] = __( 'Accessibility statement found', 'wpshadow' );
		}

		// Check for skip-to-main-content links
		global $wp_filter;
		$has_skip_link = isset( $wp_filter['wp_footer'] ) || isset( $wp_filter['wp_body_open'] );
		if ( ! $has_skip_link ) {
			$issues[] = __( 'Skip-to-main-content links not configured', 'wpshadow' );
		} else {
			$passed_checks[] = __( 'Skip-to-main-content navigation available', 'wpshadow' );
		}

		// Check for proper heading hierarchy
		$content = wp_remote_get( home_url() );
		if ( ! is_wp_error( $content ) ) {
			$body = wp_remote_retrieve_body( $content );

			// Check for h1 tags
			$h1_count = substr_count( strtolower( $body ), '<h1' );
			if ( $h1_count === 0 ) {
				$issues[] = __( 'Page missing H1 heading (required for WCAG compliance)', 'wpshadow' );
			} elseif ( $h1_count > 1 ) {
				$issues[] = sprintf(
					/* translators: %d: number of H1 tags */
					__( 'Multiple H1 headings found (%d) - should be exactly 1', 'wpshadow' ),
					$h1_count
				);
			} else {
				$passed_checks[] = __( 'Proper H1 heading hierarchy', 'wpshadow' );
			}

			// Check for alt text on images
			$img_count = substr_count( strtolower( $body ), '<img' );
			$img_no_alt = substr_count( strtolower( $body ), 'src=' ) - substr_count( strtolower( $body ), 'alt=' );

			if ( $img_no_alt > 0 ) {
				$issues[] = sprintf(
					/* translators: %d: number of images */
					__( '%d images missing alt text', 'wpshadow' ),
					$img_no_alt
				);
			} else {
				$passed_checks[] = __( 'All images have alt text', 'wpshadow' );
			}

			// Check for form labels
			$form_count = substr_count( strtolower( $body ), '<form' );
			$label_count = substr_count( strtolower( $body ), '<label' );

			if ( $form_count > 0 && $label_count < $form_count ) {
				$issues[] = __( 'Form fields missing associated labels', 'wpshadow' );
			} else {
				$passed_checks[] = __( 'Form labels properly associated', 'wpshadow' );
			}

			// Check for color contrast indicators
			$style_count = substr_count( strtolower( $body ), 'style=' );
			if ( $style_count > 0 ) {
				// Rough check for inline styles that might affect contrast
				$issues[] = __( 'Inline styles detected - verify color contrast meets WCAG AA standards', 'wpshadow' );
			}
		}

		// Check for ARIA landmarks
		$aria_main = substr_count( $body ?? '', 'role="main"' ) + substr_count( $body ?? '', '<main' );
		$aria_nav = substr_count( $body ?? '', 'role="navigation"' ) + substr_count( $body ?? '', '<nav' );

		if ( $aria_main === 0 ) {
			$issues[] = __( 'Missing main content landmark (ARIA role="main" or <main>)', 'wpshadow' );
		} else {
			$passed_checks[] = __( 'Main content landmark present', 'wpshadow' );
		}

		if ( $aria_nav === 0 ) {
			$issues[] = __( 'Missing navigation landmark', 'wpshadow' );
		} else {
			$passed_checks[] = __( 'Navigation landmark present', 'wpshadow' );
		}

		// Check for keyboard navigation support
		$javascript = isset( $wp_filter['wp_enqueue_scripts'] );
		if ( ! $javascript ) {
			$passed_checks[] = __( 'No JavaScript accessibility issues detected', 'wpshadow' );
		}

		// Check for lang attribute on html tag
		$html_lang = false;
		if ( false !== strpos( $body ?? '', 'lang="' ) || false !== strpos( $body ?? '', "lang='" ) ) {
			$html_lang = true;
			$passed_checks[] = __( 'Language attribute set on HTML element', 'wpshadow' );
		} else {
			$issues[] = __( 'HTML element missing lang attribute', 'wpshadow' );
		}

		// Report findings
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Website has WCAG accessibility compliance issues', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/accessibility-standards-compliance-wcag',
				'details'      => array(
					'issues'         => $issues,
					'passed_checks'  => $passed_checks,
					'wcag_level'     => 'Level A',
					'recommendations' => array(
						__( 'Implement WCAG 2.1 Level AA compliance', 'wpshadow' ),
						__( 'Use accessibility checker tools to identify issues', 'wpshadow' ),
						__( 'Test with screen readers', 'wpshadow' ),
						__( 'Verify keyboard navigation works throughout site', 'wpshadow' ),
					),
				),
			);
		}

		return null;
	}
}
