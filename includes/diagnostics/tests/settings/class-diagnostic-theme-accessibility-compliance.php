<?php
/**
 * Theme Accessibility Compliance Diagnostic
 *
 * Checks theme for WCAG 2.1 accessibility compliance issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5049.1230
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Accessibility Compliance Diagnostic Class
 *
 * Analyzes theme accessibility features and compliance.
 *
 * @since 1.5049.1230
 */
class Diagnostic_Theme_Accessibility_Compliance extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-accessibility-compliance';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Accessibility Compliance';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks theme for accessibility compliance';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.5049.1230
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$theme = wp_get_theme();
		$issues = array();

		// Check for accessibility-ready tag.
		$tags = $theme->get( 'Tags' );
		$is_accessibility_ready = in_array( 'accessibility-ready', $tags, true );

		// Check for skip link.
		$home_url = home_url( '/' );
		$response = wp_remote_get( $home_url, array( 'timeout' => 10 ) );

		if ( ! is_wp_error( $response ) ) {
			$html = wp_remote_retrieve_body( $response );

			// Check for skip-to-content link.
			if ( ! preg_match( '/skip.*content|skip.*main/i', $html ) ) {
				$issues[] = __( 'No skip-to-content link found', 'wpshadow' );
			}

			// Check for proper heading hierarchy.
			preg_match_all( '/<h([1-6])/i', $html, $headings );
			if ( ! empty( $headings[1] ) ) {
				$first_heading = (int) $headings[1][0];
				if ( $first_heading !== 1 ) {
					$issues[] = sprintf(
						/* translators: %d: heading level */
						__( 'First heading is H%d (should be H1)', 'wpshadow' ),
						$first_heading
					);
				}
			}

			// Check for ARIA landmarks.
			$has_main = preg_match( '/<main|role=["\']main["\']/i', $html );
			$has_nav = preg_match( '/<nav|role=["\']navigation["\']/i', $html );

			if ( ! $has_main ) {
				$issues[] = __( 'No <main> landmark found', 'wpshadow' );
			}
			if ( ! $has_nav ) {
				$issues[] = __( 'No <nav> landmark found', 'wpshadow' );
			}

			// Check for keyboard navigation styles.
			if ( ! preg_match( '/:focus/i', $html ) ) {
				$issues[] = __( 'No :focus styles detected (keyboard navigation)', 'wpshadow' );
			}
		}

		// Check if theme declares accessibility support.
		if ( ! current_theme_supports( 'accessibility' ) && ! $is_accessibility_ready ) {
			$issues[] = __( 'Theme does not declare accessibility support', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Theme may have accessibility compliance issues', 'wpshadow' ),
				'severity'    => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'details'     => array(
					'theme'                  => $theme->get( 'Name' ),
					'is_accessibility_ready' => $is_accessibility_ready,
					'issues'                 => $issues,
				),
				'kb_link'     => 'https://wpshadow.com/kb/theme-accessibility-compliance',
			);
		}

		return null;
	}
}
